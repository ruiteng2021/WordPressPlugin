<?php
/**
 * Plugin name: Query APIs
 * Description: Get information from kushmapper APIs in WordPress
 * Author: Rui Teng
 * version: 0.1.0
 * License: GPL2 or later.
 * text-domain: query-apis
 */

use AC\Asset\Script;

// If this file is access directly, abort!!!
defined( 'ABSPATH' ) or die( 'Unauthorized Access' );

function add_scripts(){
    wp_enqueue_script( 'api_script', plugin_dir_url( __FILE__ ) . 'apiscript.js', array('jquery'), '1.0.0', true);
}

add_action( 'wp_enqueue_scripts', 'add_scripts' );
// add_action( 'wp_footer', 'add_scripts' );

// create posts data .
add_shortcode('external_data', 'add_kusgmapper_data');

function add_kusgmapper_data(){

    $html = '';
    $html .= '<table id="vendorData">';
    $html .= '</table>';
    return $html;
}