<?php
require_once plugin_dir_path( __FILE__ ) . 'helpers.php';

// Localized Delivery Service 
function km_get_location_delivery_ids() {

    $location = km_get_geodir_location();

    if ( empty($location) ) {
        return array(0);
    }

    global $wpdb;
    $query = $wpdb->prepare(
        "
            SELECT post_id 
            FROM {$wpdb->prefix}geodir_gd_delivery_service_detail
            WHERE post_status = 'publish'
            AND location LIKE %s
        ",
        '%' . $location . '%');

    $ids = $wpdb->get_col(
        $query
    );

    if (!empty($ids)) {
        return $ids;
    }
    return array(0);
}

// elementor query
add_action( 'elementor/query/location_ds_filter', function( $query ) {

    $query->set('post_type', 'gd_delivery_service');
    $query->set('post__in', km_get_location_delivery_ids());
    //$query->set('nopaging', true);
    $query->set('post_status', 'publish');
} );
// elementor query
add_action( 'elementor/query/local_delivery_filter', function( $query ) {

    $query->set('post_type', 'gd_delivery_service');
    $query->set('post__in', km_get_location_delivery_ids());
    //$query->set('nopaging', true);
    $query->set('post_status', 'publish');
} );



// Mail order Delivery Service
function km_get_mailorder_delivery_ids() {

    $mailorder = 'Yes';

    global $wpdb;
    $query = $wpdb->prepare(
        "
            SELECT post_id 
            FROM {$wpdb->prefix}geodir_gd_delivery_service_detail
            WHERE post_status = 'publish'
            AND mail_order = %s
        ",
        $mailorder);

    $ids = $wpdb->get_col(
        $query
    );

    if (!empty($ids)) {
        return $ids;
    }
    return array(0);
}

// elementor query
add_action( 'elementor/query/ds_mailorder_filter', function( $query ) {
    
    $query->set('post_type', 'gd_delivery_service');
    $query->set('post__in', km_get_mailorder_delivery_ids());
    //$query->set('nopaging', true);
    $query->set('post_status', 'publish');
} );

// Global featured products
add_action( 'elementor/query/product_featured_filter', function( $query ) {
    
    $query->set('post_type', 'km_product');
    $query->set('post_status', 'publish');
    $query->set('meta_query', array(
        array(
            'key' => 'featured',
            'value' => '1'
        )
    ));
} );




// Localized Dispensary Query
function km_get_local_dispensary_ids() {

    global $wpdb;

    $city = km_get_geodir_location();

    if (empty($city)) {
        return array(0);
    }

    $query = $wpdb->prepare(
        "
            SELECT post_id 
            FROM {$wpdb->prefix}geodir_gd_place_detail
            WHERE post_status = 'publish'
            AND city = %s
        ",
        $city);

    $ids = $wpdb->get_col(
        $query
    );

    if (!empty($ids)) {
        return $ids;
    }
    return array(0);
}

add_action( 'elementor/query/local_dispensary_filter', function( $query ) {
    
    $query->set('post_type', 'gd_place');
    $query->set('post_status', 'publish');
    $query->set('post__in', km_get_local_dispensary_ids());
} );


// Mail Order Products
add_action( 'elementor/query/mailorder_product_filter', function( $query ) {
    
    $query->set('post_type', 'km_product');
    $query->set('post_status', 'publish');
    $query->set('meta_query', array(
        array(
            'key' => 'mail_order',
            'value' => '1',
        )
    ));
} );


// Localized Products
add_action( 'elementor/query/local_product_filter', function( $query ) {
    
    $query->set('post_type', 'km_product');
    $query->set('post_status', 'publish');
    $query->set('meta_query', array(
        array(
            'key' => 'cities',
            'value' => km_get_geodir_location(),
            'compare' => 'LIKE'
        )
    ));
    $query->set( 'tax_query', array(
        'taxonomy' => 'km_product_category',
        'terms'    => array( 56, 57 ),
        'operator' => 'NOT IN'
    ) ); // exclude 'gear', 'other' categories
} );



// Only allow user to associate product with listings they own
add_filter('acf/fields/relationship/query/name=listing_id', 'km_product_to_listing_relationship_query', 10, 3);
function km_product_to_listing_relationship_query( $args, $field, $post_id ) {

    if ( current_user_can( 'administrator' ) ) {
        return $args;
    }

    $args['author'] = get_current_user_id();

    return $args;
}



