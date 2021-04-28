<?php
/**
 * Plugin Name: Kushmapper vendor Product 
 * Description: Shows the vendor's product information
 * Author: SlyFox
 * Version: 0.1.1
 */

add_shortcode('vendor-products', 'vendor_products');

function vendor_products()
{
    $startUrl = 'http://api.kushmapper.com/v1/vendors/1?include=products';

    ob_start(); 
    
    ?>
        <script>           
            function kmData() {
                return {
                    startUrl: '<?php echo $startUrl; ?>',
                    name: false,
                    products: false,
                    getData(url = this.startUrl) {
                        var self = this;
                        axios.get(url)
                            .then(function(response) {
                                console.log(response);
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

        <div x-data="kmData()" x-init="getData()">
            <template x-if="products">
                <div class="columns is-mobile is-multiline">
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
                                    <img :src="product.image_url" alt="product img" />
                                </td>
                                <td><h4 x-text="product.name" ></h4></td>
                                <td><h4 x-text="product.category"></h4></td>
                                <td><h4 x-text="product.category"></h4></td>
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