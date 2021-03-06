<?php

/**
 * Plugin Name: Kushmapper API 
 * Description: Shortcodes and functions for Kushmapper API.
 * Author: SlyFox
 * Version: 0.1.1
 */
 

 $km_meta = [];

/** 
 * Hooks
 */

function km_prerender() {

    global $km_meta;

    // Allow elementor editing
    if (isset($_GET['elementor-preview'])) {    
        return;
    }
    
    // Decide whether to check the page slug and title
    if ( !is_api_page() ) {
        return;
    }
    
    $slug = get_query_var('slug');
    
    if(is_page('product') && $slug) {
        
        $request = wp_remote_get( 'https://api.kushmapper.com/v1/meta/products/' . $slug );
        $body = wp_remote_retrieve_body( $request );
        $data = json_decode($body);
        
        // set global
        $km_meta['title_term'] = $data->name;
        $km_meta['schema'] = $data->schema;
    }
    
    if(is_page('vendor') && $slug) {

        $request = wp_remote_get( 'https://api.kushmapper.com/v1/meta/vendors/' . $slug );
        $body = wp_remote_retrieve_body( $request );
        $data = json_decode($body);
        
        // set global
        $km_meta['title_term'] = $data->name;
        $km_meta['schema'] = $data->schema;
    }
    
    $city = get_query_var('city');
    $region = get_query_var('region');

    if(is_page(['location', 'location-products', 'location-dispensaries', 'location-delivery']) && $region && $city) {
        $request = wp_remote_get( 'https://api.kushmapper.com/v1/meta/locations/' . $region . '/' . $city );
        $body = wp_remote_retrieve_body( $request );
        $data = json_decode($body);
        
        // set global
        $km_meta['title_term'] = $data->city;
        $km_meta['city_name'] = $data->city;
        $km_meta['region'] = $data->state;
        $km_meta['schema'] = $data->schema;
    }

    // Set 404
    if( is_wp_error( $request ) || $request['response']['code'] != 200 ) {
        global $wp_query;
        
        $wp_query->set_404();
        status_header( 404 );
        get_template_part( 404 );

        exit();
    }

}
add_action( 'template_redirect', 'km_prerender', 15, 0 );


function set_api_title( $title ) {
    global $km_meta;

    $title = $km_meta['title_term'];

    if (empty($km_meta['title_term'])) {
        return $km_meta['title_term'];
    }

    if(is_page('location')){
      $title = "Dispensaries & Weed Delivery | " . $km_meta['title'] . ', ' . $km_meta['region'];
    }

    return $title . " | Kushmapper";
 }
 add_filter( 'pre_get_document_title', 'set_api_title', 99999999, 1 );


function set_api_meta()
{
    global $km_meta;

    if (!is_api_page()) {
        return;
    }

    $title_term = $km_meta['title_term'] ?? "";
    $description = $km_meta['description'] ?? "";
    $region = $km_meta['region'] ?? "";

    if (!empty($description)) {
        return htmlspecialchars($description); 
     }

    if (is_page('vendor')) {

        if (!empty($title_term)) {
            $content = "Learn about $title_term.  Discover $title_term products, store locations, delivery areas, special offers and more!";
            return $content; 
        }
    }
    
    if (is_page('product')) {
      
        if (!empty($title_term)) {
            $content = "Product information about $title_term.  Find where to buy near you!";
            return $content; 
        }
    }
    
    if (is_page(['location', 'location-products', 'location-dispensaries', 'location-delivery'])) {
      
        if (!empty($title_term)) {
            $content = "Find local dispensaries and weed delivery in $title_term, $region. Get your weed delivered to your door with one of the weed delivery or cannabis mail order services in $title_term, $region.";
            return $content; 
        }
    }

}
add_action( 'rank_math/frontend/description', 'set_api_meta', 20);

function km_set_schema() {
    global $km_meta;

    if (!is_api_page()) {
        return;
    }

    $schemaArray = $km_meta['schema'];

    $schemaString = "";

    foreach ($schemaArray as $item) {
        $schemaString .= '<script type="application/ld+json">' . json_encode($item) . '</script>';
    }

    echo $schemaString;
}
add_action('wp_head', 'km_set_schema');


add_filter( 'rank_math/frontend/canonical', function( $canonical ) {
    if ( is_api_page() ) {
        $canonical = false;
    }
    return $canonical;
});


/**
 * Multipurpose
 */

