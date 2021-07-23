<?php
require_once plugin_dir_path( __FILE__ ) . 'helpers.php';

// Product menu on single listing page
// [kush-product-menu]
function kush_product_menu_func($atts)
{
    // Ensure ACF is loaded
    if (!class_exists('acf')) {
        return "An error occurred. Please contact an administrator for assistance.";
    }

    $current_user = get_current_user_id();
    $post = get_queried_object();
    $is_author = $current_user == $post->post_author;

    $show_all = get_field('show_all_products', $post->ID);
    //$products = new WP_Query();

    if ( $show_all == "1" || $show_all == null) {
        $args1 = [ 
            'fields' => 'ids',
            'author' => $post->post_author,
            'post_type' => 'km_product',
            'post_status' => 'publish',
            'nopaging' => true,
        ];
        $products1 = new WP_Query( $args1 );

        $args2 = [
            'fields' => 'ids',
            'post_type' => 'km_product',
            'post_status' => 'publish',
            'meta_query' => array (
                array(
                    'key'     => 'listings',
                    'value'   => serialize( strval( $post->ID ) ),
                    'compare' => 'LIKE',
                ),
            ),
            'nopaging' => true,
        ];
        $products2 = new WP_Query( $args2 );
        
        $all = array_merge($products1->posts,$products2->posts);

        if (!empty($all)) {

            $products = new WP_Query(array(
                'post__in' => $all,
                'post_type' => 'km_product',
                'post_status' => 'publish',
                'nopaging' => true,
            ));
        }
    }

    if ($show_all == "0") {
        $products_ids = get_field('products', $post->ID);

        if (!empty($products_ids)) {
            $args = [ 
                'post__in' => $products_ids,
                'post_type' => 'km_product',
                'post_status' => 'publish',
                'nopaging' => true,
            ];
            $products = new WP_Query( $args );
        }
    }
    

    $html = '<div class="listing-products">';

    if (!empty($products->posts)) {
        if ( current_user_can('administrator') || $is_author ) {
            $html .= '<a href="/manage-listing-menu/?ref_id=' . $post->ID . '" class="edit-products-link btn btn-primary mb-3">Manage Products</a><br>';
        }

        $html .= '<table id="listing-products-table" style="width:100%;">';
        $html .= '<thead><tr><th><!-- Image --></th><th>Product</th><th>Category</th><th><!-- Actions --></th></tr></thead>';
        $html .= '<tbody>';

        foreach ( $products->posts as $row) {

            $meta = get_post_meta($row->ID);

            $price_1g = $meta['price_1g'][0] ? '<div class="price">$' . $meta['price_1g'][0] . ' per 1 g</div>' : "";
            $price_8th = $meta['price_8th'][0] ? '<div class="price">$' . $meta['price_8th'][0] . ' per 1/8 oz</div>' : "";
            $price_4th = $meta['price_4th'][0] ? '<div class="price">$' . $meta['price_4th'][0] . ' per 1/4 oz</div>' : "";
            $price_half = $meta['price_half'][0] ? '<div class="price">$' . $meta['price_half'][0] . ' per 1/2 oz</div>' : "";
            $price_1oz = $meta['price_1oz'][0] ? '<div class="price">$' . $meta['price_1oz'][0] . ' per 1 oz</div>' : "";

            $prices = '<div class="prices">' . $price_1g . $price_8th . $price_4th . $price_half . $price_1oz . '</div>';

            $thc = get_thc_range(['id' => $row->ID]);
            $cbd = get_cbd_range(['id' => $row->ID]);

            $category = get_the_terms($row->ID, 'km_product_category');
            $category = $category[0]->name ?? "";
            
            //$props = '<div class="properties">' . $thc . '<br>' . $cbd . '<br>' . 'Category: ' . $category . '</div>';
            $props = '<div class="properties">' . $thc . '<br>' . $cbd . '</div>';



            $image = get_field('image', $row->ID);
            $thumb = "";
            if ( $image ) {
                //$thumb = '<img src="' . $image['sizes']['thumbnail'] . '" alt="product image" />';
                $thumb = wp_get_attachment_image( $image, 'thumbnail', false, ["class" => "product-list-img"] );
            }
            $permalink = get_permalink($row->ID);
            $name = '<a class="product-name" href="' . $permalink .'?ref_id=' . $post->ID . '">' . $row->post_title . '</a>';

            $html .= '<tr><td><div class="img-container">' . $thumb . '</div></td><td><div>' . $name . '<div class="specs">' . $prices . $props . '</div></div></td><td><span class="category">' . $category . '</span></td><td><a href="'. $permalink . '?ref_id=' . $post->ID . '" class="btn btn-primary view-product">View</a>' . '</td></tr>';
            
        }

        $html .= '</tbody></table>';

    } else {
        if ( $is_author ) {
            $html .= '<a href="/manage-listing-menu/?ref_id=' . $post->ID . '" class="edit-products-link btn btn-primary mb-3">Manage Products</a><br>';
        } else {
            $html .= '<p class="h3">Menu not Available</p><p>This retailer has not added their menu yet.</p>';
        }
    }

    $html .='</div>'; // close container

    return $html;

}
add_shortcode( 'kush-product-menu', 'kush_product_menu_func' );


