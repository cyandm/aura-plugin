<?php

namespace Cyan\PortalImporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Init {
	public static function init() {
		Menu::init();
		Assets::init();
		Rest::init();
		Logger::init();
	}
}

