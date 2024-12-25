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

	public static function init() {
		$productUpdater = new ProductUpdater();

		$productUpdater->count = 100;

		$productUpdater->processProductGroup();
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

	private function processProductGroup() {
		$products_response = $this->getStore();

		foreach ( $products_response['products'] as $product ) {
			$this->processProduct( $product );
		}
	}

	private function processProduct( $product_from_group ) {
		$product_from_api = $this->getProduct( $product_from_group['id'] );

		if ( ! $product_from_api ) {
			error_log( "Product from api not found: " . $product_from_group['id'] );
			return;
		}

		$product_from_api = $product_from_api['product'];

		$sku = $product_from_api['id'];

		if ( ! Helpers::checkExistProductBySku( $sku ) ) {
			error_log( "Product with id $sku not found" );
			return;
		}

		$is_simple = empty( $product_from_api['variants'] );

		$product = $is_simple ?
			$this->updateSimpleProduct( $product_from_api ) :
			$this->updateVariableProduct( $product_from_api );


		$product->save();

		wc_delete_product_transients( $product->get_id() );
	}

	private function updateSimpleProduct( $product_from_api ) {

		$product = wc_get_product( $product_from_api['id'] );

		$product->set_stock_quantity( $product_from_api['stock'] );

		$product->save();

		return $product;
	}

	private function updateVariableProduct( $product_from_api ) {

		$variants_from_api = $product_from_api['variants'];

		$product = wc_get_product( $product_from_api['id'] );

		$product->set_stock_quantity( $product_from_api['stock'] );

		$product->save();

		foreach ( $variants_from_api as $variant_api ) {

			$variant = wc_get_product( $variant_api['id'] );

			$variant->set_stock_quantity( $variant_api['stock'] );

			$variant->save();
		}

		return $product;
	}
}
