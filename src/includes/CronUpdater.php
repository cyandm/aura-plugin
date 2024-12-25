<?php

namespace Cyan\PortalImporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CronUpdater {

	public static function init() {


		add_action( 'wp', 'start_daily_product_update_schedule' );
		add_action( 'daily_product_update_cron_hook', 'execute_product_update_via_cron' );
		add_filter( 'cron_schedules', 'add_daily_schedule' );
	}

	// ثبت کرون جاب برای بروزرسانی محصولات ساعت 12 شب
	function start_daily_product_update_schedule() {
		if ( ! wp_next_scheduled( 'daily_product_update_cron_hook' ) ) {
			wp_schedule_event( strtotime( '12:00:00' ), 'daily', 'daily_product_update_cron_hook' );
		}
	}

	// اتصال کرون جاب به تابع بروزرسانی محصولات
	function execute_product_update_via_cron() {
		if ( class_exists( 'Cyan\PortalImporter\ProductUpdater' ) ) {
			ProductUpdater::init();
		}
	}

	function add_daily_schedule( $schedules ) {
		$schedules['daily'] = [ 
			'interval' => 86400, // یک روز
			'display' => 'هر روز'
		];
		return $schedules;
	}
}
