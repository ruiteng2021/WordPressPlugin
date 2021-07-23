<?php

/* NOT USED */

// Not used?
// Listing Menu Options
function km_listing_menu() {
    // Ensure ACF is loaded
    if (!class_exists('acf')) {
        return "An error occurred. Please contact an administrator for assistance.";
    }

    $current_user = get_current_user_id();
    $post = get_queried_object();
    $is_author = $current_user == $post->post_author;

    if ( !current_user_can('administrator') && !$is_author ) {
        return;
    }

    $settings = [
        'post_id'       => $post->ID,
        'submit_value'  => 'Update Product Menu',
    ];
    ob_start();
    acf_form( $settings );
    $form = ob_get_contents(); 
    ob_end_clean();
    $html = '<p><button class="edit-product">Edit Product Menu</button></p><div id="edit-product-form">' . $form . '</div>';
    return $html;
}
add_shortcode('km-listing-menu', 'km_listing_menu');

// Products by location-queried listings
function km_queried_listing_products($current_query = null, $limit = null) {

    if ( empty($current_query) ) {
        global $wp_query;
        $listing_query = $wp_query;
    } else {
        $listing_query = $current_query;
    }

    //$listing_type = ['gd_place']; // currently only works for gd_place
    $listing_type = $listing_query->get('post_type');


    $listing_query->set('fields', 'ids');
    $listing_query->set('nopaging', true);

    $listing_ids = new WP_Query($listing_query->query_vars);


    // reduce listings?
    $reduced_listing_ids = array_rand($listing_ids->posts, $limit);

    // Get all listings that have 'show all products' enabled
    $args1 = [ 
        'post__in' => $reduced_listing_ids,
        'post_type' => $listing_type,
        'post_status' => 'publish',
        'meta_query' => [
            [
                'key' => 'show_all_products',
                'value' => false,
                'compare' => '!=',
            ]
        ],
        'nopaging' => true,
    ];
    $listings_all_products = new WP_Query( $args1 );

    // Get the authors of those listings
    $author_ids_all = [];
    foreach ($listings_all_products->posts as $listing) {
        array_push($author_ids_all, $listing->post_author);
    }

    // Get all listings that show only specific products
    $args2 = [ 
        'post__in' => $listing_ids->posts,
        'post_type' => $listing_type,
        'post_status' => 'publish',
        'meta_query' => [
            [
                'key' => 'show_all_products',
                'value' => '0',
            ]
        ],
        'fields' => 'ids',
        'nopaging' => true,
    ];
    $listings_some_products = new WP_Query( $args2 );

    if (empty($listings_all_products->posts) && empty($listings_some_products)) {
        return false;
    }

    // Get all products from authors who show all products
    $args3 = [
        'post_type' => 'km_product',
        'post_status' => 'publish',
        'nopaging' => true,
        'author__in' => $author_ids_all,
        'fields' => 'ids',
    ];

    $products_all = new WP_Query($args3);

    // Get 'specific' products from listings with 'show all products' == false
    $products_some = [];

    foreach ( $listings_some_products->posts as $listing) {

        $products = get_field('products', $listing);

        foreach ($products as $product) {
            array_push($products_some, $product);
        }
    }

    // Flatten $products_some
    if ( !empty($products_some) ) {
        array_values($products_some);
    }

    // Merge $products_all with $products_some
    $product_ids = array_merge($products_all->posts, $products_some);

    // if no products
    if (empty($product_ids)) {
        return false;
    }
    
    // Finally get all products
    $args4 = [
        'post__in' =>  $product_ids,
        'post_type' => 'km_product',
        'post_status' => 'publish',
        'nopaging' => true,
    ];

    $products = new WP_Query($args4);

    if ( empty($current_query) ) {
        return $products;
    } else {
        $current_query->set('post_type', 'km_product');
        $current_query->set('post__in', $product_ids);
        return $current_query;
    }

}

