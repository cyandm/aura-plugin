<?php

// namespace Cyan\PortalImporter;

// if (!defined('ABSPATH')) {
//     exit;
// }

// class ProductUpdaterCronJob extends ProductUpdater
// {
//     public function __construct($page = 1, $count = 20)
//     {
//         parent::__construct($page, $count);
//     }

//     public function example_add_cron_interval($schedules)
//     {
//         $schedules['five_seconds'] = array(
//             'interval' => 5,
//             'display'  => esc_html__('Every Five Seconds'),
//         );
//         return $schedules;
//     }

//     public function add_actions()
//     {
//         add_filter('cron_schedules', [$this, 'example_add_cron_interval']);
//         add_action('cyan_plugin_init', [$this, 'handle_cron_job']);
//         Logger::log('Cron job added', 'cron');
//     }


//     public function handle_cron_job()
//     {

        
//         Logger::log('Starting product update via cron job', 'cron');

        // $page = 1;
        // $count = 20;
        // $finished = false;

        // while (!$finished) {
        //     $products_response = $this->getStore($page, $count);

        //     if (!$products_response || empty($products_response['products'])) {
        //         Logger::log('No products found or failed to fetch products from API for page ' . $page, 'cron');
        //         break;
        //     }

        //     // پردازش هر محصول
        //     foreach ($products_response['products'] as $product) {
        //         Logger::log('Processing Product id: ' . $product['id'], 'cron');
        //         $this->processProduct($product);
        //     }

        //     if (count($products_response['products']) < $count) {
        //         $finished = true;
        //     }

        //     $page++;


        //     sleep(300);
        // }

        // Logger::log('Finished product update via cron job', 'cron');
    //}
//}
