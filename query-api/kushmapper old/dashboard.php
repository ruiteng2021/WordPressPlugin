<?php

// ['km-my-listings']
function km_my_listings() {

    $author = wp_get_current_user();

    if ( $author->ID == 0 ) {
        return "Please log in to view account details.";
    }
    
    $args = [
        'post_type' => ['gd_place', 'gd_delivery_service'],
        'author' => $author->ID,
        'nopaging' => true,
    ];

    $listings = new WP_Query($args);

    $html = '<p>No listings found. <a href="/add-listing">Add a Listing</a></p>';

    if ( empty($listings->posts)) {
        return $html;
    }

    $html = '<table id="listings-table" style="width:100%;">';
    $html .= '<thead><tr><th>Name</th><th>Type</th><th><!-- Actions --></th></tr></thead>';
    $html .= '<tbody>';

    foreach ( $listings->posts as $listing ) {
        $permalink = get_permalink($listing->ID);
        $type_slug = $listing->post_type;
        $type = "Other";
        if ( $type_slug == 'gd_place' ) { $type = "Dispensary"; }
        if ( $type_slug == 'gd_delivery_service' ) { $type = "Delivery Service"; }

        $html .= '<tr><td><a href="' . $permalink . '">' . $listing->post_title . '</a></td><td>' . $type . '</td><td>' . '<a class="button-link button-view" href="' . $permalink . '">View</a>'. 
        '<a class="button-link button-view" href="/manage-listing-menu/?ref_id=' . $listing->ID . '">Manage Menu</a></td></tr>';
    }

    $html .= '</tbody></table>';

    return $html;
    ///manage-listing-menu/?ref_id=2230   
}
add_shortcode('km-my-listings', 'km_my_listings');

// ['km-my-products']
function km_my_products() {

    $author = wp_get_current_user();
    
    if ( $author->ID == 0 ) {
        return "Please log in to view account details.";
    }
    
    $args = [
        'post_type' => ['km_product'],
        'author' => $author->ID,
        'nopaging' => true,
    ];

    $products = new WP_Query($args);

    $html = '<p>No products found.  <a href="/add-products">Add Products</a></p>';

    if ( empty($products->posts)) {
        return $html;
    }

    $html = '<table id="my-products-table" style="width:100%;">';
    $html .= '<thead><tr><th>Name</th><th>Category</th><th><!-- Actions --></th></tr></thead>';
    $html .= '<tbody>';

    foreach ( $products->posts as $listing ) {
        $permalink = get_permalink($listing->ID);
        $type = get_field('category', $listing->ID);

        $html .= '<tr><td><a href="' . $permalink . '">' . $listing->post_title . '</a></td><td>' . $type->name . '</td><td>' . '<a class="button-link button-view" href="' . $permalink . '">View/Edit </a>' . km_delete_product_link($listing->ID) . '</td></tr>';
    }

    $html .= '</tbody></table>';

    return $html;
    
}
add_shortcode('km-my-products', 'km_my_products');