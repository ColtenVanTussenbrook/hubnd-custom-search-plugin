<?php
/*
Plugin Name: Custom Search Term Plugin
Plugin URI: https://coltenv.com
Description: Plugin to create a custom search that places user search term directly into another site's search query
Version: 1.0
Author: Colten Van Tussenbrook
Author URI: https://coltenv.com
License: GPLv2 or later
*/

// fire up the plugin
require_once dirname( __FILE__ ) .'/custom-search.php';
require_once dirname( __FILE__ ) .'/search-urls-custom-post-type.php';
require_once dirname(__FILE__) . '/documentation.php';

function custom_search_css() {
    $css_path = plugin_dir_url( __FILE__ ) . '/css/style.css';
    wp_enqueue_style( 'style.css', $css_path); 
}

function admin_custom_css() {
    $css_path = plugin_dir_url( __FILE__ ) . '/css/admin-style.css';
    wp_enqueue_style( 'admin-style.css', $css_path);
}

add_action( 'wp_enqueue_scripts', 'custom_search_css' );
add_action( 'admin_enqueue_scripts', 'admin_custom_css' );
 