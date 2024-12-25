<?php
/*
Plugin Name: Cyan Portal Importer
Description: وارد کردن محصولات ووکامرس از سایت دیگر به سایت شما
Version: 1.0
Author: Amirali Dizabadi
Author URI: https://github.com/amirali-dizabadi
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PLUGIN_NAME', 'cyn-portal-importer' );
define( 'PLUGIN_VERSION', '1.0' );

require_once PLUGIN_DIR . 'vendor/autoload.php';

Cyan\PortalImporter\Init::init();


