<?php

namespace Cyan\PortalImporter;

if (!defined('ABSPATH')) {
    exit;
}

class ProductColumns {
    public static function init() {
        add_filter('manage_edit-product_columns', [self::class, 'addApiSourceColumn']);
        add_action('manage_product_posts_custom_column', [self::class, 'populateApiSourceColumn'], 10, 2);
    }

    public static function addApiSourceColumn($columns) 
    {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'sku') {
                $new_columns['api_source'] = 'منبع محصول';
            }
        }
        return $new_columns;
    }

    public static function populateApiSourceColumn($column, $post_id) 
    {
        if ($column === 'api_source') {
            $api_source = get_post_meta($post_id, '_api_source', true);
            if ($api_source) {
                echo esc_html(str_replace(['https://', 'http://'], '', $api_source));
            } else {
                echo '—';
            }
        }
    }
}