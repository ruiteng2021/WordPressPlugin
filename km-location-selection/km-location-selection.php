<?php
/**
 * Plugin Name: Kushmapper location selection
 * Description: Nearst location auto detect and list all locations
 * Author: SlyFox
 * Version: 0.1.1
 */

function add_km_location_selection_scripts(){
    wp_enqueue_script( 'km-location-selection-script', plugin_dir_url( __FILE__ ) . 'km-location-selection.js', array('jquery'), '1.0.0', true);
}
add_action( 'wp_enqueue_scripts', 'add_km_location_selection_scripts' );

add_shortcode('location-selection', 'location_selection');
function location_selection()
{ 
    $html .= '<div class="columns is-full">';
    $html .= '<div class="column">';
    $html .= '<select class="km-location-select" data-placeholder="Select a City" onchange="sendLocation(this); storeHistory(this)">';
    $html .= '</select>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;

}//end location_selection()
