<?php

namespace Cyan\PortalImporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Stats {
	public static function getTotalProductsCount($api_url = null) {
		if (!$api_url) {
			$api_url = get_option(PLUGIN_NAME . '_base_url', 'https://mobomobo.ir');
		}
		return get_option(PLUGIN_NAME . '_total_products_count_from_api_' . md5($api_url), 0);
	}

	public static function setTotalProductsCount($count, $api_url = null) {
		if (!$api_url) {
			$api_url = get_option(PLUGIN_NAME . '_base_url', 'https://mobomobo.ir');
		}
		update_option(PLUGIN_NAME . '_total_products_count_from_api_' . md5($api_url), $count);
	}

	public static function getTotalProductsCountFromDatabase($api_url = null) {
		if (!$api_url) {
			$api_url = get_option(PLUGIN_NAME . '_base_url', 'https://mobomobo.ir');
		}
		
		global $wpdb;
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT p.ID) 
				FROM {$wpdb->posts} p 
				JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
				WHERE p.post_type = 'product' 
				AND pm.meta_key = '_api_source' 
				AND pm.meta_value = %s",
				$api_url
			)
		);
		
		return (int)$count;
	}
}