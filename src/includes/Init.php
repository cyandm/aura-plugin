<?php

namespace Cyan\PortalImporter;

if (! defined('ABSPATH')) {
	exit;
}

class Init
{
	public static function init()
	{
		Menu::init();
		Assets::init();
		Rest::init();
		Logger::init();
		ProductColumns::init();

		// $singleProduct = new SingleProduct();
		// $singleProduct->add_actions();


		// $productUpdaterCronJob = new ProductUpdaterCronJob();
		// $productUpdaterCronJob->add_actions();


		// if (! wp_next_scheduled('cyan_plugin_init')) {
		// 	wp_schedule_event(time(), 'every_minute', 'cyan_plugin_init');
		// }
	}
}
