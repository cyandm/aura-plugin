<?php

namespace Cyan\PortalImporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {
	public static function init() {
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueueScripts' ] );
	}

	public static function enqueueScripts() {

		$file_name = ENV === 'development' ? 'admin.js' : 'admin.min.js';

		$src = PLUGIN_URL . 'src/assets/js/dist/' . $file_name;

		wp_enqueue_script( 'product-importer-admin', $src, [ 'jquery' ], PLUGIN_VERSION, true );
	}
}