// [km-product]
// Product Page properties - used to populate single product page with properties via shortcodes
function km_product_prop_func($atts) {

    // Ensure ACF is loaded
    if (!class_exists('acf')) {
        return "An error occurred. Please contact an administrator for assistance.";
    }

    $a = shortcode_atts( array(
		'prop' => '',
		'label' => '',

	), $atts );

    $prop = $a['prop'];
    $post = get_queried_object();

    if ($prop == 'thc') {
        $thc = get_field('thc');
        if (!empty($thc)) {
            $html = '<p class="km-prop-label thc">THC%:</p><p class="km-prop thc">' . $thc . '</p>';
            return $html;
        }
    }
    if ($prop == 'thc_min') {
        $thc_min = get_field('thc_min');
        if (!empty($thc_min)) {
            $html = '<p class="km-prop-label thc_min">Min THC%:</p><p class="km-prop thc_min">' . $thc_min . '</p>';
            return $html;
        }
    }
    if ($prop == 'thc_max') {
        $thc_max = get_field('thc_max');
        if (!empty($thc_max)) {
            $html = '<p class="km-prop-label thc_max">Max THC%:</p><p class="km-prop thc_max">' . $thc_max . '</p>';
            return $html;
        }
    }
    if ($prop == 'cbd') {
        $cbd = get_field('cbd');
        if (!empty($cbd)) {
            $html = '<p class="km-prop-label cbd">CBD%:</p><p class="km-prop cbd">' . $cbd . '</p>';
            return $html;
        }
    }
    if ($prop == 'cbd_min') {
        $cbd_min = get_field('cbd_min');
        if (!empty($cbd_min)) {
            $html = '<p class="km-prop-label cbd_min">Min CBD%:</p><p class="km-prop cbd_min">' . $cbd_min . '</p>';
            return $html;
        }
    }
    if ($prop == 'cbd_max') {
        $cbd_max = get_field('cbd_max');
        if (!empty($cbd_max)) {
            $html = '<p class="km-prop-label cbd_max">Max CBD%:</p><p class="km-prop cbd_max">' . $cbd_max . '</p>';
            return $html;
        }
    }
    if ($prop == 'base_price') {
        $base_price = get_field('base_price');
        
        if (!empty($base_price)) {
            $html = '<p class="km-prop base_price">' . $base_price . '</p>';
            return $html;
        }
    }
    if ($prop == 'prices') {

        $currency_symbol = "$";

        $base_price = get_field('base_price');

        $price_1g = get_field('price_1g');
        $price_8th = get_field('price_8th');
        $price_4th = get_field('price_4th');
        $price_half = get_field('price_half');
        $price_1oz = get_field('price_1oz');
        
        $has_price = false;
        if (!empty($price_1g) || !empty($price_8th) || !empty($price_4th) || !empty($price_half) || !empty($price_1oz) ) {
            $has_price = true;
        }


        if(!$has_price && empty($base_price)) {
            return "Please contact the vendor for pricing.";
        }

        $html = '<p class="km-prop-h">Pricing:</p>';
        if (!empty($base_price)) {
            $html .= '<p class="km-prop base_price">' . $currency_symbol . $base_price . '</p>';
        }
        
        if (!empty($price_1g)) {
            $html .= '<p class="km-prop-label price">1g</p><p class="km-prop price">' . $currency_symbol . $price_1g . '</p>';
        }

        
        if (!empty($price_8th)) {
            $html .= '<p class="km-prop-label price">1/8 oz</p><p class="km-prop price">' . $currency_symbol . $price_8th . '</p>';
        }

        
        if (!empty($price_4th)) {
            $html .= '<p class="km-prop-label price">1/4 oz</p><p class="km-prop price">' . $currency_symbol . $price_4th . '</p>';
        }

        
        if (!empty($price_half)) {
            $html .= '<p class="km-prop-label price">1/2 oz</p><p class="km-prop price">' . $currency_symbol . $price_half . '</p>';
        }

        
        if (!empty($price_1oz)) {
            $html .= '<p class="km-prop-label price">1 oz</p><p class="km-prop price">' . $currency_symbol . $price_1oz . '</p>';
        }
        return $html;
    }
    if ($prop == 'link') {
        $url = get_field('url');
        if (!empty($url)) {
            $label = $a['label'] ?? "View Product";
            $html = '<a class="km-link" href="' . $url . '" target="_blank" ref="nofollow noopener">' . $label . '</a>';
            return $html;
        }
    }
    if ($prop == 'url') {
        $url = get_field('url');
        if (!empty($url)) {
            return $url;
        }
    }
    if ($prop == 'category') {
        $category = get_field('category');
        if (!empty($category)) {

            $html = '<p class="km-prop-h category">Category:</p>';
            $html .= '<p class="km-prop category">' . esc_html($category->name) . '</p>';
            // foreach ( $category as $cat) {
            //     $html .= '<p class="km-prop category">' . esc_html($cat->name) . '</p>';
            // }
            return $html;
        }
    }

    if ( $prop == 'vendors' ) {

        $author = $post->post_author;

        $args1 = [
            'post_type' => ['gd_place', 'gd_delivery_service'],
            'author' => $author,
            'meta_query' => [
                [
                    'key' => 'show_all_products',
                    'value' => false,
                    'compare' => '!=',
                ]
            ],
            'fields' => 'ids',
            'nopaging' => true,
        ];

        $listings1 = new WP_Query($args1);

        $args2 = [
            'post_type' => ['gd_place', 'gd_delivery_service'],
            'meta_query' => [
                [
                    'key' => 'products',
                    'value' => serialize( strval( $post->ID ) ),
                    'compare' => 'LIKE',
                ]
            ],
            'fields' => 'ids',
            'nopaging' => true,
        ];

        $listings2 = new WP_Query($args2);

        $all = array_merge($listings1->posts,$listings2->posts);

        if (empty($all)) {
            return "Vendors not found.";
        }

        $listings = new WP_Query(array(
            'post__in' => $all,
            'post_type' => ['gd_place', 'gd_delivery_service'],
            'post_status' => 'publish',
            'nopaging' => true,
        ));

        $html = "";

        if ( !empty($listings->posts) ) {
            $html .= '<p class=km-prop-h vendors>Vendors:</p>';
            $html .= '<ul class="product-vendors">';
            foreach ( $listings->posts as $listing ) {
                $permalink = get_permalink($listing->ID);
                $html .= '<li><a href="' . $permalink . '">' . $listing->post_title . '</a></li>';
            }
            $html .= '</ul>';
        }

        return $html;
    }
}
add_shortcode( 'km-product', 'km_product_prop_func' );