// Mail order delivery services
function km_get_mail_order_ds_ids() {

    global $wpdb;

    $query = $wpdb->prepare(
        "
            SELECT post_id 
            FROM {$wpdb->prefix}geodir_gd_delivery_service_detail
            WHERE post_status = 'publish'
            AND mail_order = %s
        ",
        "Yes");

    $ids = $wpdb->get_col(
        $query
    );

    if (!empty($ids)) {
        return $ids;
    }
    return array(0);
}

add_action( 'elementor/query/mail_order_ds_filter', function( $query ) {
    $query->set('post_type', 'gd_delivery_service');
    $query->set('post_status', 'publish');
    $query->set('post__in', km_get_mail_order_ds_ids());
} );


// Mail order dispensaries
function km_get_mail_order_dispensary_ids() {

    global $wpdb;

    $query = $wpdb->prepare(
        "
            SELECT post_id 
            FROM {$wpdb->prefix}geodir_gd_place_detail
            WHERE post_status = 'publish'
            AND purchase_options LIKE %s
        ",
        '%' . "Order Online" . '%');

    $ids = $wpdb->get_col(
        $query
    );

    if (!empty($ids)) {
        return $ids;
    }
    return array(0);
}

add_action( 'elementor/query/mail_order_dispensary_filter', function( $query ) {
    $query->set('post_type', 'gd_place');
    $query->set('post_status', 'publish');
    $query->set('post__in', km_get_mail_order_dispensary_ids());
} );


// Featured Listings
function km_get_featured_local_listings($type = "dispensary") {

    $location = km_get_geodir_location();

    if ( empty($location) ) {
        return null;
    }

    global $wpdb;

    if ($type == "dispensary") {
        $listing_sql = "{$wpdb->prefix}geodir_gd_place_detail";

        $query = $wpdb->prepare(
            "
                SELECT post_id 
                FROM $listing_sql
                WHERE post_status = 'publish'
                AND featured = 1
                AND city = %s
            ",
            $location);
    }


    if ($type == "delivery") {
        $listing_sql = "{$wpdb->prefix}geodir_gd_delivery_service_detail";

        $query = $wpdb->prepare(
            "
                SELECT post_id 
                FROM $listing_sql
                WHERE post_status = 'publish'
                AND featured = 1
                AND location LIKE %s
            ",
            '%' . $location . '%');
    }

    $ids = $wpdb->get_col(
        $query
    );

    if (!empty($ids)) {
        return $ids;
    }
    return null;
}

add_action( 'elementor/query/featured_local_deliveries_filter', function( $query ) {

    $ids = km_get_featured_local_listings("delivery");

    if ($ids === null) {
        // fall back to random
        $ids = km_get_location_delivery_ids();
    }

    $query->set('post_type', 'gd_delivery_service');
    $query->set('post_status', 'publish');
    $query->set('post__in', $ids);
} );

add_action( 'elementor/query/featured_local_dispensaries_filter', function( $query ) {

    $ids = km_get_featured_local_listings("dispensary");

    if ($ids === null) {
        // fall back to random
        $ids = km_get_local_dispensary_ids();
        
    }

    $query->set('post_type', 'gd_place');
    $query->set('post_status', 'publish');
    $query->set('post__in', $ids);
} );

// Has Featured

function local_has_featured($atts) {


     // Do nothing if editing template
     if(isset($_GET['action'])) {
        if ($_GET['action'] == "elementor") {
            return 1;
        }
    }

    $a = shortcode_atts( array(
		'type' => 'delivery'
	), $atts );

    $location = km_get_geodir_location();

    if ( empty($location) ) {
        return 0;
    }

    global $wpdb;

    if ($a['type'] == "dispensary") {
        $listing_sql = "{$wpdb->prefix}geodir_gd_place_detail";

        $query = $wpdb->prepare(
            "
                SELECT COUNT(post_id) as NumberOfListings
                FROM $listing_sql
                WHERE post_status = 'publish'
                AND featured = 1
                AND city = %s
            ",
            $location);
    }


    if ($a['type'] == "delivery") {
        $listing_sql = "{$wpdb->prefix}geodir_gd_delivery_service_detail";

        $query = $wpdb->prepare(
            "
                SELECT COUNT(post_id) as NumberOfListings
                FROM $listing_sql
                WHERE post_status = 'publish'
                AND featured = 1
                AND location LIKE %s
            ",
            '%' . $location . '%');
    }

    $count = $wpdb->get_var(
        $query
    );

    if ( !is_numeric($count)) {
        return 0;
    }

    return $count;

}
add_shortcode('local-has-featured', 'local_has_featured');