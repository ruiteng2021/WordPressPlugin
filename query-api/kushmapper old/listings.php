<?php
require_once plugin_dir_path( __FILE__ ) . 'helpers.php';

// [km-return-link]
// return to listing link (requires ref_id url param)
function km_return_link() {
    $return_url = "";
    $return_link = "";

    if (isset($_GET['ref_id'])) {
        $ref_id = $_GET['ref_id'];
        if (is_numeric($ref_id)) {
            $listing = get_post($ref_id);
            if ($listing) {
                $return_url = get_permalink($listing->ID);
            }
        }
    }
    if (!empty($return_url)) {
        $return_link = '<a href="' . $return_url . '" class="button-link">Return to Listing</a>';
    }
    return $return_link;
}
add_shortcode('km-return-link', 'km_return_link');

//[km-location-back]
function km_location_back($atts) {

    $a = shortcode_atts( [
        'label' => '',
    ], $atts);

    $return_url = "";
    $return_link = "";

    $city = km_get_geodir_location();
    $region = km_get_geodir_location('region');
    $country = km_get_geodir_location('country');

    if ( empty($city) ) {
        return;
    }


    $label = $a['label'] == '' ? 'Return to ' . $city . ', ' . $region . ' Homepage' : htmlspecialchars($a['label']);

    $return_url = "/location/" . sanitize_title($country) . '/' . sanitize_title($region) . '/' . sanitize_title($city) . "/";

    if (!empty($return_url)) {
        $return_link = '<a href="' . $return_url . '" class="location-back-link">' . $label . '</a>';
    }
    return $return_link;
}
add_shortcode('km-location-back', 'km_location_back');


//local subpage links
function km_local_subpage_link($atts) {

    $a = shortcode_atts( array(
		'type' => 'dispensary',
		'html' => "true",
        'label' => '',
        'button' => "true"
	), $atts );

    $city = sanitize_text_field(km_get_geodir_location());
    $region = sanitize_text_field(km_get_geodir_location("region"));
    $country = sanitize_text_field(km_get_geodir_location("country"));

    if (empty($city)) {
        return;
    }

    $url = "";
    $html = "";
    $button = $a['button'] == "true" ? "button" : "";

    if ($a['type'] == "dispensary") {
        $label = empty($a['label']) ? $label = "Search $city Weed Dispensary Database" : htmlspecialchars($a['label']);
        //$url = '/local-dispensaries/?city=' . sanitize_title($city) . '&region=' . sanitize_title($region);
        $url = '/local-dispensaries/' . sanitize_title($country) . '/' . sanitize_title($region) . '/' . sanitize_title($city);
        $html = "<a href=\"$url\" class=\"local-link $button\">$label</a>";
    }
    if ($a['type'] == "delivery") {
        $label = empty($a['label']) ? $label = "Search $city Weed Delivery Service Database" : htmlspecialchars($a['label']);
        //$url = '/local-delivery/?city=' . sanitize_title($city) . '&region=' . sanitize_title($region);
        $url = '/local-delivery/' . sanitize_title($country) . '/' . sanitize_title($region) . '/' . sanitize_title($city);
        $html = "<a href=\"$url\" class=\"local-link $button\">$label</a>";
    }
    if ($a['type'] == "products") {
        $label = empty($a['label']) ? $label = "Search $city Weed Product Database" : htmlspecialchars($a['label']);
        //$url = '/local-products/?city=' . sanitize_title($city) . '&region=' . sanitize_title($region);
        $url = '/local-products/' . sanitize_title($country) . '/' . sanitize_title($region) . '/' . sanitize_title($city);
        $html = "<a href=\"$url\" class=\"local-link $button\">$label</a>";
    }

    $result = $a['html'] == "false" ? $url : $html;
    return $result;

}
add_shortcode('local-link', 'km_local_subpage_link');

// Listing Menu Options (separate page)
function km_listing_menu_separate() {
    // Ensure ACF is loaded
    if (!class_exists('acf')) {
        return "An error occurred. Please contact an administrator for assistance.";
    }

    if (!isset($_GET['ref_id'])) {
         return "No listing found.";
    }

    $ref_id = $_GET['ref_id'];

    if ( empty($ref_id) || !is_numeric($ref_id) ) {
        return "No listing found.";
    }

    $post = get_post($ref_id);

    if (!$post) {
        return "No listing found.";
    }

    if (!in_array($post->post_type, ['gd_place', 'gd_delivery_service'])) {
        return "Invalid listing id.";
    }

    $current_user = get_current_user_id();
    $is_author = $current_user == $post->post_author;
    $permalink = get_permalink($post->ID);

    if ( !current_user_can('administrator') && !$is_author ) {
        return 'Unauthorized';
    }

    $settings = [
        'post_id'       => $post->ID,
        'submit_value'  => 'Update Listing Details',
    ];
    ob_start();
    echo "<h1>Menu settings for " . $post->post_title . "</h1>";
    acf_form( $settings );
    echo '<a class="m16 button-link" href="/add-products/' . '?ref_id=' . $ref_id .  '">Add New Product to Account</a>';
    echo '<a class="m16 button-link" href="' . $permalink . '">Return to Listing</a>';
    $form = ob_get_contents(); 
    ob_end_clean();
    $html = '<div id="edit-product-form-separate">' . $form . '</div>';
    return $html;
}
add_shortcode('km-listing-menu-separate', 'km_listing_menu_separate');


function show_add_listing_form() {

    if ( !isset($_GET['pid']) ) {
        return;
    }

    ob_start();
    echo do_shortcode('[gd_notifications]');
    echo do_shortcode('[gd_add_listing show_login=0]');
    return ob_get_clean();
}
add_shortcode('edit-listing-form','show_add_listing_form');