function is_api_page() {
    return is_page(['product', 'vendor', 'location', 'location-products', 'location-dispensaries', 'location-delivery']);
}

function km_get_api_data($startUrl) {

    $request = wp_remote_get( $startUrl );

    if( is_wp_error( $request ) ) {
        return false;
    }
    
    $body = wp_remote_retrieve_body( $request );

    $data = json_decode($body);
    
    return $data;
}

function km_get_location() {

    $location = [];

    if ( !empty(get_query_var('country')) && !empty(get_query_var('region')) && !empty(get_query_var('city')) ) {
        $location['country'] = get_query_var('country');
        $location['region'] = get_query_var('region');
        $location['city'] = get_query_var('city');
    }

    return $location;
}

function km_get_api_page() {
    $page_number = false;
    if(isset($_GET['page_number'])) {
        $page_number = is_numeric( $_GET['page_number'] ) ? $_GET['page_number'] : false;
    }
    return $page_number;
}

function km_get_api_slug() {
    $slug = get_query_var('slug');
    if(!empty($slug)) {
        return $slug;
    }
    return $slug;
}

add_shortcode( 'location', 'km_location_string' );
function km_location_string($atts) {

    global $km_meta;

    $a = shortcode_atts( array(
		'type' => ''
	), $atts );

    $location = km_get_location();

    if ( empty($location) ) {
        return "this location";
    }

    $city = !empty($km_meta['city_name']) ? $km_meta['city_name'] : ucwords($location['city']);

    if ($a['type'] == 'city') {
       return $city;
    }
    if ($a['type'] == 'region') {
        return strtoupper($location['region']);
    }
    if ($a['type'] == 'country') {
        return ucwords($location['country']);
    }
    if ($a['type'] == 'city-region') {
        return $city . ", " . strtoupper($location['region']);
    }

    return $city;
}

function generateRandomString($length = 20) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


/**
 * Link Builders
 */

function loc_url_str($location) {
    return strtolower($location['country']) . '/' . strtolower($location['region']) . '/' . strtolower($location['city']);
}


add_shortcode( 'location-url', 'km_url_builder' );
function km_url_builder($atts) {

    $a = shortcode_atts( array(
		'p' => 'home',
	), $atts );

    $location = km_get_location();

    if ( $a['p'] == 'location' && !empty($location) )
    {
        return home_url('/location/' . loc_url_str($location) . '/');
    }
    
    if ( $a['p'] == 'location-delivery' && !empty($location) )
    {
        return home_url('/location/' . loc_url_str($location) . '/delivery/' );
    }
    
    if ( $a['p'] == 'location-dispensaries' && !empty($location) )
    {
        return home_url('/location/' . loc_url_str($location) . '/dispensaries/' );
    }

    if ( $a['p'] == 'location-products' && !empty($location) )
    {
        return home_url('/location/' . loc_url_str($location) . '/products/' );
    }

    $url = home_url('/');
    return $url;
}

add_shortcode( 'location-link', 'km_link_builder' );
function km_link_builder($atts) {
    $url = km_url_builder($atts);
    $a = shortcode_atts( array(
		'label' => 'Link',
	), $atts );

    $link = "<a href=\"$url\">{$a['label']}</a>";

    return $link;
}


/**
 * Vendors
 */

add_shortcode('vendor-single', 'vendor_single');
function vendor_single()
{
    $slug = km_get_api_slug();
    if (empty($slug)) {
        return "Not found.";
    }

    $api_url = "https://api.kushmapper.com/v1/vendors/slug/{$slug}?include=products";

    $data = km_get_api_data($api_url);
    if ( $data && !empty($data->data) ) {
        $vendor = $data->data;
    } else {
        return "Not found.";
    }

    ob_start(); ?>

    <script>
        function kmData() {

            return {
                vendorId: <?php echo $vendor->id; ?>,
                vendor: <?php echo json_encode($vendor); ?>,
                getData() {
                    var self = this;
                    axios.get('https://api.kushmapper.com/v1/vendors/' + this.vendorId + "?include=products")
                        .then(function(response) {
                            console.log(response);
                            self.vendor = response.data.data;
                        })
                        .catch(function(error) {
                            console.log(error);
                        })
                        .then(function() {

                        })
                }
            };
        }
    </script>
    <div x-data="kmData()">
        <template x-if="vendor">
            <div>
                <h1 x-text="vendor.name"></h1>
                <pre x-text="JSON.stringify(vendor, null, '\t')"></pre>
            </div>
        </template>
    </div>
<?php
    $html = ob_get_clean();

    return $html;
}

