<?php

namespace Cyan\PortalImporter;

if (! defined('ABSPATH')) {
	exit;
}

use WC_Product;
use WC_Product_Variable;
use WC_Product_Variation;

class ProductImporter
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

	// Add this setter method
	public function setBaseUrl($baseUrl)
	{
		$this->baseUrl = $baseUrl;
		update_option(PLUGIN_NAME . '_base_url', $baseUrl);
	}

	public static function init($count, $percentage, $page)
	{
		Logger::removeLogs('import');
		Logger::log("--------------------------- Init product importer ---------------------------");

		$instance = new self();
		// Get base_url from POST request
		if (isset($_POST['base_url'])) {
			$instance->setBaseUrl($_POST['base_url']);
		}

		$instance->setCount($count);
		$instance->setPercentage($percentage);
		$instance->setPage($page);
		$instance->processProductGroup();
		Logger::log("--------------------------- End product importer ---------------------------");
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

	private function getStore()
	{

		$url = add_query_arg([
			'size' => $this->count,
			'page' => $this->page,
		], $this->baseUrl . $this->endpointProducts);

		$response = wp_remote_get($url, [
			'timeout' => $this->timeout,
		]);

		Logger::log("Get all products from api: " . $url);

		if (is_wp_error($response)) {
			Logger::log("Get all products from api failed: " . $response->get_error_message());
			return false;
		}

		return json_decode(wp_remote_retrieve_body($response), true);
	}

	private function getProduct($id)
	{


		$url = $this->baseUrl . $this->endpointProducts . '/' . $id;

		$response = wp_remote_get($url, ['timeout' => $this->timeout]);
		Logger::log("Get single product from api: " . $url);

		if (is_wp_error($response)) {
			Logger::log("Get single product from api failed: " . $response->get_error_message());
			return false;
		}

		return json_decode(wp_remote_retrieve_body($response), true);
	}


	// -------------- Creators --------------

	private function createVariableProduct($product_from_api)
	{
		Logger::log("Create variable product: " . $product_from_api['id']);

		$product = new WC_Product_Variable();
		$product->set_name($product_from_api['title']);
		$product->set_sku($product_from_api['id']);
		$product->set_status('publish');
		$product->set_stock_status($product_from_api['available'] ? 'instock' : 'outofstock');
		// $product->set_manage_stock(true);
		// $product->set_stock_quantity($product_from_api['available'] ? 1000 : 0);
		$product->save();
		return $product;
	}

	private function createSimpleProduct($product_from_api)
	{
		Logger::log("Create simple product: " . $product_from_api['id']);

		$product = new WC_Product();
		$product->set_name($product_from_api['title']);
		$product->set_sku($product_from_api['id']);
		$product->set_date_modified($product_from_api['updated_at']);
		$product->set_status('publish');
		$product->set_stock_status($product_from_api['available'] ? 'instock' : 'outofstock');
		// $product->set_manage_stock(true);
		// $product->set_stock_quantity($product_from_api['available'] ? 1000 : 0);
		$product->save();
		return $product;
	}

	private function createCombinations($arrays)
	{
		$result = [[]];
		foreach ($arrays as $property_values) {
			$tmp = [];
			foreach ($result as $result_item) {
				foreach ($property_values as $property_value) {
					$tmp[] = array_merge($result_item, [$property_value]);
				}
			}
			$result = $tmp;
		}
		return $result;
	}

	// ---------------- Processors ------------------

	private function processProductGroup()
	{
		$products_response = $this->getStore();

		Stats::setTotalProductsCount($products_response['total']);

		if (!is_array($products_response) || !isset($products_response['products'])) {
			Logger::log("Invalid products response - using empty array");
			$products_response['products'] = [];
		}

		foreach ($products_response['products'] as $product) {
			$this->processProduct($product);
		}
	}

	private function processProduct($product_from_group)
	{

		Logger::log("Process product: " . $product_from_group['id']);

		$product_from_api = $this->getProduct($product_from_group['id']);

		if (! $product_from_api) {
			Logger::log("Product not found: " . $product_from_group['id']);
			return;
		}

		$product_from_api = $product_from_api['product'];

		$sku = $product_from_api['id'];

		if (Helpers::checkExistProductBySku($sku)) {
			Logger::log("Product with id <b>$sku</b> already exists");
			return;
		}

		$is_simple = empty($product_from_api['variants']);

		if ($is_simple) {

			$product = $this->createSimpleProduct($product_from_api);
		} else {

			$product = $this->createVariableProduct($product_from_api);

			$product = $this->processProductVariants($product, $product_from_api);
		}

		$product = $this->processProductImages($product, $product_from_api);

		$product = $this->processProductAttributes($product, $product_from_api);

		$product = $this->processProductCategories($product, $product_from_api);

		$product = $this->processTimeProduct($product, $product_from_api);

		// Add API source meta before final save
		update_post_meta($product->get_id(), '_api_source', $this->baseUrl);

		$product->save();

		Logger::log("Product saved: " . $product->get_id());

		wc_delete_product_transients($product->get_id());
	}

	private function processProductImages(WC_Product $product, $product_from_api)
	{
		Logger::log("Process product images: " . $product_from_api['id']);

		// Upload feature image
		$url = $this->baseUrl . $product_from_api['images'][0]['path'];
		$feature_image_id = Helpers::uploadImage($url, $product->get_id());

		if ($feature_image_id) {
			$product->set_image_id($feature_image_id);
			$product->save();
			sleep(2); // تأخیر 2 ثانیه قبل از شروع آپلود گالری
		}

		// Upload gallery images
		$gallery_image_ids = [];
		foreach (array_slice($product_from_api['images'], 1) as $images) {
			$gallery_image_url = $this->baseUrl . $images['path'];
			$gallery_image_id = Helpers::uploadImage($gallery_image_url, $product->get_id());

			if ($gallery_image_id) {
				array_push($gallery_image_ids, $gallery_image_id);
				sleep(1); // تأخیر 1 ثانیه بین هر آپلود
			}
		}

		if (!empty($gallery_image_ids)) {
			update_post_meta($product->get_id(), '_product_image_gallery', implode(',', $gallery_image_ids));
			$product->save();
		}

		return $product;
	}

	private function processProductAttributes(WC_Product $product, $product_from_api)
	{
		Logger::log("Process product attributes: " . $product_from_api['id']);

		$attributes = $product_from_api['attributes'];

		foreach ($attributes as $attr) {
			$attr_name = $attr['name'];
			$attr_slug = 'pa_' . sanitize_title(Helpers::convertPersianToEnglish($attr_name));

			wc_create_attribute([
				'name' => $attr_name,
				'slug' => $attr_slug,
			]);

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
			foreach ($attr['values'] as $value) {

				$term = term_exists($value, $attr_slug);

				if (is_null($term)) {
					$term = wp_insert_term($value, $attr_slug);
				}

				array_push($term_ids, intval($term['term_id']));
			}

			$attributes_array[$attr_slug] = [
				'name' => $attr_slug,
				'value' => [],
				'is_taxonomy' => 1,
				'position' => 0,
				'is_visible' => 0,
				'is_variation' => 1,
			];

			update_post_meta($product->get_id(), '_product_attributes', $attributes_array);

			wp_set_object_terms($product->get_id(), $term_ids, $attr_slug);
		}


		$product->save();

		return $product;
	}

	private function processProductVariants(WC_Product $product, $product_from_api)
	{
		Logger::log("Process product variants: " . $product_from_api['id']);

		$attributes = $product_from_api['attributes'];

		if (empty($attributes)) {
			return $product;
		}

		$product_id = $product->get_id();

		$combinations = $this->createCombinations(array_column($attributes, 'values'));

		foreach ($combinations as $combination) {
			$variation = new WC_Product_Variation();
			$variation->set_parent_id($product_id);

			$id = $variation->save();

			// Map the combination to its corresponding attributes
			foreach ($combination as $key => $value) {

				update_post_meta(
					$id,
					'attribute_pa_' . sanitize_title(Helpers::convertPersianToEnglish($attributes[$key]['name'])),
					sanitize_title($value)
				);
			}

			foreach ($product_from_api['variants'] as $variant) {

				$title = $variant['title'];
				$price = $variant['price'] + $this->percentage;
				//$price = $variant['price'] + ($variant['price'] * $this->percentage / 100);


				$variant_model = explode('،', $title);
				$variant_checker = [];

				foreach ($variant_model as $model) {
					$arr_item = explode(':', $model);
					array_push($variant_checker, trim($arr_item[1]));
				}

				// If the title of the variant matches the combination value
				if ($variant_checker === $combination) {
					$variation->set_price($price);
					$variation->set_regular_price($price);
					$variation->set_stock_status($variant['available'] ? 'instock' : 'outofstock');
					$variation->set_manage_stock(true);
					$variation->set_stock_quantity($variant['stock']);
					//$variation->set_stock_quantity($variant['available'] ? 1000 : 0);
					$variation->set_sku($variant['id']);

					$variation->save();
				}
			}

			$variation->save();
		}


		$product->save();

		return $product;
	}

	//TODO: need refactor for break down function
	private function processProductCategories(WC_Product $product, $product_from_api)
	{
		Logger::log("Process product categories: " . $product_from_api['id']);

		$product_id = $product->get_id();
		$categories = $product_from_api['categories'];

		$parent_id = 0;
		foreach ($categories as $category) {
			$url_parts = array_filter(explode('/', trim($category['url'], '/'))); // تجزیه URL به بخش‌ها

			foreach ($url_parts as $index => $part) {

				if ($part === 'products') {
					continue;
				}

				$slug = sanitize_title($part);

				$name = ($index === array_key_last($url_parts)) ? $category['title'] : ucfirst($part);

				$existing_term = term_exists($slug, 'product_cat', $parent_id);

				if (! $existing_term) {
					if ($parent_id && ! term_exists(get_term($parent_id)->slug, 'product_cat')) {
						error_log("والد یافت نشد: $parent_id");
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

					if (! is_wp_error($new_term)) {
						$parent_id = $new_term['term_id'];
					} else {
						error_log('خطا در ایجاد دسته‌بندی: ' . print_r($new_term, true));
					}
				} else {
					$parent_id = is_array($existing_term) ? $existing_term['term_id'] : $existing_term; // دریافت ID والد
				}
			}


			if ($parent_id) {
				$term = get_term($parent_id, 'product_cat');

				if (! is_wp_error($term) && $term) {
					wp_set_object_terms($product_id, $term->name, 'product_cat', true);
				}
			}

			$parent_id = 0;
		}

		$product->save();

		return $product;
	}

	private function processTimeProduct(WC_Product $product, $product_from_api)
	{
		$product_id = $product->get_id();

		$timestamp = $product_from_api['published']['timestamp'];

		Logger::log("Setting product timestamp to: " . $timestamp);

		$date = date('Y-m-d H:i:s', $timestamp);

		wp_update_post([
			'ID' => $product_id,
			'post_date' => $date,
			'post_date_gmt' => get_gmt_from_date($date),
			'post_modified' => $date,
			'post_modified_gmt' => get_gmt_from_date($date)
		]);

		return $product;
	}
}
