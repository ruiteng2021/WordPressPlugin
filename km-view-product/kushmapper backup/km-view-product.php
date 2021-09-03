<?php
/**
 * Plugin Name: Kushmapper View Product
 * Description: Shows select product detail
 * Author: SlyFox
 * Version: 0.1.1
 */

add_shortcode('view-product', 'view_product');


function view_product()
{
    $slug = get_query_var('slug');
    $startUrl = "https://api.kushmapper.com/v1/products/slug/{$slug}";

    ob_start();

    ?>
        <script>          
            function kmData() {
                return {
                    startUrl:   '<?php echo $startUrl; ?>',
                    product: false,
                    name: {},
                    async getData(url = this.startUrl) 
                    {
                        var self = this;
                        // console.log(url);
                        await axios.get(url)
                            .then(function(response) {
                                // console.log(response);
                                self.product = response.data.data;
                                self.name.slug = self.product.vendor.slug;
                                self.name.name = self.product.vendor.name;
                            })
                            .catch(function(error) {
                                console.log(error);
                            })                  
                    },

                };
            }

        </script>

        <div class="columns is-full" x-data="kmData()" x-init="getData()">
            <template x-if="product">
                <div class="column is-one-quarter">    
                    <figure class="vendorlogo">
                            <img :src="product.image_url" alt="product img" />
                    </figure>   
                </div>
            </template>
            <template x-if="product">
                <div class="column is-half" style="padding-right: 20px">    
                    <div class="content">
                        <strong><h1 x-show="product.name != null"><span x-text="product.name"></span></h1></strong>
                        <strong><h2 x-show="product.price_gram != null"><span x-text="product.price_gram"></span><span>$&nbsp;per 1 g</span></h2></strong>
                        <h4 x-show="product.category != null"><span x-text="product.category"></span></h4>
                        <p x-show="product.description != null"><span x-text="product.description"></span></p>   
                        <div class="content is-medium km-product-detail-link-1">  
                            <!-- <p>Vendors: <a :href="'/wordpress/vendor/' + name.slug"><span style="text-decoration: underline; color: #197826;" x-text=name.name></span></a></p> -->
                            <h4>Vendors: <a :href="'/vendor/' + name.slug"><span style="text-decoration: underline; color: #197826;" x-text=name.name></span></a></h4>
                            <a class="button is-rounded is-dark" :href=product.url target="_blank">VIEW WEBSITE</a>
                        <div>                    
                    </div>
                </div>
            </template>
            <template x-if="product">
                <div class="column is-one-quarter">    
                    <div class="content">
                        <strong><p style="margin-bottom: 10px"> Price </p></strong>
                        <template x-if="product.price_gram">
                            <div class="columns is-mobile">
                                <div class="column is-half km-view-single-price">
                                    <p><span>Per 1 gram</span></p>
                                </div>
                                <div class="column is-half km-view-single-price">
                                    <p>$<span x-text="product.price_gram"></span></p>
                                </div>
                            </div>
                        </template>
                        <template x-if="product.price_oz_eighth">
                            <div class="columns is-mobile">
                                <div class="column is-half km-view-single-price">
                                    <p><span>Per 1/8 oz</span></p>
                                </div>
                                <div class="column is-half km-view-single-price">
                                    <p>$<span x-text="product.price_oz_eighth"></span></p>
                                </div>
                            </div>
                        </template>
                        <template x-if="product.price_oz_fourth">
                            <div class="columns is-mobile">
                                <div class="column is-half km-view-single-price">
                                    <p><span>Per 1/4 oz</span></p>
                                </div>
                                <div class="column is-half km-view-single-price">
                                    <p>$<span x-text="product.price_oz_fourth"></span></p>
                                </div>
                            </div>
                        </template>
                        <template x-if="product.price_oz_half">
                            <div class="columns is-mobile">
                                <div class="column is-half km-view-single-price">
                                    <p><span>Per 1/2 oz</span></p>
                                </div>
                                <div class="column is-half km-view-single-price">
                                    <p>$<span x-text="product.price_oz_half"></span></p>
                                </div>
                            </div>
                        </template>
                        <template x-if="product.price_oz">
                            <div class="columns is-mobile">
                                <div class="column is-half km-view-single-price">
                                    <p><span>Per 1 oz</span></p>
                                </div>
                                <div class="column is-half km-view-single-price">
                                    <p>$<span x-text="product.price_oz"></span></p>
                                </div>
                            </div>
                        </template>

                        <template x-if="product.thc_min && product.thc_max">
                            <div class="columns is-mobile">
                                <div class="column is-half km-view-single-price">
                                    <p>THC:</p>
                                </div>
                                <div class="column is-half km-view-single-price">
                                    <p><span x-text="product.thc_min"></span>%-<span x-text="product.thc_max"></span>%</p>
                                </div>
                            </div>
                        </template>
                        <template x-if="!product.thc_min || !product.thc_max">
                            <div class="columns is-mobile">
                                <div class="column is-half km-view-single-price">
                                    <p>THC:</p>
                                </div>
                                <div class="column is-half km-view-single-price">
                                    <p><span>Not Available</span></p>
                                </div>
                            </div>
                        </template>

                        <template x-if="product.cbd_min && product.cbd_max">
                            <div class="columns is-mobile">
                                <div class="column is-half km-view-single-price">
                                    <p>CBD:</p>
                                </div>
                                <div class="column is-half km-view-single-price">
                                    <p><span x-text="product.cbd_min"></span>%-<span x-text="product.cbd_max"></span>%</p>
                                </div>
                            </div>
                        </template>

                        <template x-if="!product.cbd_min || !product.cbd_max">
                            <div class="columns is-mobile">
                                <div class="column is-half km-view-single-price">
                                    <p>CBD:</p>
                                </div>
                                <div class="column is-half km-view-single-price">
                                    <p><span>Not Available</span></p>
                                </div>
                            </div>
                        </template>

                        <!-- <p x-show="product.price_gram != null"><span>Per 1 gram     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>$<span x-text="product.price_gram"></span></p>
                        <p x-show="product.price_oz_eighth != null"><span>Per 1/8 oz&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>$<span x-text="product.price_oz_eighth"></span></p>
                        <p x-show="product.price_oz_fourth != null"><span>Per 1/4 oz&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>$<span x-text="product.price_oz_fourth"></span></p>
                        <p x-show="product.price_oz_half != null"><span>Per 1/2 oz  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>$<span x-text="product.price_oz_half"></span></p>
                        <p x-show="product.price_oz != null"><span>Per 1 oz         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>$<span x-text="product.price_oz"></span></p>
                        
                        <template x-if="product.thc_min && product.thc_max">
                            <p>THC: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span x-text="product.thc_min"></span>%-<span x-text="product.thc_max"></span>%</p>
                        </template>
                        <template x-if="!product.thc_min || !product.thc_max">
                            <p>THC: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>Not Available</span></p>
                        </template>
                        <template x-if="product.cbd_min && product.cbd_max">
                            <p>CBD: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span x-text="product.cbd_min"></span>%-<span x-text="product.cbd_max"></span>%</p>
                        </template>
                        <template x-if="!product.cbd_min || !product.cbd_max">
                            <p>CBD: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>Not Available</span></p>
                        </template>    -->
                        
                        
                    </div>
                </div>
            </template>
            <div class="content is-medium km-product-detail-link-2"> 
                <!-- <p>Vendors: <a :href="'/wordpress/vendor/' + name.slug"><span style="text-decoration: underline; color: #197826;" x-text=name.name></span></a></p> -->
                <h4>Vendors: <a :href="'/vendor/' + name.slug"><span style="text-decoration: underline; color: #197826;" x-text=name.name></span></a></h4>
                <a class="button is-rounded is-dark" :href=product.url>VIEW WEBSITE</a>
            <div>  
        </div>
        
    <?php
    $html = ob_get_clean();
    return $html;

}//end vendor_products()


function local_styles_detail()
{
    wp_enqueue_style('view-product', plugin_dir_url(__FILE__).'view-product.css');
}//end local_styles()


add_action('wp_enqueue_scripts', 'local_styles_detail');