add_shortcode('vendor-index', 'km_vendor_index');
function km_vendor_index($atts)
{

    $a = shortcode_atts( array(
        'id' => generateRandomString(),
		'use_location' => 0,
		'page_size' => 12,
        'has_store' => null,
        'has_delivery' => null,
        'has_mail_order' => null,
        'supplies_location' => null,
        'is_slider' => 0,
        'is_featured' => 0,
        'pagination' => 1,
        'sort' => null,
        'loop' => 0,
        'false_if_null' => 0,
	), $atts );

    $options = [
        'id' => $a['id'],
        'pagination' => $a['pagination'],
        'false_if_null' => $a['false_if_null'],
        'loop' => $a['loop'],
    ];

    $page_number = km_get_api_page();

    $page_query = "";
    if ($page_number) {
        $page_query = "&page=$page_number";
    }

    $page_size = $a['page_size'];
    
    $startUrl = "https://api.kushmapper.com/v1/vendors?page_size={$page_size}{$page_query}";
    
    $location = km_get_location();

    if ($a['use_location'] && count($location) == 3) {

        $country = $location['country'];
        $region = $location['region'];
        $city = $location['city'];

        if ($a['has_store']) {
            $startUrl .= "&filter[stores.country]=$country&filter[stores.state]=$region&filter[stores.city]=$city";
        }

        if ($a['has_delivery']) {
            $startUrl .= "&filter[service_areas.country]=$country&filter[service_areas.state]=$region&filter[service_areas.city]=$city";
        }

        if ($a['supplies_location']) {
            $startUrl .= "&filter[supplies_location]=$city|$region";
        }

    }

    if ( !$a['use_location'] ) {

        if ($a['has_store']) {
            $startUrl .= "&filter[has_store]=1";
        }
        if ($a['has_delivery']) {
            $startUrl .= "&filter[has_delivery]=1";
        }
    }

    if ( $a['is_featured'] ) {
        $startUrl .= "&filter[is_featured]=1";
    }

    if ( $a['has_mail_order'] ) {
        $startUrl .= "&filter[has_mail_order]=1";
    }

    if ( in_array($a['sort'], ['random', 'name']) ) {
        $startUrl .= "&sort=" . $a['sort'];
    }

    if ( $a['is_slider'] ) {
        $html = km_renderVendorSliderTemplate($startUrl, $options);

        return $html;
    } 

    $html = km_renderVendorIndexTemplate($startUrl, $options);

    return $html;

}

function km_renderVendorIndexTemplate($startUrl, $options)
{
    $data = km_get_api_data($startUrl);

    if ($options['false_if_null'] === 1 && empty($data->data) ) {
        return false;
    }

    $componentFunction = "kmVendorData_" . $options['id'] . "()";

    ob_start(); ?>

    <script>
        
        function <?php echo $componentFunction;?> {
            return {
                startUrl: '<?php echo $startUrl; ?>',
                pagination: <?php echo $options['pagination'];?>,
                data: <?php echo json_encode( $data->data ); ?>,
                meta: <?php echo json_encode( $data->meta ); ?>,
                getData(url = this.startUrl) {
                    var self = this;

                    if (!url) {
                        return;
                    }

                    self.$dispatch('toggleloading');

                    axios.get(url)
                        .then(function(response) {
                            console.log(response);
                            self.data = response.data.data;
                            self.meta = response.data.meta;
                            self.$dispatch('toggleloading');
                        })
                        .catch(function(error) {
                            console.log(error);
                            self.$dispatch('toggleloading');
                        })
                        .then(function() {

                        })
                },
                getVendorUrl(slug){
                    return '/vendor/'+slug+'/';
                },
                getPaginationHref(api_url) {

                    if (!api_url) {
                        return;
                    }

                    var url = new URL(api_url);

                    var queryString = url.search;
                    var urlParams = new URLSearchParams(queryString);
                    var pageNumber = urlParams.get('page');

                    if (this.isNumber(pageNumber) ) {
                        return window.location + '?page_number=' + pageNumber;
                    }

                    return "";
                },
                isNumber(n) {return !isNaN(parseFloat(n)) && !isNaN(n - 0) },
            };
        }
    </script>
    <div x-data="<?php echo $componentFunction;?>">
        <template x-if="data">
            <div class="columns is-multiline">
                <template x-for="vendor in data">
                    <div class="column is-one-third-tablet is-one-quarter-desktop">
                        <div class="vendor-item index-item">
                            <a :href="getVendorUrl(vendor.slug)"><img :src="vendor.logo_url" alt="Vendor Logo" loading="lazy" /></a>
                            <a class="item-name" x-text="vendor.name" :href="getVendorUrl(vendor.slug)"></a>
                            <a class="item-button button is-dark" :href="getVendorUrl(vendor.slug)">View Dispensary</a>
                        </div>  
                    </div>
                </template>
            </div>
        </template>
        <template x-if="pagination === 1 && meta && meta.total > meta.per_page">
            <div>
                <template x-for="link in meta.links">
                    <a :class="{'button':true, 'is-active':link.active}" :href="getPaginationHref(link.url)" @click.prevent="getData(link.url)" x-html="link.label"></a>
                </template>
            </div>
        </template>
    </div>
    <?php
    $html = ob_get_clean();

    return $html;
}

