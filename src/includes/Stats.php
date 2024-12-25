<?php

namespace Cyan\PortalImporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Stats {
	public static function getTotalProductsCount() {
		return get_option( PLUGIN_NAME . '_total_products_count_from_api' );
	}

	public static function getTotalProductsCountFromDatabase() {
		return wp_count_posts( 'product' )->publish;
	}

	public static function setTotalProductsCount( $count ) {
		update_option( PLUGIN_NAME . '_total_products_count_from_api', $count );
	}
}