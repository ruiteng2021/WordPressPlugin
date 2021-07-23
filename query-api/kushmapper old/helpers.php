<?php

// get location string from url
function km_get_uri_segment() {

    $urlParts = explode("/", parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

    if ( $urlParts[1] == 'location' ) {
        
        // array_pop doesnt work??
        //$urlLast = array_pop($urlParts);
        
        return $urlParts[4];
    }

    return "nowhere";
}

//get location from geodir object
function km_get_geodir_location($type = 'city') {
    global $geodirectory;

    if ($type == 'region') {
        return $geodirectory->location->region;
    }
    if ($type == 'country') {
        return $geodirectory->location->country;
    }

    return $geodirectory->location->city;
}


function km_gdir() {
    global $geodirectory;
    return vprint($geodirectory->location);
}
add_shortcode( 'gdir', 'km_gdir' );


function vprint($var) {
    return "<pre>" . print_r($var, true) . "</pre>";
}