function km_renderVendorSliderTemplate($startUrl, $options)
{    
    $data = km_get_api_data($startUrl);

    $should_loop = $options['loop'] === 1 ? 'true' : 'false';

    $html ='<div class="swiper-container km-slide-container km-vendor-slider-'. $options['id'] . '"><div class="swiper-wrapper">';

    foreach ( $data->data as $vendor ) {
        $html .= '<div class="swiper-slide" style="width: 300px">
                        <div class="vendor-item index-item vendor-slider item-slider">
                            <a href="/vendor/' . $vendor->slug . '/"><img src="' . $vendor->logo_url . '" alt="Vendor Logo" loading="lazy" /></a>
                            <a class="item-name" href="/vendor/' . $vendor->slug . '/">' . $vendor->name . '</a>
                            <a class="item-button button is-dark" href="/vendor/' . $vendor->slug . '/">View Dispensary</a>
                        </div>  
                    </div>';
    }

    $html .= '</div>
            <div class="swiper-pagination"></div>
        </div>';

    $html .= '<script>
    jQuery(document).ready(function ($) {
        const swiper_' . $options['id'] . '= new Swiper(".km-vendor-slider-' . $options['id'] . '", {
            autoplay: {
                delay: 4000,
            },
            slidesPerView: 1,
            slidesPerGroup: 1,
            spaceBetween: 20,
            loop: ' . $should_loop . ',
            loopFillGroupWithBlank: true,
            speed: 600,
            breakpoints: {
                450: {
                    slidesPerView: 2,
                    slidesPerGroup: 2,
                    spaceBetween: 20
                },
                769: {
                    slidesPerView: 3,
                    slidesPerGroup: 3,
                    spaceBetween: 20
                },
                1024: {
                    slidesPerView: 4,
                    slidesPerGroup: 4,
                    spaceBetween: 24
                }
            },
            // navigation: {
            //     nextEl: ".swiper-button-next",
            //     prevEl: ".swiper-button-prev",
            // },
            pagination: {
                el: ".swiper-pagination",
                type: "bullets",
                clickable: true,
            },
            // scrollbar: {
            //     el: ".swiper-scrollbar",
            //     draggable: true,
            // },
        });
    });

    </script>';

    return $html;
}


/**
 * Products
 */

/***
add_shortcode('product-single', 'product_single');
function product_single()
{
    $slug = km_get_api_slug();
    if (empty($slug)) {
        return "Not found.";
    }

    $api_url = "https://api.kushmapper.com/v1/products/slug/{$slug}?include=vendor";

    $data = km_get_api_data($api_url);
    if ( $data && !empty($data->data) ) {
        $product = $data->data;
    } else {
        return "Not found.";
    }

    ob_start(); ?>

    <script>
        function kmProductData() {

            return {
                productId: <?php echo $product->id; ?>,
                product: <?php echo json_encode($product); ?>,
                getData() {
                    var self = this;
                    axios.get('https://api.kushmapper.com/v1/products/' + this.productId + "?include=vendor")
                        .then(function(response) {
                            console.log(response);
                            self.product = response.data.data;
                        })
                        .catch(function(error) {
                            console.log(error);
                        })
                        .then(function() {

                        })
                }
            };
        }
    </script>
    <div x-data="kmProductData()">
        <template x-if="product">
            <div>
                <h1 x-text="product.name"></h1>
                <pre x-text="JSON.stringify(product, null, '\t')"></pre>
            </div>
        </template>
    </div>
<?php
    $html = ob_get_clean();

    return $html;
}
***/