// [kush-product-form]
// Add product form
function km_product_form() {

    // Ensure ACF is loaded
    if (!class_exists('acf')) {
        return "An error occurred. Please contact an administrator for assistance.";
    }

    $return_link = km_return_link();

    $field_groups = ['group_5fc7bb9e98060'];

    if (current_user_can('administrator')) {
        $field_groups = false;
    }

    $settings = [
        'post_id'       => 'new_post',
        'new_post' => [
            'post_type' => 'km_product',
            'post_status'   => 'publish'
        ],
        'field_groups' => $field_groups,
        'post_title' => true,
        'post_content' => false,
        'submit_value'  => 'Add Product',
        'return' => '?product_added=%post_id%'
        
    ];
    ob_start();
    echo $return_link;
    acf_form( $settings );
    $html = ob_get_contents(); 
    ob_end_clean();
    return $html;
}
add_shortcode('kush-product-form', 'km_product_form');



// [km-edit-product]
// Edit product form
function km_edit_product_func() {

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
        'post_title' => true,
        'submit_value'  => 'Update Product',
        'field_groups' => ['group_5fc7bb9e98060']
    ];
    ob_start();
    acf_form( $settings );
    $form = ob_get_contents(); 
    ob_end_clean();
    $html = '<p><button class="edit-product">Edit Product</button></p><div id="edit-product-form">' . $form . '</div>';
    return $html;
}
add_shortcode('km-edit-product', 'km_edit_product_func');




