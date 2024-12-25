<?php

namespace Cyan\PortalImporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WC_Product;
use WC_Product_Variable;
use WC_Product_Variation;

class ProductImporter {
	private $baseUrl = 'https://mobomobo.ir';
	private $endpointProducts = '/site/api/v1/store/products';
	private $timeout = 30;
	private $count;
	private $percentage;


	public function __construct() {

		$this->count = get_option( PLUGIN_NAME . '_count', 3 );
		$this->percentage = get_option( PLUGIN_NAME . '_percentage', 40 );
	}

	public static function init( $count, $percentage ) {
		$instance = new self();
		$instance->setCount( $count );
		$instance->setPercentage( $percentage );
		$instance->processProductGroup();
	}

	//----------------- Setters ----------------
	public function setCount( $count ) {
		$this->count = $count;
		update_option( PLUGIN_NAME . '_count', $count );
	}

	public function setPercentage( $percentage ) {
		$this->percentage = $percentage;
		update_option( PLUGIN_NAME . '_percentage', $percentage );
	}

	//----------------- Getters ----------------
	public function getCount() {
		return $this->count;
	}

	public function getPercentage() {
		return $this->percentage;
	}

	private function getStore() {

		$url = add_query_arg( [ 
			'size' => $this->count,
		], $this->baseUrl . $this->endpointProducts );

		$response = wp_remote_get( $url, [ 
			'timeout' => $this->timeout,
		] );

		if ( is_wp_error( $response ) ) {
			error_log( "Get all products from api failed: " . $response->get_error_message() );
			return false;
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}

	private function getProduct( $id ) {


		$url = $this->baseUrl . $this->endpointProducts . '/' . $id;

		$response = wp_remote_get( $url, [ 'timeout' => $this->timeout ] );

		if ( is_wp_error( $response ) ) {
			error_log( "Get single product from api failed: " . $response->get_error_message() );
			return false;
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}


	// -------------- Creators --------------

	private function createVariableProduct( $product_from_api ) {
		$product = new WC_Product_Variable();
		$product->set_name( $product_from_api['title'] );
		$product->set_sku( $product_from_api['id'] );
		$product->set_status( 'publish' );
		$product->save();
		return $product;
	}

	private function createSimpleProduct( $product_from_api ) {
		$product = new WC_Product();
		$product->set_name( $product_from_api['title'] );
		$product->set_sku( $product_from_api['id'] );
		$product->set_status( 'publish' );
		$product->save();
		return $product;
	}

	private function createCombinations( $arrays ) {
		$result = [ [] ];
		foreach ( $arrays as $property_values ) {
			$tmp = [];
			foreach ( $result as $result_item ) {
				foreach ( $property_values as $property_value ) {
					$tmp[] = array_merge( $result_item, [ $property_value ] );
				}
			}
			$result = $tmp;
		}
		return $result;
	}


	// ---------------- Processors ------------------

	private function processProductGroup() {
		$products_response = $this->getStore();

		Stats::setTotalProductsCount( $products_response['total'] );

		foreach ( $products_response['products'] as $product ) {
			$this->processProduct( $product );
		}
	}

	private function processProduct( $product_from_group ) {
		$product_from_api = $this->getProduct( $product_from_group['id'] );

		if ( ! $product_from_api ) {
			error_log( "Product not found: " . $product_from_group['id'] );
			return;
		}

		$product_from_api = $product_from_api['product'];

		$sku = $product_from_api['id'];

		if ( Helpers::checkExistProductBySku( $sku ) ) {
			echo "Product with id $sku already exists";
			return;
		}

		$is_simple = empty( $product_from_api['variants'] );

		if ( $is_simple ) {

			$product = $this->createSimpleProduct( $product_from_api );

		} else {

			$product = $this->createVariableProduct( $product_from_api );

			$product = $this->processProductVariants( $product, $product_from_api );
		}

		$product = $this->processProductImages( $product, $product_from_api );

		$product = $this->processProductAttributes( $product, $product_from_api );

		$product = $this->processProductCategories( $product, $product_from_api );

		$product->save();

		wc_delete_product_transients( $product->get_id() );
	}

	private function processProductImages( WC_Product $product, $product_from_api ) {

		//Upload feature image
		$url = $this->baseUrl . $product_from_api['images'][0]['path'];
		$feature_image_id = Helpers::uploadImage( $url, $product->get_id() );
		$product->set_image_id( $feature_image_id );

		$product->save();

		//Upload gallery images
		$gallery_image_ids = [];
		foreach ( $product_from_api['images'] as $images ) {
			$gallery_image_url = 'https://mobomobo.ir/' . $images['path'];

			$gallery_image_id = Helpers::uploadImage( $gallery_image_url, $product->get_id() );

			$gallery_image_id && array_push( $gallery_image_ids, $gallery_image_id );
		}

		if ( ! empty( $gallery_image_ids ) ) {
			update_post_meta( $product->get_id(), '_product_image_gallery', implode( ',', $gallery_image_ids ) );
		}

		$product->save();

		return $product;
	}

	private function processProductAttributes( WC_Product $product, $product_from_api ) {
		$attributes = $product_from_api['attributes'];

		foreach ( $attributes as $attr ) {
			$attr_name = $attr['name'];
			$attr_slug = 'pa_' . sanitize_title( Helpers::convertPersianToEnglish( $attr_name ) );

			wc_create_attribute( [ 
				'name' => $attr_name,
				'slug' => $attr_slug,
			] );

			register_taxonomy(
				$attr_slug,
				'product',
				[ 
					'hierarchical' => false,
					'show_ui' => false,
					'query_var' => true,
				]
			);

			$term_ids = [];
			foreach ( $attr['values'] as $value ) {

				$term = term_exists( $value, $attr_slug );

				if ( is_null( $term ) ) {
					$term = wp_insert_term( $value, $attr_slug );
				}

				array_push( $term_ids, intval( $term['term_id'] ) );
			}

			$attributes_array[ $attr_slug ] = [ 
				'name' => $attr_slug,
				'value' => [],
				'is_taxonomy' => 1,
				'position' => 0,
				'is_visible' => 0,
				'is_variation' => 1,
			];

			update_post_meta( $product->get_id(), '_product_attributes', $attributes_array );

			wp_set_object_terms( $product->get_id(), $term_ids, $attr_slug );
		}


		$product->save();

		return $product;
	}

	//TODO: need refactor for two model of variants
	private function processProductVariants( WC_Product $product, $product_from_api ) {
		$attributes = $product_from_api['attributes'];
		$product_id = $product->get_id();

		$combinations = $this->createCombinations( array_column( $attributes, 'values' ) );

		foreach ( $combinations as $combination ) {
			$variation = new WC_Product_Variation();
			$variation->set_parent_id( $product_id );

			$id = $variation->save();

			// Map the combination to its corresponding attributes
			foreach ( $combination as $key => $value ) {

				update_post_meta(
					$id,
					'attribute_pa_' . sanitize_title( Helpers::convertPersianToEnglish( $attributes[ $key ]['name'] ) ),
					sanitize_title( $value )
				);

				foreach ( $product_from_api['variants'] as $variant ) {

					$variant_model = explode( ':', $variant['title'] )[1] ?? '';
					$variant_model = trim( $variant_model );

					$price = $variant['price'] + ( $variant['price'] * $this->percentage / 100 );

					// If the title of the variant matches the combination value
					if ( $variant_model === $value ) {
						$variation->set_price( $price );
						$variation->set_regular_price( $price );
						$variation->set_stock_status( $variant['available'] ? 'instock' : 'outofstock' );
						$variation->save();
					}
				}
			}

			$variation->save();
		}


		$product->save();

		return $product;
	}

	//TODO: need refactor for break down function
	private function processProductCategories( WC_Product $product, $product_from_api ) {
		$product_id = $product->get_id();
		$categories = $product_from_api['categories'];

		$parent_id = 0;
		foreach ( $categories as $category ) {
			$url_parts = array_filter( explode( '/', trim( $category['url'], '/' ) ) ); // تجزیه URL به بخش‌ها

			foreach ( $url_parts as $index => $part ) {

				if ( $part === 'products' ) {
					continue;
				}

				$slug = sanitize_title( $part );

				$name = ( $index === array_key_last( $url_parts ) ) ? $category['title'] : ucfirst( $part );

				$existing_term = term_exists( $slug, 'product_cat', $parent_id );

				if ( ! $existing_term ) {
					if ( $parent_id && ! term_exists( get_term( $parent_id )->slug, 'product_cat' ) ) {
						error_log( "والد یافت نشد: $parent_id" );
						$parent_id = 0;
					}

					$new_term = wp_insert_term(
						$name,
						'product_cat',
						[ 
							'slug' => $slug,
							'parent' => $parent_id,
						]
					);

					if ( ! is_wp_error( $new_term ) ) {
						$parent_id = $new_term['term_id'];
					} else {
						error_log( 'خطا در ایجاد دسته‌بندی: ' . print_r( $new_term, true ) );
					}
				} else {
					$parent_id = is_array( $existing_term ) ? $existing_term['term_id'] : $existing_term; // دریافت ID والد
				}
			}


			if ( $parent_id ) {
				$term = get_term( $parent_id, 'product_cat' );

				if ( ! is_wp_error( $term ) && $term ) {
					wp_set_object_terms( $product_id, $term->name, 'product_cat', true );
				}
			}

			$parent_id = 0;
		}

		$product->save();

		return $product;
	}
}