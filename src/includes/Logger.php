<?php
namespace Cyan\PortalImporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Logger {

	public static function init() {
		$filenames = [ 'import', 'update' ];

		foreach ( $filenames as $filename ) {
			$filePath = PLUGIN_DIR . '/logs/' . $filename . '.log';

			if ( file_exists( $filePath ) ) {
				continue;
			}

			fopen( $filePath, 'w' );
		}
	}

	public static function log( $message, $file = 'import' ) {

		$filePath = PLUGIN_DIR . '/logs/' . $file . '.log';

		date_default_timezone_set( 'Asia/Tehran' ); // Set timezone to Iran
		file_put_contents( $filePath, date( 'Y-m-d H:i:s' ) . ' - ' . $message . PHP_EOL, FILE_APPEND );
	}

	public static function getLogs( $file = 'import' ) {
		$filePath = PLUGIN_DIR . '/logs/' . $file . '.log';
		return file_get_contents( $filePath );
	}

	public static function removeLogs( $file = 'import' ) {
		$filePath = PLUGIN_DIR . '/logs/' . $file . '.log';
		file_put_contents( $filePath, '' );
	}
}