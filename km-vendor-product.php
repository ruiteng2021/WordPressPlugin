<?php
/**
 * Plugin Name: Kushmapper Vendor Product 
 * Description: Shows the vendor's product information
 * Author: SlyFox
 * Version: 0.1.1
 */

add_shortcode('vendor-products', 'vendor_products');

function vendor_products()
{
    $startUrl = 'https://api.kushmapper.com/v1/vendors/1?include=products';

    ob_start(); 
    
    ?>
        <script>           
            function kmData() {
                return {
                    startUrl: '<?php echo $startUrl; ?>',
                    name: false,
                    products: false,
                    data: false,
                    async getData(url = this.startUrl) {
                        var self = this;
                        await axios.get(url)
                            .then(function(response) {
                                // console.log(response);
                                self.data = response.data.data;
                                self.name = response.data.data["name"];
                                self.products = response.data.data["products"];
                            })
                            .catch(function(error) {
                                console.log(error);
                            })                            
                    },
                };
            }
        </script>

        <div class="columns is-full" x-data="kmData()" x-init="getData()">
            <template x-if="data">
                <div class="km-logo column is-one-quarter">    
                    <figure class="vendorlogo">
                        <img :src="data.logo_url" alt="product img" />
                    </figure>   
                    <p x-text="data.name"> </p>
                    <p x-text="data.phone"> </p>
                    <a class="vendorWebsite" href=data.website><p class="vendorMail" x-text="data.website"> </p></a>
                    <button class="button is-black">Claim Listing</button>
                </div>
                <div class="columns is-multiline km-products">
                    <table class="table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Product</th>
                                <th>Category</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="product in products">
                            <tr>                          
                                <td>
                                    <figure class="image is-96x96">
                                        <img :src="product.image_url" alt="product img" />
                                    </figure>                                    
                                </td>
                                <td>
                                    <strong><p class="is-size-5" x-text="product.name" ></p></strong>
                                        <div class="columns is-mobile is-multiline">
                                            <div class="column">
                                                <strong>
                                                    <p><span x-text="product.price_gram"></span> per 1 g</p>
                                                    <p><span x-text="product.price_oz_eighth"></span> per 1/8 oz</p>
                                                    <p><span x-text="product.price_oz_fourth"></span> per 1/4 oz</p>
                                                    <p><span x-text="product.price_oz_half"></span> per 1/2 oz</p>
                                                    <p><span x-text="product.price_oz"></span> per 1 oz</p>
                                                </strong>
                                            </div>
                                            <div class="column">
                                                <strong>
                                                    <p>THC: <span x-text="product.thc_min"></span>%-<span x-text="product.thc_max"></span>%</p>
                                                    <p>CBD: <span x-text="product.cbd_min"></span>%-<span x-text="product.cbd_max"></span>%</p>
                                                </strong>
                                            </div>
                                        </div>


                                </td>
                                <td><p class="is-size-7" x-text="product.category"></p></td>
                                <td class="viewButton"><button class="button is-rounded is-black">View</button></h4></td>
                            </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </template>
        </div>

    <?php

    $html = ob_get_clean();
    return $html;
}


function local_styles(){
    wp_enqueue_style( 'product', plugin_dir_url( __FILE__ ) . 'product.css' );
}

add_action( 'wp_enqueue_scripts', 'local_styles' );