add_shortcode('product-index', 'km_product_index');
function km_product_index($atts)
{

    $a = shortcode_atts( array(
        'id' => generateRandomString(), 
		'use_location' => 0,
        'use_filter' => 0,
		'page_size' => 12,
        'has_store' => null,
        'has_delivery' => null,
        'available_here' => null,
        'is_slider' => 0,
        'is_featured' => 0,
        'pagination' => 1,
        'sort' => null,
        'loop' => 0,
        'appends' => 'lowest_price',
	), $atts );

    $options = [
        'id' => $a['id'],
        'pagination' => $a['pagination'],
        'use_filter' => $a['use_filter'],
        'loop' => $a['loop'],
    ];

    $page_number = km_get_api_page();

    $page_query = "";

    if ($page_number) {
        $page_query = "&page=$page_number";
    }

    $page_size = $a['page_size'];
    
    $startUrl = "https://api.kushmapper.com/v1/products?page_size={$page_size}{$page_query}&append={$a['appends']}";
    
    $location = km_get_location();

    if ($a['use_location'] && count($location) == 3) {

        $country = $location['country'];
        $region = $location['region'];
        $city = $location['city'];

        if ($a['has_store']) {
            $startUrl .= "&filter[vendor.stores.country]=$country&filter[vendor.stores.state]=$region&filter[vendor.stores.city]=$city";
        }

        if ($a['has_delivery']) {
            $startUrl .= "&filter[vendor.service_areas.country]=$country&filter[vendor.service_areas.state]=$region&filter[vendor.service_areas.city]=$city";
        }

        if ($a['available_here']) {
            $startUrl .= "&filter[available_in]=$city|$region";
        }

    }

    if ( !$a['use_location'] ) {

        if ($a['has_store']) {
            $startUrl .= "&filter[vendor.has_store]=1";
        }
        if ($a['has_delivery']) {
            $startUrl .= "&filter[vendor.has_delivery]=1";
        }
        
    }

    if ( $a['is_featured'] ) {
        $startUrl .= "&filter[is_featured]=1";
    }

    if ( in_array($a['sort'], ['random', 'name']) ) {
        $startUrl .= "&sort=" . $a['sort'];
    }

    if ( $a['is_slider'] ) {
        $html = km_renderProductSliderTemplate($startUrl, $options);

        return $html;
    } 

    $html = km_renderProductIndexTemplate($startUrl, $options);

    return $html;

}