function km_products_table($atts) {

    $a = shortcode_atts( array(
		'type' => '',
		'title' => '',
	), $atts );

    // Don't show on elementor search template
    if(isset($_GET['action'])) {
        if ($_GET['action'] == "elementor") {
            return false;
        }
    }

    if ($a['type'] == "all") {
        $args = [
            'post_type' => 'km_product',
            'status' => 'publish',
            'nopaging' => true,
        ];
        $products = new WP_Query($args);
        $section_title = $a['title'];

    } else {
        $products = km_queried_listing_products();
        $section_title = $a['title'];
        
    }

    if($products === false) {
        return '<script>var products=[];</script>';
    }

    $category_array = get_terms([
        'taxonomy' => 'km_product_category',
        'fields' => 'names',
    ]);

    array_unshift($category_array, "");

    $products_array = [];

    // $terms = get_terms( array(
    //     'taxonomy' => 'km_product_category',
    //     'hide_empty' => false,
    // ) );

    foreach ($products->posts as $product) {

        $product_data = [];

        // --------------------------------

        $meta = get_post_meta($product->ID);

        $image = $meta['image'][0];
        $thumb = "";
        if ( $image ) {
           $thumb = wp_get_attachment_image( $image, 'thumbnail', false, ["class" => "product-list-img"] );
        }

        $product_data['name'] = $product->post_title;
        $product_data['url'] = get_permalink($product->ID);
        $product_data['thc'] = $meta['thc'][0];
        $product_data['thc_min'] = $meta['thc_min'][0];
        $product_data['thc_max'] = $meta['thc_max'][0];
        $product_data['cbd'] = $meta['cbd'][0];
        $product_data['cbd_min'] = $meta['cbd_min'][0];
        $product_data['cbd_max'] = $meta['cbd_max'][0];
        
        $category = get_term($meta['category'][0], 'km_product_category');
        $cat_id = array_search($category->name, $category_array);
        //$category = km_match_term($meta['category'][0], $terms);
        //$cat_id = array_search($category, $category_array);
        $product_data['category'] = $cat_id;
        

        $product_data['base_price'] = $meta['base_price'][0];
        $product_data['price_1g'] = $meta['price_1g'][0];
        $product_data['price_8th'] = $meta['price_8th'][0];
        $product_data['price_4th'] = $meta['price_4th'][0];
        $product_data['price_half'] = $meta['price_half'][0];
        $product_data['price_1oz'] = $meta['price_1oz'][0];
        $product_data['image'] = $thumb;

        // --------------------------------

        // $image = get_field('image', $product->ID);
        // $thumb = "";
        // if ( $image ) {
        //     $thumb = wp_get_attachment_image( $image, 'thumbnail', false, ["class" => "product-list-img"] );
        // }

        // $product_data['name'] = $product->post_title;
        // $product_data['url'] = get_permalink($product->ID);
        // $product_data['thc'] = get_field('thc', $product->ID);
        // $product_data['thc_min'] = get_field('thc_min', $product->ID);
        // $product_data['thc_max'] = get_field('thc_max', $product->ID);
        // $product_data['cbd'] = get_field('cbd', $product->ID);
        // $product_data['cbd_min'] = get_field('cbd_min', $product->ID);
        // $product_data['cbd_max'] = get_field('cbd_max', $product->ID);
        
        // $category = get_term(get_field('category', $product->ID), 'km_product_category');
        // $cat_id = array_search($category->name, $category_array);
        // $product_data['category'] = $cat_id;

        // $product_data['base_price'] = get_field('base_price', $product->ID);
        // $product_data['price_1g'] = get_field('price_1g', $product->ID);
        // $product_data['price_8th'] = get_field('price_8th', $product->ID);
        // $product_data['price_4th'] = get_field('price_4th', $product->ID);
        // $product_data['price_half'] = get_field('price_half', $product->ID);
        // $product_data['price_1oz'] = get_field('price_1oz', $product->ID);
        // $product_data['image'] = $thumb;

        
        $products_array[] = $product_data;
    }

    return '<script>var products=' . json_encode($products_array) . ';' . 'var product_categories=' . json_encode($category_array) . ';</script> <span style="font-size: 24px;">'.$section_title.'</span><div id="product-grid"></div><button id="apply-filters">Apply Filters</button><button id="reset-filters">Reset Filters</button>';

}
add_shortcode( 'km-products-table', 'km_products_table' );