function km_delete_product_link($post_id = null) {

    if (!$post_id) {
        $post = get_queried_object();
        $post_id = $post->ID;
    }

    $link = "";
    if ( current_user_can('delete_km_products', $post_id ) ) {
        $link = '<a class="button-link button-delete" rel="nofollow" href="' . esc_url( get_delete_post_link( $post_id ) ) . '">Delete</a>';
    }
    return $link;
}
add_shortcode( 'km-delete-product-link', 'km_delete_product_link' );




// LOCATION PRODUCTS

function km_get_location_dispensary_ids() {
    global $wpdb;
    $query = $wpdb->prepare(
        "
            SELECT post_id 
            FROM {$wpdb->prefix}geodir_gd_place_detail
            WHERE post_status = 'publish'
            AND city LIKE %s
        ",
        '%' . km_get_geodir_location() . '%');

    $ids = $wpdb->get_col(
        $query
    );

    if (!empty($ids)) {
        return $ids;
    }
    return array(0);
}



function get_product_ids_from_listing_ids($listing_ids, $listing_type) {
    $args1 = [ 
        'post__in' => $listing_ids,
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
        'post__in' => $listing_ids,
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

    return $product_ids;
}

function km_products_html() {

    // Don't show on elementor search template
    if(isset($_GET['action'])) {
        if ($_GET['action'] == "elementor") {
            return false;
        }
    }

    $dispensary_ids = km_get_location_dispensary_ids();

    $product_ids = get_product_ids_from_listing_ids($dispensary_ids, "gd_place");

    //reduce results
    $flipped = array_flip($product_ids);
    $product_ids = array_rand($flipped, 24);

    if (empty($product_ids)) {
        return "None found.";
    }

    $args = [
        'post_type' => 'km_product',
        'post_status' => 'publish',
        'nopaging' => true,
        'post__in' => $product_ids,
    ];

    $products = new WP_Query($args);

    $html = '<div class="product-container">';

    foreach($products->posts as $product) {

        $meta = get_post_meta($product->ID, '');

        $image = $meta['image'][0];
        $thumb = "";
        if ( $image ) {
           $thumb = wp_get_attachment_image( $image, 'thumb', false, ["class" => "location-prod-img"] );
        }

        $price_1g = $meta['price_1g'][0] ? '<div class="price">$' . $meta['price_1g'][0] . ' per 1 g</div>' : "";
        $price_8th = $meta['price_8th'][0] ? '<div class="price">$' . $meta['price_8th'][0] . ' per 1/8 oz</div>' : "";
        $price_4th = $meta['price_4th'][0] ? '<div class="price">$' . $meta['price_4th'][0] . ' per 1/4 oz</div>' : "";
        $price_half = $meta['price_half'][0] ? '<div class="price">$' . $meta['price_half'][0] . ' per 1/2 oz</div>' : "";
        $price_1oz = $meta['price_1oz'][0] ? '<div class="price">$' . $meta['price_1oz'][0] . ' per 1 oz</div>' : "";

        $prices = '<div class="prices">' . $price_1g . $price_8th . $price_4th . $price_half . $price_1oz . '</div>';

        $url = get_permalink($product->ID);

        $html .= '<div class="item-container"><div class="product-item">' . '<div class="image"><a href="'. $url . '">' . $thumb . '</a></div><div class="product-name"><a href="' . $url . '">' . $product->post_title . '</a></div>' . $prices . '</div></div>';
    }
    return $html;
}
add_shortcode( 'location-products', 'km_products_html' );



function get_lowest_price() {
    global $post;

    $prices = [];

    $meta = get_post_meta($post->ID);

    $prices['per 1 g'] = $meta['price_1g'][0] ?? null;
    $prices['per 1/8 oz'] = $meta['price_8th'][0] ?? null;
    $prices['per 1/4 oz'] = $meta['price_4th'][0] ?? null;
    $prices['per 1/2 oz'] = $meta['price_half'][0] ?? null;
    $prices['per 1 oz'] = $meta['price_1oz'][0] ?? null;
    $prices['each'] = $meta['base_price'][0] ?? null;

    $lowest = array_reduce($prices, function($carry, $item) {

        if ($carry == null) {
            return $item;
        }

        if ($item != null && $item < $carry ) {
            return $item;
        }
        
        return $carry;
    });

    if ( empty($lowest) ) {
        return;
    }

    $suffix = array_search($lowest, $prices);

    $html = '<span class="low-price">$' . $lowest . ' ' . $suffix . '</span>';

    return $html;

}
add_shortcode('lowest-price', 'get_lowest_price');

function get_thc_range($atts) {
    global $post;
    
    $a = shortcode_atts( [
        'show_label' => "true",
        'label' => null,
        'id' => null
    ], $atts);
    
    $meta = $a['id'] ? get_post_meta($a['id']) : get_post_meta($post->ID);

    $thc = $meta['thc'][0];
    $thc_min = $meta['thc_min'][0];
    $thc_max = $meta['thc_max'][0];

    $label_text = "THC: ";
    $label = $a['show_label'] == "true" ? $label_text : "";

    if (!empty($thc)) {
        $label_text = $a['label'] ?? "THC: ";
        $label = $a['show_label'] == "true" ? $label_text : "";
        return $label . $thc . '%';
    }
    if (!empty($thc_min) && !empty($thc_max)) {
        $label_text = $a['label'] ?? 'THC: ';
        $label = $a['show_label'] == "true" ? $label_text : "";
        return  $label . $thc_min . '% - ' . $thc_max . '%';
    }
    if (!empty($thc_min)) {
        $label_text = $a['label'] ?? 'Min. THC: ';
        $label = $a['show_label'] == "true" ? $label_text : "";
        return  $label . $thc_min;
    }
    return $label . "Not Available";
}
add_shortcode('thc-range', 'get_thc_range');

function get_cbd_range($atts) {
    global $post;
    
    $a = shortcode_atts( [
        'show_label' => "true",
        'label' => null,
        'id' => null
    ], $atts);
    
    $meta = $a['id'] ? get_post_meta($a['id']) : get_post_meta($post->ID);

    $cbd = $meta['cbd'][0];
    $cbd_min = $meta['cbd_min'][0];
    $cbd_max = $meta['cbd_max'][0];

    $label_text = "CBD: ";
    $label = $a['show_label'] == "true" ? $label_text : "";

    if (!empty($cbd)) {
        $label_text = $a['label'] ?? "CBD: ";
        $label = $a['show_label'] == "true" ? $label_text : "";
        return $label . $cbd . '%';
    }
    if (!empty($cbd_min) && !empty($cbd_max)) {
        $label_text = $a['label'] ?? 'CBD: ';
        $label = $a['show_label'] == "true" ? $label_text : "";
        return  $label . $cbd_min . '% - ' . $cbd_max . '%';
    }
    if (!empty($cbd_min)) {
        $label_text = $a['label'] ?? 'Min. CBD: ';
        $label = $a['show_label'] == "true" ? $label_text : "";
        return  $label . $cbd_min;
    }
    return $label . "Not Available";
}
add_shortcode('cbd-range', 'get_cbd_range');