function km_renderProductIndexTemplate($startUrl, $options)
{
    $data = km_get_api_data($startUrl);

    $componentFunction = "kmProductData_" . $options['id'] . "()";

    ob_start(); ?>

    <script>
        
        function <?php echo $componentFunction;?> {
            return {
                filter: {
                    page_size: 12,
                    category: 'All',
                    maxPrice: '',
                    weight: 'All',
                    minThc: 'All',
                    minCbd: 'All',
                },
                categories: ['Flower - Sativa', 'Flower - Indica', 'Flower - Hybrid', 'Concentrates', 'Edibles', 'Gear', 'Other' ],
                startUrl: '<?php echo $startUrl; ?>',
                pagination: <?php echo $options['pagination']; ?>,
                data: <?php echo json_encode( $data->data ); ?>,
                meta: <?php echo json_encode( $data->meta ); ?>,
                getData(url = this.startUrl) {
                    var self = this;

                    if (!url) {
                        return;
                    }

                    self.$dispatch('toggleloading');

                    axios.get(url)
                        .then(function(response) {
                            console.log(response);
                            self.data = response.data.data;
                            self.meta = response.data.meta;
                            self.$dispatch('toggleloading');
                        })
                        .catch(function(error) {
                            self.$dispatch('toggleloading');
                            console.log(error);
                        })
                        .then(function() {

                        })
                },
                getProductUrl(slug){
                    return '/product/'+slug+'/';
                },
                getPaginationHref(api_url) {

                    if (!api_url) {
                        return;
                    }

                    var url = new URL(api_url);

                    var queryString = url.search;
                    var urlParams = new URLSearchParams(queryString);
                    var pageNumber = urlParams.get('page');

                    if (this.isNumber(pageNumber) ) {
                        return window.location + '?page_number=' + pageNumber;
                    }

                    return "";
                },
                isNumber(n) {return !isNaN(parseFloat(n)) && !isNaN(n - 0) },
                getLowestPrice(product) {
                    var prices = [];

                    
                    prices.push(product.price, product.price_oz, product.price_oz_half, product.price_oz_fourth, product.price_oz_eighth, product.price_gram);
                    var lowest = prices.reduce(function callbackFn(accumulator, currentValue, currentIndex){
                        if ( accumulator == null ) {
                            return currentValue;
                        }

                        if ( currentValue != null && currentValue < accumulator ) {
                            return currentValue;
                        }

                        return accumulator;
                    });
                    if (lowest != null) {
                        return lowest.toFixed(2);
                    }
                    return "";

                },
                //Filter
                setCategories(){
                    this.categories = [];
                },
                getFilterString() 
                {
                    weightSelect = "";
                    sortPrice = "";
                    if ( this.filter.weight == "1g"){
                        weightSelect = "&filter[minimum_price_gram]=0&filter[maximum_price_gram]=";
                        sortPrice = "&sort=-price_gram&filter[minimum_price_gram]=0";
                    }

                    if ( this.filter.weight == "1/8oz"){
                        weightSelect = "&filter[minimum_price_oz_eighth]=0&filter[maximum_price_oz_eighth]=";
                        sortPrice = "&sort=-price_oz_eighth&filter[minimum_price_oz_eighth]=0";
                    }

                    if ( this.filter.weight == "1/4oz"){
                        weightSelect = "&filter[minimum_price_oz_fourth=0]&filter[maximum_price_oz_fourth]=";
                        sortPrice = "&sort=-price_oz_fourth&filter[minimum_price_oz_fourth]=0";
                    }

                    if ( this.filter.weight == "1/2oz"){
                        weightSelect = "&filter[minimum_price_oz_half=0]&filter[maximum_price_oz_half]=";
                        sortPrice = "&sort=-price_oz_half&filter[minimum_price_oz_half]=0";
                    }

                    if ( this.filter.weight == "1oz"){
                        weightSelect = "&filter[minimum_price_oz=0]&filter[maximum_price_oz]=";
                        sortPrice = "&sort=-price_oz&filter[minimum_price_oz]=0";
                    }

                    if ( this.filter.minThc != "All"){
                        thc = this.filter.minThc.split("-");
                        thcString = "&filter[minimum_thc]=" + thc[0] + "&filter[maximum_thc]=" + thc[1];
                    }

                    if ( this.filter.minCbd != "All"){
                        cbd = this.filter.minCbd.split("-");
                        cbdString = "&filter[minimum_cbd]=" + cbd[0] + "&filter[maximum_cbd]=" + cbd[1];
                    }

                    // this.filter.page_size = this.pageSize;
                    // let baseString = "https://api.kushmapper.com/v1/products";
                    // let pageSizeString = "?page_size=" + this.meta.per_page;
                    let maxPriceString = this.filter.weight == 'All' && this.filter.maxPrice != '' ? "&filter[maximum_price_any]=" + this.filter.maxPrice : '';
                    let weightStringAll = this.filter.weight != 'All' && this.filter.maxPrice != '' ? sortPrice + weightSelect + this.filter.maxPrice : '';
                    let weightStringSingle = this.filter.weight != 'All' && this.filter.maxPrice == '' ? sortPrice : '';
                    let categoryString = this.filter.category != 'All' ? "&filter[category]=" + this.filter.category : '';
                    let minThcString = this.filter.minThc != 'All' ? thcString : '';
                    let minCbdString = this.filter.minCbd != 'All' ? cbdString : '';

                    return this.startUrl + maxPriceString + weightStringAll + weightStringSingle + categoryString + minThcString + minCbdString;
                },
                resetFilter()
                { 
                    this.filter.category = 'All';
                    this.filter.maxPrice = '';
                    this.filter.weight = 'All';
                    this.filter.minThc = 'All';
                    this.filter.minCbd = 'All';
                    this.getData(this.getFilterString());
                },
                updateInputType()
                {
                    jQuery(".km-max-thc-input-location-product").attr('type', 'number'); 
                    jQuery(".km-max-thc-input-location-product").attr('min', '0');                    
                },
            };
        }
    </script>
    <div x-data="<?php echo $componentFunction;?>">
        <?php if ($options['use_filter'] == 1) {echo km_render_product_filter();}?>
        <template x-if="data">
            <div class="columns is-multiline">
                <template x-for="product in data">
                    <div class="column is-one-third-tablet is-one-quarter-desktop">
                        <div class="product-item index-item">
                            <a :href="getProductUrl(product.slug)"><img :src="product.image_url" alt="Product Image" loading="lazy" /></a>
                            <a class="item-name" x-text="product.name" :href="getProductUrl(product.slug)"></a>
                            <p class="item-price" x-text="product.lowest_price ? 'from $' + product.lowest_price : ''"></p>
                            <a class="item-button button is-dark" :href="getProductUrl(product.slug)">View Product</a>
                        </div>  
                    </div>
                </template>
            </div>
        </template>
        <template x-if="pagination == 1 && meta && meta.total > meta.per_page">
            <div>
                <template x-for="link in meta.links">
                    <a :class="{'button':true, 'is-active':link.active}" :href="getPaginationHref(link.url)" @click.prevent="getData(link.url)" x-html="link.label"></a>
                </template>
            </div>
        </template>
    </div>
    <?php
    $html = ob_get_clean();

    return $html;
}

