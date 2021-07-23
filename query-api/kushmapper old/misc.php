<?php

// [km-alert-message]
function km_alert_message() {
    if (isset($_GET['product_added'])) {
        $product_id = $_GET['product_added'];
        if (is_numeric($product_id)) {
            $permalink = get_permalink($product_id);
            $html = '<div class="km-alert km-alert-success"><span>Product added successfully. </span><a href="' . $permalink . '">View</a></div>';
            return $html;
        }
    }
}
add_shortcode('km-alert-message', 'km_alert_message' );


// Logout shortcode: [logout]
function apc_logout_shortcode() {
    if (is_user_logged_in()) {
        // If you want to redirect the user to homepage after logout
        return '<a class="apc-logout-btn" href="' . wp_logout_url(home_url()) . '">'. esc_html__( 'Logout', 'apc_text' ) .'</a>';
    }
}
add_shortcode('logout', 'apc_logout_shortcode');



// [location]
// Use location strings
function km_location_function($atts) {
    global $geodirectory;

    // if( !geodir_is_page('location')) {
    //     return;
    // }

    $a = shortcode_atts( array(
		'type' => ''
	), $atts );

    if ($a['type'] == 'neighborhood') {
        return $geodirectory->location->neighborhood;
    }
    if ($a['type'] == 'city') {
        return $geodirectory->location->city;
    }
    if ($a['type'] == 'region') {
        return $geodirectory->location->region;
    }
    if ($a['type'] == 'country') {
        return $geodirectory->location->country;
    }

    return $geodirectory->location->city;
}

add_shortcode('location', 'km_location_function' );