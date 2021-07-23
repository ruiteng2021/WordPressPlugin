<?php
/**
 * Plugin Name: KushMapper Plugin
 * Description: Custom functions for KushMapper
 * Author: SlyFox
 * Version: 1.0
 */

define( 'KM_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'KM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once KM_PLUGIN_PATH . 'helpers.php';
require_once KM_PLUGIN_PATH . 'products.php';
require_once KM_PLUGIN_PATH . 'queries.php';
require_once KM_PLUGIN_PATH . 'listings.php';
require_once KM_PLUGIN_PATH . 'dashboard.php';
require_once KM_PLUGIN_PATH . 'misc.php';
require_once KM_PLUGIN_PATH . 'rewrite.php';

// Scripts
function km_scripts() {
    wp_enqueue_style( 'datatables', 'https://cdn.datatables.net/v/dt/dt-1.10.22/datatables.min.css' );
    wp_enqueue_style( 'jsgrid', 'https://cdnjs.cloudflare.com/ajax/libs/jsgrid/1.5.3/jsgrid.min.css' );
    wp_enqueue_style( 'jsgrid-theme', 'https://cdnjs.cloudflare.com/ajax/libs/jsgrid/1.5.3/jsgrid-theme.min.css' );
    wp_enqueue_script( 'datatables', 'https://cdn.datatables.net/v/dt/dt-1.10.22/datatables.min.js', array('jquery'), '1.10.22', true );
    wp_enqueue_script( 'jsgrid', 'https://cdnjs.cloudflare.com/ajax/libs/jsgrid/1.5.3/jsgrid.min.js', array('jquery'), '1.5.3', true );
    wp_enqueue_script( 'kushmapper-plugin', KM_PLUGIN_URL . 'includes/kushmapper.js', array('jquery', 'jsgrid'), '1.0', true );
    
    
    wp_enqueue_style( 'slick', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css' );
    wp_enqueue_script( 'slick', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), '1.8.1', true );
    
    
    // wp_enqueue_style( 'tiny-slider', 'https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.3/tiny-slider.css' );
    // wp_enqueue_script( 'tiny-slider', 'https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.2/min/tiny-slider.js', array('jquery'), '2.9.2', true );
    // wp_enqueue_style( 'datatables', 'https://cdn.datatables.net/v/bs4/dt-1.10.22/b-1.6.5/datatables.min.css' );
    // wp_enqueue_script( 'datatables', 'https://cdn.datatables.net/v/bs4/dt-1.10.22/b-1.6.5/datatables.min.js', array('jquery'), '1.10.22', true );
}
add_action( 'wp_enqueue_scripts', 'km_scripts' );


add_action( 'init', 'km_acf_form_head' );
function km_acf_form_head(){
  if (!is_admin()) {
    acf_form_head();
  }
}