function km_renderProductSliderTemplate($startUrl, $options)
{    

    $should_loop = $options['loop'] === 1 ? 'true' : 'false';

    $data = km_get_api_data($startUrl);

    $html ='<div class="swiper-container km-slide-container km-product-slider-' . $options['id'] . '"><div class="swiper-wrapper">';

    foreach ( $data->data as $product ) {

        if (!empty($product->lowest_price)) {
            $lowest_price_html = "<p class=\"item-price\">from $" . number_format($product->lowest_price,2) . "</p>";
        } else {
            $lowest_price_html = '<p class=\"item-price\"></p>';
        }
        if (!empty($product->thc)) {
            $thc_html = $product->thc . '%';
        } else {
            $thc_html = 'Not Available';
        }

        $html .= '<div class="swiper-slide" style="width: 300px">
                        <div class="product-item index-item product-slider item-slider">
                            <a href="/product/' . $product->slug . '/"><img src="' . $product->image_url . '" alt="Product Image" loading="lazy" /></a>
                            <a class="item-name" href="/product/' . $product->slug . '/">' . $product->name . '</a>' .
                            $lowest_price_html .
                            '<span class="item-thc">THC: ' . $thc_html . '</span> 
                            <a class="item-button button is-dark" href="/product/' . $product->slug . '/">View Product</a>
                        </div>  
                    </div>';
    }

    $html .= '</div>
            <div class="swiper-pagination"></div>
        </div>';

    $html .= '<script>
    jQuery(document).ready(function ($) {
        const swiper_' . $options['id'] . ' = new Swiper(".km-product-slider-'. $options['id'] .'", {
            autoplay: {
                delay: 4000,
            },
            slidesPerView: 1,
            slidesPerGroup: 1,
            spaceBetween: 20,
            loop: ' . $should_loop . ',
            loopFillGroupWithBlank: true,
            speed: 600,
            breakpoints: {
                450: {
                    slidesPerView: 2,
                    slidesPerGroup: 2,
                    spaceBetween: 20
                },
                769: {
                    slidesPerView: 3,
                    slidesPerGroup: 3,
                    spaceBetween: 20
                },
                1024: {
                    slidesPerView: 4,
                    slidesPerGroup: 4,
                    spaceBetween: 24
                }
            },
            // navigation: {
            //     nextEl: ".swiper-button-next",
            //     prevEl: ".swiper-button-prev",
            // },
            pagination: {
                el: ".swiper-pagination",
                type: "bullets",
                clickable: true,
            },
            // scrollbar: {
            //     el: ".swiper-scrollbar",
            //     draggable: true,
            // },
        });
    });

    </script>';

    return $html;
}

