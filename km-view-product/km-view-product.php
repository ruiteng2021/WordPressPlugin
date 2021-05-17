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
    $startUrl = 'https://api.kushmapper.com/v1/vendors/1?include=products';
    global $wp;
    $linkUrl = home_url(add_query_arg(array($_GET), $wp->request));

    ob_start();

    ?>
        <script>          
            function kmData() {
                return {
                    startUrl: '<?php echo $startUrl; ?>',
                    linkUrl: '<?php echo  $linkUrl; ?>',
                    data: false,
                    product: false,
                    // name: false,
                    async getData(url = this.startUrl) 
                    {
                        var self = this;
                        await axios.get(url)
                            .then(function(response) {
                                // console.log(response);
                                self.data = response.data.data;
                                products = response.data.data["products"];   
                                let productInfo = self.linkUrl.split("?");
                                console.log(productInfo[1]);
                                productInfo = productInfo[1].split("&");
                                console.log(productInfo);
                                id = productInfo[1].split("=");
                                console.log(id);
                                for ( let product of products) {
                                    // debugger;
                                    console.log("XXX " + product.id + " XXX");
                                    if (product.id == id[1]){
                                        self.product = product;  
                                        break;
                                    }
                                }
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
                <div class="column is-half">    
                    <div class="content">
                        <strong><h1 x-show="product.price_gram != null"><span x-text="product.price_gram"></span><span>$&nbsp;per 1 g</span></h1></strong>
                        <h4 x-show="product.category != null"><span x-text="product.category"></span></h4>
                        <p x-show="product.description != null"><span x-text="product.description"></span></p>   
                        <div class="content is-medium km-product-detail-link-1">  
                            <p>Vendors: <a :href=data.website><span style="text-decoration: underline;" x-text="data.name"></span></a></p>
                            <a class="button is-rounded is-dark" :href=data.website>VIEW WEBSITE</a>
                        <div>                    
                    </div>
                </div>
            </template>
            <template x-if="product">
                <div class="column is-one-quarter">    
                    <div class="content">
                        <strong><h1> Price </h1></strong>
                        <p x-show="product.price_gram != null"><span>Per 1 gram     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span x-text="product.price_gram"></span></p>
                        <p x-show="product.price_oz_eighth != null"><span>Per 1/8 oz&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span x-text="product.price_oz_eighth"></span></p>
                        <p x-show="product.price_oz_fourth != null"><span>Per 1/4 oz&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span x-text="product.price_oz_fourth"></span></p>
                        <p x-show="product.price_oz_half != null"><span>Per 1/2 oz  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span x-text="product.price_oz_half"></span></p>
                        <p x-show="product.price_oz != null"><span>Per 1 oz         &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span x-text="product.price_oz"></span></p>
                        <p x-show="product.thc != null"><span>THC                   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span x-text="product.thc"></span></p>
                        <p x-show="product.cbd != null"><span>CBD                   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span x-text="product.cbd"></span></p>
                    </div>
                </div>
            </template>
            <div class="content is-medium km-product-detail-link-2">  
                <p>Vendors: <a :href=data.website><span style="text-decoration: underline;" x-text="data.name"></span></a></p>
                <a class="button is-rounded is-dark" :href=data.website>VIEW WEBSITE</a>
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
