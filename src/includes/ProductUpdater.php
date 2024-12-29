<?php

namespace Cyan\PortalImporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ProductUpdater {
	private $baseUrl = 'https://mobomobo.ir';
	private $endpointProducts = '/site/api/v1/store/products';
	private $timeout = 30;
	private $count;
	private $page;

	public function __construct( $page = 1, $count = 20 ) {
		$this->page = $page;
		$this->count = $count;
	}

	public static function init() {
		$productUpdater = new ProductUpdater();

		Logger::removeLogs( 'update' );

		$productUpdater->count = 20;
		$productUpdater->page = get_option( 'product_updater_last_page', 1 );

		Logger::log( "--------------------------- Init product updater ---------------------------", 'update' );
		$productUpdater->processProductGroup();
		Logger::log( "--------------------------- End product updater ---------------------------", 'update' );


		$productUpdater->page++;
		update_option( 'product_updater_last_page', $productUpdater->page );
	}


	public function getPage() {
		return $this->page;
	}

	public function getCount() {
		return $this->count;
	}

	private function getStore() {

		$url = add_query_arg( [ 
			'size' => $this->count,
			'page' => $this->page,
		], $this->baseUrl . $this->endpointProducts );

		$response = wp_remote_get( $url, [ 
			'timeout' => $this->timeout,
		] );

		if ( is_wp_error( $response ) ) {
			Logger::log( "Get all products from api failed: " . $response->get_error_message(), 'update' );
			return false;
		}

		Logger::log( "Get all products from api: " . $url, 'update' );

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}

	private function getProduct( $id ) {


		$url = $this->baseUrl . $this->endpointProducts . '/' . $id;

		$response = wp_remote_get( $url, [ 'timeout' => $this->timeout ] );

		Logger::log( "Get single product from api: " . $url, 'update' );

		if ( is_wp_error( $response ) ) {
			Logger::log( "Get single product from api failed: " . $response->get_error_message(), 'update' );
			return false;
		}

		return json_decode( wp_remote_retrieve_body( $response ), true );
	}

	private function processProductGroup() {
		$products_response = $this->getStore();

		foreach ( $products_response['products'] as $product ) {
			$this->processProduct( $product );
		}

	}

	private function processProduct( $product_from_group ) {
		$product_from_api = $this->getProduct( $product_from_group['id'] );
		Logger::log( 'processing Product id: ' . $product_from_group['id'], 'update' );


		if ( ! $product_from_api ) {
			Logger::log( "Product from api not found: " . $product_from_group['id'], 'update' );
			return;
		}

		$product_from_api = $product_from_api['product'];

		$sku = $product_from_api['id'];

		if ( false === Helpers::checkExistProductBySku( $sku ) ) {
			Logger::log( "Product with id $sku not found", 'update' );
			return;
		}


		$is_simple = empty( $product_from_api['variants'] );

		Logger::log( 'is_simple: ' . var_export( $is_simple, true ), 'update' );

		$is_simple ?
			$this->updateSimpleProduct( $product_from_api ) :
			$this->updateVariableProduct( $product_from_api );



	}

	private function updateSimpleProduct( $product_from_api ) {

		$product = wc_get_product( wc_get_product_id_by_sku( $product_from_api['id'] ) );


		if ( ! $product ) {
			Logger::log( 'product not found: ' . $product_from_api['id'], 'update' );
			return false;
		}

		Logger::log( 'updateSimpleProduct: ' . $product->get_id(), 'update' );

		$product->set_stock_quantity( $product_from_api['stock'] );

		$product->save();

		return $product;
	}

	private function updateVariableProduct( $product_from_api ) {

		$variants_from_api = $product_from_api['variants'];

		$product = wc_get_product( wc_get_product_id_by_sku( $product_from_api['id'] ) );

		if ( ! $product ) {
			Logger::log( 'product not found: ' . $product_from_api['id'], 'update' );
			return false;
		} else {
			Logger::log( 'updateVariableProduct: ' . $product->get_id(), 'update' );
		}


		$product->set_stock_quantity( intval( $product_from_api['stock'] ) );

		$product->save();

		foreach ( $variants_from_api as $variant_api ) {

			$variant = wc_get_product( wc_get_product_id_by_sku( $variant_api['id'] ) );

			if ( ! $variant ) {
				Logger::log( 'variant not found: ' . $variant_api['id'], 'update' );
				continue;
			}

			Logger::log( 'update Variant VariableProduct: ' . $variant->get_id(), 'update' );

			$variant->set_stock_quantity( intval( $variant_api['stock'] ) );

			$variant->save();
		}

		wc_delete_product_transients( $product->get_id() );

	}
}
