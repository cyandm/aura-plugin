<?php

namespace Cyan\PortalImporter;

if (! defined('ABSPATH')) {
	exit;
}


class ProductUpdater
{
	private $baseUrl;
	private $endpointProducts = '/site/api/v1/store/products';
	private $timeout = 30000;
	private $count;
	private $page;
	private $percentage;


	public function __construct()
	{
		$this->baseUrl = get_option(PLUGIN_NAME . '_base_url', 'https://mobomobo.ir');
		$this->count = get_option(PLUGIN_NAME . '_count', 3);
		$this->page = get_option(PLUGIN_NAME . '_page', 1);
		$this->percentage = get_option(PLUGIN_NAME . '_percentage', 100000);
	}


	// Add after __construct method
	public function setBaseUrl($baseUrl)
	{
		$this->baseUrl = $baseUrl;
		update_option(PLUGIN_NAME . '_base_url', $baseUrl);
	}

	public static function init($count, $percentage, $page)
	{
		Logger::removeLogs('update');

		$productUpdater = new ProductUpdater();
		// Add this line to get base_url from POST
		if (isset($_POST['base_url'])) {
			$productUpdater->setBaseUrl($_POST['base_url']);
		}

		$productUpdater->setCount($count);
		$productUpdater->setPercentage($percentage);
		$productUpdater->setPage($page);

		Logger::log("--------------------------- Init product updater ---------------------------", 'update');
		$productUpdater->processProductGroup();
		Logger::log("--------------------------- End product updater ---------------------------", 'update');

		$productUpdater->page++;
		update_option(PLUGIN_NAME . '_page', $productUpdater->page);
	}

	//----------------- Setters ----------------
	public function setCount($count)
	{
		$this->count = $count;
		update_option(PLUGIN_NAME . '_count', $count);
	}

	public function setPercentage($percentage)
	{
		$this->percentage = $percentage;
		update_option(PLUGIN_NAME . '_percentage', $percentage);
	}

	public function setPage($page)
	{
		$this->page = $page;
		update_option(PLUGIN_NAME . '_page', $page);
	}

	//----------------- Getters ----------------
	public function getCount()
	{
		return $this->count;
	}

	public function getPercentage()
	{
		return $this->percentage;
	}

	public function getPage()
	{
		return $this->page;
	}

	protected function getStore()
	{

		$url = add_query_arg([
			'size' => $this->count,
			'page' => $this->page,
		], $this->baseUrl . $this->endpointProducts);

		$response = wp_remote_get($url, [
			'timeout' => $this->timeout,
		]);

		if (is_wp_error($response)) {
			Logger::log("Get all products from api failed: " . $response->get_error_message(), 'update');
			return false;
		}

		Logger::log("Get all products from api: " . $url, 'update');

		return json_decode(wp_remote_retrieve_body($response), true);
	}

	protected function getProduct($id)
	{


		$url = $this->baseUrl . $this->endpointProducts . '/' . $id;

		$response = wp_remote_get($url, ['timeout' => $this->timeout]);

		Logger::log("Get single product from api: " . $url, 'update');

		if (is_wp_error($response)) {
			Logger::log("Get single product from api failed: " . $response->get_error_message(), 'update');
			return false;
		}

		return json_decode(wp_remote_retrieve_body($response), true);
	}

	private function processProductGroup()
	{
		$products_response = $this->getStore();

		// if (!$products_response || empty($products_response['products'])) {
		// 	Logger::log('No products found or failed to fetch products from API.', 'update');
		// 	return;
		// }

		if (!is_array($products_response) || !isset($products_response['products'])) {
			Logger::log("Invalid products response - using empty array", 'update');
			$products_response['products'] = [];
		}

		foreach ($products_response['products'] as $product) {
			$this->processProduct($product);
		}
	}

	protected function processProduct($product_from_group)
	{
		$product_from_api = $this->getProduct($product_from_group['id']);
		Logger::log('processing Product id: ' . $product_from_group['id'] . ' and presence: ' . $this->percentage, 'update');


		if (! $product_from_api) {
			Logger::log("Product from api not found: " . $product_from_group['id'], 'update');
			return;
		}

		$product_from_api = $product_from_api['product'];

		$sku = $product_from_api['id'];

		if (false === Helpers::checkExistProductBySku($sku)) {
			Logger::log("Product with id $sku not found", 'update');
			return;
		}


		$is_simple = empty($product_from_api['variants']);

		Logger::log('is_simple: ' . var_export($is_simple, true), 'update');

		$is_simple ?
			$this->updateSimpleProduct($product_from_api) :
			$this->updateVariableProduct($product_from_api);
		$this->processTimeProduct($product_from_api);
	}

	private function updateSimpleProduct($product_from_api)
	{

		$product = wc_get_product(wc_get_product_id_by_sku($product_from_api['id']));


		if (! $product) {
			Logger::log('product not found: ' . $product_from_api['id'], 'update');
			return false;
		}

		Logger::log('updateSimpleProduct: ' . $product->get_id(), 'update');

		$product->set_stock_quantity($product_from_api['stock']);
		$product->set_manage_stock(true);
		$product->set_stock_quantity($product_from_api['available'] ? 1000 : 0);

		if (isset($product_from_api['price'])) {
			$new_price = $product_from_api['price'] + $this->percentage;
			$product->set_price($new_price);
			$product->set_regular_price($new_price);
		}

		$product->save();

		return $product;
	}

	private function updateVariableProduct($product_from_api)
	{

		$variants_from_api = $product_from_api['variants'];

		$product = wc_get_product(wc_get_product_id_by_sku($product_from_api['id']));

		if (! $product) {
			Logger::log('product not found: ' . $product_from_api['id'], 'update');
			return false;
		} else {
			Logger::log('updateVariableProduct: ' . $product->get_id(), 'update');
		}


		$product->set_stock_quantity(intval($product_from_api['stock']));

		if (isset($product_from_api['price'])) {
			$new_price = $product_from_api['price'] + $this->percentage;
			$product->set_price($new_price);
			$product->set_regular_price($new_price);
		}

		$product->save();

		foreach ($variants_from_api as $variant_api) {

			$variant = wc_get_product(wc_get_product_id_by_sku($variant_api['id']));

			if (! $variant) {
				Logger::log('variant not found: ' . $variant_api['id'], 'update');
				continue;
			}

			Logger::log('update Variant VariableProduct: ' . $variant->get_id(), 'update');

			if (isset($variant_api['price'])) {
				$new_price = $variant_api['price'] + $this->percentage;
				$variant->set_price($new_price);
				$variant->set_regular_price($new_price);
			}

			$variant->set_manage_stock(true);
			$variant->set_stock_quantity($variant_api['available'] ? 1000 : 0);
			//$variant->set_stock_quantity(intval($variant_api['stock']));

			$variant->save();
		}

		wc_delete_product_transients($product->get_id());
	}

	private function processTimeProduct($product_from_api)
	{
		$product = wc_get_product(wc_get_product_id_by_sku($product_from_api['id']));

		if (!$product) {
			Logger::log('Product not found for timestamp update: ' . $product_from_api['id'], 'update');
			return false;
		}

		$timestamp = $product_from_api['published']['timestamp'];

		Logger::log("Setting product timestamp to: " . $timestamp, 'update');

		$date = date('Y-m-d H:i:s', $timestamp);

		wp_update_post([
			'ID' => $product->get_id(),
			'post_date' => $date,
			'post_date_gmt' => get_gmt_from_date($date),
			'post_modified' => $date,
			'post_modified_gmt' => get_gmt_from_date($date)
		]);

		return $product;
	}
}
