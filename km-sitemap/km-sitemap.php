<?php
/**
 * Plugin Name: Kushmapper sitemap creator
 * Description: Create sitmap xml
 * Author: SlyFox
 * Version: 0.1.1
 */

add_shortcode('create-sitemap', 'create_sitemap');


function create_sitemap()
{
    //////////////////////////////
    //// create vendor xml //////
    //////////////////////////////
    $xmlVendor = new DomDocument('1.0');
    $xmlVendor->encoding = "utf-8";
    $xmlVendor->formatOutput = true;
    $urlset = $xmlVendor->createElementNS("http://www.sitemaps.org/schemas/sitemap/0.9","urlset");
    $urlset->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');
    $urlset->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
    $urlset->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.google.com/schemas/sitemap-image/1.1 http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd');
    $xmlVendor->appendChild($urlset);

    $startUrl = "http://api.kushmapper.com/v1/vendors?page_size=1000";   
    $vendorUrl = "https://v2.kushmapper.com/vendor/";
    $arguments = array(
        'method' => 'GET'
    );
	$response = wp_remote_get( $startUrl, $arguments );
	if ( is_wp_error( $response ) ) {
		$error_message = $response->get_error_message();
		return "Something went wrong: $error_message";
	} else 
    {
		echo '<pre>';
		// var_dump( wp_remote_retrieve_body( $response ) );
        $results = json_decode(wp_remote_retrieve_body( $response ));
        // var_dump($results);
		echo '</pre>';
        
        $results = $results->data;

        foreach ($results as $result) {
            $slug = $vendorUrl . $result->slug ."/";
            $url = $xmlVendor->createElement("url");
            $urlset->appendChild($url);
            $loc = $xmlVendor->createElement("loc", $slug);
            $url->appendChild($loc);
            $lastmod = $xmlVendor->createElement("lastmod", "2021-06-07");
            $url->appendChild($lastmod);    
            // echo "$result->slug <br>";
        }

	}

    echo "<xmp>".$xmlVendor->saveXML()."</xmp>";
    $xmlVendor->save("vendor_sitemap.xml") or die("Error, Unable to create xml file");


    //////////////////////////////
    //// create product xml //////
    //////////////////////////////
    $xmlProduct = new DomDocument('1.0');
    $xmlProduct->encoding = "utf-8";
    $xmlProduct->formatOutput = true;
    $urlset = $xmlProduct->createElementNS("http://www.sitemaps.org/schemas/sitemap/0.9","urlset");
    $urlset->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');
    $urlset->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
    $urlset->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.google.com/schemas/sitemap-image/1.1 http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd');
    $xmlProduct->appendChild($urlset);

    $startUrl = "http://api.kushmapper.com/v1/products?page_size=1000";   
    $vendorUrl = "https://v2.kushmapper.com/product/";
    $arguments = array(
        'method' => 'GET'
    );
	$response = wp_remote_get( $startUrl, $arguments );
	if ( is_wp_error( $response ) ) {
		$error_message = $response->get_error_message();
		return "Something went wrong: $error_message";
	} else 
    {
		echo '<pre>';
		// var_dump( wp_remote_retrieve_body( $response ) );
        $results = json_decode(wp_remote_retrieve_body( $response ));
        // var_dump($results);
		echo '</pre>';
        
        $results = $results->data;

        foreach ($results as $result) {
            $slug = $vendorUrl . $result->slug ."/";
            $url = $xmlProduct->createElement("url");
            $urlset->appendChild($url);
            $loc = $xmlProduct->createElement("loc", $slug);
            $url->appendChild($loc);
            $lastmod = $xmlProduct->createElement("lastmod", "2021-06-07");
            $url->appendChild($lastmod);    
        }

	}

    echo "<xmp>".$xmlProduct->saveXML()."</xmp>";
    $xmlProduct->save("product_sitemap.xml") or die("Error, Unable to create xml file");
}//end create_sitemap()

