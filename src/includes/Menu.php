<?php

namespace Cyan\PortalImporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Menu {

	public static function init() {
		if ( ! defined( 'ABSPATH' ) ) {
			exit;
		}

		add_action( 'admin_menu', [ self::class, 'productImporterAddMenu' ] );
	}

	public static function productImporterAddMenu() {
		add_menu_page(
			'Import Products from API',
			'همگام سازی محصولات',
			'manage_options',
			'product-importer',
			[ self::class, 'productImporterPage' ],
			'dashicons-download',
			50
		);

		add_submenu_page(
			'product-importer',
			'دریافت محصولات',
			'دریافت محصولات',
			'manage_options',
			'product-importer',
			[ self::class, 'productImporterPage' ]
		);

		add_submenu_page(
			'product-importer',
			'به روز رسانی محصولات',
			'به روز رسانی محصولات',
			'manage_options',
			'product-importer-update',
			[ self::class, 'productImporterUpdatePage' ]
		);

		add_submenu_page(
			'product-importer',
			'آمار',
			'آمار',
			'manage_options',
			'product-importer-statistics',
			[ self::class, 'productImporterStatisticsPage' ]
		);
	}

	public static function productImporterPage() {
		Helpers::getTemplatePart( 'admin-default' );
	}

	public static function productImporterUpdatePage() {
		Helpers::getTemplatePart( 'admin-update' );
	}

	public static function productImporterStatisticsPage() {
		Helpers::getTemplatePart( 'admin-statistics' );
	}
}