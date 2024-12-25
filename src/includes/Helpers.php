<?php
namespace Cyan\PortalImporter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Helpers {
	public static function getTemplatePart( $slug, $name = null ) {
		// Build the template path
		$template = PLUGIN_DIR . 'src/templates/' . $slug;
		if ( $name ) {
			$template .= "-{$name}.php";
		} else {
			$template .= '.php';
		}

		// Check if the template exists and include it
		if ( file_exists( $template ) ) {
			include $template;
		} else {
			// Optionally log or handle missing template errors
			error_log( "Template not found: {$template}" );
		}
	}

	public static function convertPersianToEnglish( $text ) {
		// Mapping of Persian characters to English equivalents
		$persianToEnglishMap = [ 
			// Numbers
			'۰' => '0',
			'۱' => '1',
			'۲' => '2',
			'۳' => '3',
			'۴' => '4',
			'۵' => '5',
			'۶' => '6',
			'۷' => '7',
			'۸' => '8',
			'۹' => '9',
			// Letters (Transliteration examples, extend as needed)
			'ا' => 'a',
			'ب' => 'b',
			'پ' => 'p',
			'ت' => 't',
			'ث' => 'th',
			'ج' => 'j',
			'چ' => 'ch',
			'ح' => 'h',
			'خ' => 'kh',
			'د' => 'd',
			'ذ' => 'z',
			'ر' => 'r',
			'ز' => 'z',
			'ژ' => 'zh',
			'س' => 's',
			'ش' => 'sh',
			'ص' => 's',
			'ض' => 'z',
			'ط' => 't',
			'ظ' => 'z',
			'ع' => 'a',
			'غ' => 'gh',
			'ف' => 'f',
			'ق' => 'gh',
			'ک' => 'k',
			'گ' => 'g',
			'ل' => 'l',
			'م' => 'm',
			'ن' => 'n',
			'و' => 'v',
			'ه' => 'h',
			'ی' => 'y',
			'ئ' => 'y',
			'ی' => 'i',
		];

		// Replace Persian characters with English equivalents
		return strtr( $text, $persianToEnglishMap );
	}

	public static function checkExistProductBySku( $sku ) {
		$product = wc_get_product_id_by_sku( $sku );
		return $product ? true : false;
	}

	public static function uploadImage( $url, $post_id ) {
		$response = wp_remote_get( $url );
		$image_data = wp_remote_retrieve_body( $response );
		$file = wp_upload_bits( basename( $url ), null, $image_data );
		$attachment = [ 
			'post_mime_type' => 'image/jpeg',
			'post_title' => basename( $url ),
			'post_content' => '',
			'post_status' => 'inherit',
			'post_parent' => $post_id,
		];
		return wp_insert_attachment( $attachment, $file['file'], $post_id );
	}
}