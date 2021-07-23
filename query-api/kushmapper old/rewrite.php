<?php

function km_vars($vars){
    $vars[] = 'city';
    $vars[] = 'region';
    $vars[] = 'country';
    return $vars;
}

function km_tags() {
    add_rewrite_tag('%city%', '([^&]+)');
    add_rewrite_tag('%region%', '([^&]+)');
    add_rewrite_tag('%country%', '([^&]+)');
}
add_action('init', 'km_tags');

function km_rewrite_rules() {
    //add_filter('query_vars', 'km_vars');
    add_rewrite_rule('^local-products/([^/]*)/([^/]*)/([^/]*)/page/([0-9]+)/?$','index.php?pagename=local-products&country=$matches[1]&region=$matches[2]&city=$matches[3]&paged=$matches[4]','top');
    add_rewrite_rule('^local-dispensaries/([^/]*)/([^/]*)/([^/]*)/page/([0-9]+)/?$','index.php?pagename=local-dispensaries&country=$matches[1]&region=$matches[2]&city=$matches[3]&paged=$matches[4]','top');
    add_rewrite_rule('^local-delivery/([^/]*)/([^/]*)/([^/]*)/page/([0-9]+)/?$','index.php?pagename=local-delivery&country=$matches[1]&region=$matches[2]&city=$matches[3]&paged=$matches[4]','top');
    
    // add_rewrite_rule('^local-products/([^/]*)/([^/]*)/([^/]*)/page/([0-9]+)/?$','index.php?page_id=12945&country=$matches[1]&region=$matches[2]&city=$matches[3]&paged=$matches[4]','top');
    // add_rewrite_rule('^local-dispensaries/([^/]*)/([^/]*)/([^/]*)/page/([0-9]+)/?$','index.php?page_id=12963&country=$matches[1]&region=$matches[2]&city=$matches[3]&paged=$matches[4]','top');
    // add_rewrite_rule('^local-delivery/([^/]*)/([^/]*)/([^/]*)/page/([0-9]+)/?$','index.php?page_id=12957&country=$matches[1]&region=$matches[2]&city=$matches[3]&paged=$matches[4]','top');


    add_rewrite_rule('^local-products/([^/]*)/([^/]*)/([^/]*)/?$','index.php?pagename=local-products&country=$matches[1]&region=$matches[2]&city=$matches[3]','top');
    add_rewrite_rule('^local-dispensaries/([^/]*)/([^/]*)/([^/]*)/?$','index.php?pagename=local-dispensaries&country=$matches[1]&region=$matches[2]&city=$matches[3]','top');
    add_rewrite_rule('^local-delivery/([^/]*)/([^/]*)/([^/]*)/?$','index.php?pagename=local-delivery&country=$matches[1]&region=$matches[2]&city=$matches[3]','top');
    
    // add_rewrite_rule('^local-products/([^/]*)/([^/]*)/([^/]*)/?$','index.php?page_id=12945&country=$matches[1]&region=$matches[2]&city=$matches[3]','top');
    // add_rewrite_rule('^local-dispensaries/([^/]*)/([^/]*)/([^/]*)/?$','index.php?page_id=12963&country=$matches[1]&region=$matches[2]&city=$matches[3]','top');
    // add_rewrite_rule('^local-delivery/([^/]*)/([^/]*)/([^/]*)/?$','index.php?page_id=12957&country=$matches[1]&region=$matches[2]&city=$matches[3]','top');
    



}
add_action('init', 'km_rewrite_rules');



// function rewriteurl() {
//     global $wp, $wp_rewrite;
//     $wp->add_query_var('city');
//     $wp->add_query_var('region');
//     $wp->add_query_var('country');
//     $wp_rewrite->add_rule('^loctest/([^/]*)/([^/]*)/([^/]*)/?','index.php?pagename=test&country=$matches[1]&region=$matches[2]&city=$matches[3]','top');
  
//     // Reset permalinks, delete after programming
//     $wp_rewrite->flush_rules(false);  
//   }
//   add_action( 'init', 'rewriteurl' );