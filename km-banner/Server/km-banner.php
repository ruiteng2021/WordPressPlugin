<?php
/**
 * Plugin Name: Kushmapper Banner
 * Description: Banner
 * Author: SlyFox
 * Version: 0.1.1
 */

add_shortcode('vendor-banner', 'vendor_banner');

function vendor_banner()
{
    $slug = get_query_var('slug');

    if(is_page('product') && $slug) {      
        $request = wp_remote_get( 'https://api.kushmapper.com/v1/products/slug/' . $slug );
        $body = wp_remote_retrieve_body( $request );
        $data = json_decode( $body );
        $startUrl = "https://api.kushmapper.com/v1/vendors/slug/{$data->data->vendor->slug}";
    }    

    if(is_page('vendor') && $slug) {
        $startUrl = "https://api.kushmapper.com/v1/vendors/slug/{$slug}";
    }

    // echo $startUrl;    

    ob_start();

    ?>
        <script>          
            function kmFindDataBanner() {
                return {
                    startUrl:   '<?php echo $startUrl; ?>',
                    vendor: false,
                    store: false,
                    async getData(url = this.startUrl) 
                    {
                        var self = this;
                        // console.log(url);
                        await axios.get(url)
                            .then(function(response) {
                                // console.log(response);
                                self.vendor = response.data.data;
                                if(self.vendor.stores.length == 1)
                                    self.store = true;
                                // banner is not defined currently
                                self.setBannerImage(self.vendor.banner);
                            })
                            .catch(function(error) {
                                console.log(error);
                            })                  
                    },

                    setBannerImage(imageUrl)
                    {
                        if(imageUrl)
                        { 
                            console.log(imageUrl);
                            jQuery(".km-banner-image").css("background-image", "url('" + imageUrl + "')");
                        }
                        else
                        {
                            // imageUrl = "/wordpress/banner.jpg";
                            imageUrl = "/banner.jpg";
                            jQuery(".km-banner-image").css("background-image", "url('" + imageUrl + "')");
                        }
                       
                    },

                    toggleHeart()
                    {
                        color = jQuery(".km-banner-favorates-heart").css("color");
                        // console.log(color);
                        if(color=="rgb(255, 0, 0)") {
                            jQuery(".km-banner-favorates-heart").attr("style", "color:white !important");
                            jQuery(".km-banner-favorates-heart").attr("title", "Add to Favorates");
                        }
                        else{
                            jQuery(".km-banner-favorates-heart").attr("style", "color:red !important");
                            jQuery(".km-banner-favorates-heart").attr("title", "Remove from Favorites");
                        }                       
                    },

                };
            }

        </script>

        <div class="km-banner-image" x-data="kmFindDataBanner()" x-init="getData()">
            <div class="km-banner-container">
                <div class="columns">
                    <div class="column km-banner-favorates">
                        <div class="buttons">
                            <a>
                                <button class="button km-banner-favorates-heart" x-on:click="toggleHeart()"  title="Add to Favorates">
                                    <span class="icon">
                                        <i class="fa fa-heart"></i>
                                    </span>
                                </button>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="columns">
                    <div class="column km-banner-text">
                        <h1 x-text="vendor.name"></h1>
                    </div>
                </div>
                <div class="columns">
                    <template x-if="store">
                        <div x-show="vendor.stores[0] != null" class="column km-banner-address">
                            <span class="icon-text">
                                <span class="icon">
                                <i class="fas fa-map-marker-alt"></i>
                                </span>                                
                                <span x-text="vendor.stores[0].address1"></span>,&nbsp;
                                <span x-text="vendor.stores[0].city"></span>
                            </span>
                        </div>
                    </template>
                </div>
                <div class="columns">
                    <div class="column km-banner-mail-order">
                        <span x-show="vendor.has_mail_order == true" class="icon-text">
                            <span class="icon">
                            <i class="fas fa-mail-bulk"></i>
                            </span>
                            <span>MAIL ORDER Available</span>
                        </span>
                    </div>
                </div>
                <div class="columns">
                    <div class="column km-banner-featured-verify">
                        <div x-show="vendor.is_featured == true" class="buttons">
                            <a href="#">
                                <button class="km-banner-featured button">
                                    <span class="icon">
                                        <i class="fas fa-certificate"></i>
                                    </span>
                                    <span>Features</span>
                                </button>
                            </a>
                            <a href="#">
                                <button class="km-banner-verified button">
                                    <span class="icon">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                    <span>Verified</span>
                                </button>
                            </a>        
                        </div>
                    </div>
                    <div class="column km-banner-icons">
                        <div class="buttons">
                            <a :href="vendor.website">
                                <button class="km-banner-icon km-banner-link-color button is-rounded">
                                    <span class="icon">
                                        <i class="fas fa-link"></i>
                                    </span>
                                </button>
                            </a>
                            <a :href="'mailto:'+ vendor.email">
                                <button class="km-banner-icon km-banner-mail-color button is-rounded">
                                    <span class="icon">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                </button>
                            </a>
                            <a :href="'Tel:' + vendor.phone">
                                <button class="km-banner-icon km-banner-phone-color button is-rounded">
                                    <span class="icon">
                                        <i class="fas fa-phone"></i>
                                    </span>
                                </button>
                            </a>
                            <template x-if="store">
                                <a :href="'https://maps.google.com/?q='+ vendor.stores[0].address1 + ',' + vendor.stores[0].city + ' ' + vendor.stores[0].state">
                                    <button class="km-banner-icon km-banner-direction-color button is-rounded">
                                        <span class="icon">
                                            <i class="fas fa-directions"></i>
                                        </span>
                                    </button>
                                </a>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
    <?php
    $html = ob_get_clean();
    return $html;

}//end vendor_products()


function local_banner()
{
    wp_enqueue_style('vendor-banner', plugin_dir_url(__FILE__).'banner.css');
}//end local_styles()


add_action('wp_enqueue_scripts', 'local_banner');
