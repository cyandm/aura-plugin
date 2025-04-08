<?php


// namespace Cyan\PortalImporter;

// if (! defined('ABSPATH')) {
//     exit;
// }


// class SingleProduct extends ProductUpdater
// {


//     public function __construct()
//     {
//         parent::__construct();
//     }


//     public function add_actions()
//     {
//         add_action('woocommerce_before_single_product', array($this, 'update_single_product'));

//         Logger::removeLogs('single');
//     }


//     public function update_single_product()
//     {


//         if (!is_product()) {
//             return;
//         }

//         global $product;

//         if (!$product || !is_a($product, 'WC_Product')) {
//             return;
//         }

//         $product_sku = $product->get_sku();

//         Logger::log('Single Product Recivied SKU: ' . $product_sku, 'single');

//         if (!$product_sku) {
//             return;
//         }

//         $last_modified = get_post_field('post_modified', $product->get_id());
//         $last_modified_timestamp = strtotime($last_modified);

//         $current_time = current_time('timestamp');


//         Logger::log('Last Modified Timestamp: ' . $last_modified_timestamp, 'single');
//         Logger::log('Current Time Timestamp: ' . $current_time, 'single');
//         Logger::log('Difference in Seconds: ' . ($current_time - $last_modified_timestamp), 'single');

//         if (($current_time - $last_modified_timestamp) <= 14400) {
//             Logger::log('محصول اخیراً ویرایش شده است. درخواست API انجام نشد.', 'single');
//             Logger::log('محصول اخیراً ویرایش شده است. فاصله زمانی: ' . ($current_time - $last_modified_timestamp) . ' ثانیه.', 'single');
//             return;
//         }

//         $api_response = $this->getProduct($product_sku);

//         if (!$api_response || is_wp_error($api_response)) {
//             return;
//         }


//         $this->processProduct($api_response['product']);
//     }
// }