function km_render_product_filter() {
    ob_start(); ?>
    <div  class="filter-controls mb-4">
    <div class="columns km-filters-location-product">
        <div class="column km-filters-column-location-product">              
            <fieldset class="km-max-thc-location-product">
                <legend>Max Price</legend>
                <div>
                    <div class="select">
                        <select x-model="filter.weight" x-on:change="getData(getFilterString())">
                            <option value="All">All</option>        
                            <option value="1g">1g</option>     
                            <option value="1/8oz">1/8oz</option>  
                            <option value="1/4oz">1/4oz</option>   
                            <option value="1/2oz">1/2oz</option>              
                            <option value="1oz">1oz</option>                                   
                        </select>
                    </div>
                    <div class="km-max-thc-currency-location-product">
                        <input class="km-max-thc-input-location-product" 
                            type="text" 
                            id="thcMax" 
                            placeholder="price"  
                            x-model="filter.maxPrice" 
                            x-on:change="getData(getFilterString())"
                            x-on:click="updateInputType()" />
                    </div>
                </div>
            </fieldset>
        </div>
        
        <div class="column  km-filters-column-location-product">     
            <div class="km-filters-label-thc-location-product"> 
                <div class="select">        
                    <select id="thc" x-model="filter.minThc" x-on:change="getData(getFilterString())">
                        <option value="All" name="all">All</option>
                        <option value="10-14" name="20">10-14%</option>     
                        <option value="14-18" name="15">14-18%</option>  
                        <option value="18-22" name="10">18-22%</option>   
                        <option value="22-26" name="5">22-26%</option>    
                        <option value="26-30" name="5">26-30%</option>   
                        <option value="30-34" name="5">30-34%</option>  
                        <option value="34-38" name="5">34-38%</option>                                      
                    </select>
                </div>
            </div>
        </div>

        <div class="column km-filters-column-location-product">      
            <div class="km-filters-label-cbd-location-product"> 
                <div class="select">          
                    <select id="cbd" x-model="filter.minCbd" x-on:change="getData(getFilterString())">
                        <option value="All" name="all">All</option>
                        <option value="0-4" name="20">0-4%</option>  
                        <option value="4-8" name="15">4-8%</option>   
                        <option value="8-12" name="10">8-12%</option>   
                        <option value="12-16" name="5">12-16%</option>      
                        <option value="16-20" name="5">16-20%</option>   
                        <option value="20-24" name="5">20-24%</option>                                        
                    </select>
                </div>
            </div>
        </div>

        <div class="column  km-filters-column-location-product">      
            <div class="km-filters-label-category-location-product">         
                <div class="select"> 
                    <select name="Category" id= "cat" x-model="filter.category" x-on:change="getData(getFilterString())">
                        <option value="All" name="all">All</option>
                        <template x-for="category in categories" :key="category">
                            <option :value="category" x-text="category"></option>
                        </template>                            
                    </select> 
                </div>        
            </div>
        </div>

        <div class="column km-filters-column-location-product">
            <button class="button is-black" x-on:click="resetFilter()">Reset</button>
        </div>

    </div>
</div>
<?php
    $html = ob_get_clean();
    return $html;
}



/**
 * Rewrite
 */
function km_tags()
{
    add_rewrite_tag('%city%', '([^&]+)');
    add_rewrite_tag('%region%', '([^&]+)');
    add_rewrite_tag('%country%', '([^&]+)');
    add_rewrite_tag('%slug%', '([^&]+)');
}
add_action('init', 'km_tags');

function km_rewrite_rules() {

    //add_rewrite_rule('^location/([^/]*)/([^/]*)/([^/]*)/page/([^/]*)/?$', 'index.php?pagename=location&country=$matches[1]&region=$matches[2]&city=$matches[3]&api_page=$matches[4]', 'top');

    add_rewrite_rule('^location/([^/]*)/([^/]*)/([^/]*)/products/?$', 'index.php?pagename=location-products&country=$matches[1]&region=$matches[2]&city=$matches[3]', 'top');
    add_rewrite_rule('^location/([^/]*)/([^/]*)/([^/]*)/delivery/?$', 'index.php?pagename=location-delivery&country=$matches[1]&region=$matches[2]&city=$matches[3]', 'top');
    add_rewrite_rule('^location/([^/]*)/([^/]*)/([^/]*)/dispensaries/?$', 'index.php?pagename=location-dispensaries&country=$matches[1]&region=$matches[2]&city=$matches[3]', 'top');
    add_rewrite_rule('^location/([^/]*)/([^/]*)/([^/]*)/?$', 'index.php?pagename=location&country=$matches[1]&region=$matches[2]&city=$matches[3]', 'top');
    add_rewrite_rule('^vendor/([^/]*)/?$', 'index.php?pagename=vendor&slug=$matches[1]', 'top');
    add_rewrite_rule('^product/([^/]*)/?$', 'index.php?pagename=product&slug=$matches[1]', 'top');
}
add_action('init', 'km_rewrite_rules');
