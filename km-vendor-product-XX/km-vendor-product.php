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
                    products: [],
                    data: false,
                    categories: [],
                    selectedPrice: 'enter price in gram',
                    selectedTHC: 'All', 
                    selectedCBD: 'All', 
                    selectedCat: 'All',
                    pageSize: '5',
                    meta: false,
                    urlSearchGlobal: false,
                    async getData(url = this.startUrl) 
                    {
                        var self = this;
                        await axios.get(url)
                            .then(function(response) {
                                // console.log(response);
                                self.data = response.data.data;
                                self.name = response.data.data["name"];
                                self.products = response.data.data["products"];                                
                                // remove duplicate categories
                                for ( let i in self.products) {
                                    self.categories[i] =  self.products[i].category;
                                }
                                self.categories = [...new Set(self.categories)];
                                console.log(self.categories.toString());

                                urlPage = url.replace("?include=", "/") + "?page_size=" + self.pageSize +"&page=1";
                                console.log("XXXX" + urlPage + "XXXX");   
                                console.log(urlPage);                                  
                                // urlPage = "https://api.kushmapper.com/v1/vendors/1/products?page_size=5&page=1";
                                axios.get(urlPage)
                                    .then(function(response) {                         
                                        self.products = response.data.data;
                                        self.meta = response.data.meta;                                        
                                    })
                            })
                            .catch(function(error) {
                                console.log(error);
                            })                            
                    },

                    async SearchFilter(url = this.startUrl)
                    {                        
                        var self = this;
                        // debugger;
                        urlSearch = url.replace("?include=", "/");
                        // urlSearch = "https://api.kushmapper.com/v1/vendors/1/products";

                        // 0 0 0 0
                        if( (self.selectedPrice == "enter price in gram") &&
                            (self.selectedTHC == "All") &&
                            (self.selectedCBD == "All") &&
                            (self.selectedCat == "All") )                   
                        {
                            urlSearch = urlSearch + "?page_size=" + self.pageSize;
                        }

                        // 0 0 0 1
                        if( (self.selectedPrice == "enter price in gram") &&
                            (self.selectedTHC == "All") &&
                            (self.selectedCBD == "All") &&
                            (self.selectedCat != "All") )                   
                        {
                            urlSearch = urlSearch + 
                                "?filter[category]=" + self.selectedCat + 
                                "&page_size=" + self.pageSize;
                        }

                        // 0 0 1 0
                        if( (self.selectedPrice == "enter price in gram") &&
                            (self.selectedTHC == "All") &&
                            (self.selectedCBD != "All") &&
                            (self.selectedCat == "All") )                   
                        {
                            urlSearch = urlSearch + 
                                "?filter[maximum_cbd]=" + self.selectedCBD + 
                                "&page_size=" + self.pageSize;;
                        }

                        // 0 0 1 1
                        if( (self.selectedPrice == "enter price in gram") &&
                            (self.selectedTHC == "All") &&
                            (self.selectedCBD != "All") &&
                            (self.selectedCat != "All") )                   
                        {
                            urlSearch = urlSearch + 
                                "?filter[maximum_cbd]=" + self.selectedCBD + 
                                "&filter[category]=" + self.selectedCat + 
                                "&page_size=" + self.pageSize;
                        }

                        // 0 1 0 0
                        if( (self.selectedPrice == "enter price in gram") &&
                            (self.selectedTHC != "All") &&
                            (self.selectedCBD == "All") &&
                            (self.selectedCat == "All") )                   
                        {
                            urlSearch = urlSearch + 
                                "?filter[maximum_thc]=" + self.selectedTHC +
                                "&page_size=" + self.pageSize;
                        }

                        // 0 1 0 1
                        if( (self.selectedPrice == "enter price in gram") &&
                            (self.selectedTHC != "All") &&
                            (self.selectedCBD == "All") &&
                            (self.selectedCat != "All") )                   
                        {
                            urlSearch = urlSearch + 
                                "?filter[maximum_thc]=" + self.selectedTHC + 
                                "&filter[category]=" + self.selectedCat +
                                "&page_size=" + self.pageSize;
                        }

                        // 0 1 1 0
                        if( (self.selectedPrice == "enter price in gram") &&
                            (self.selectedTHC != "All") &&
                            (self.selectedCBD != "All") &&
                            (self.selectedCat == "All") )                   
                        {
                            urlSearch = urlSearch + 
                                "?filter[maximum_thc]=" + self.selectedTHC + 
                                "&filter[maximum_cbd]=" + self.selectedCBD +
                                "&page_size=" + self.pageSize;
                        }

                        // 0 1 1 1
                        if( (self.selectedPrice == "enter price in gram") &&
                            (self.selectedTHC != "All") &&
                            (self.selectedCBD != "All") &&
                            (self.selectedCat != "All") )                   
                        {
                            urlSearch = urlSearch + 
                                "?filter[maximum_thc]=" + self.selectedTHC + 
                                "&filter[maximum_cbd]=" + self.selectedCBD + 
                                "&filter[category]=" + self.selectedCat +
                                "&page_size=" + self.pageSize;
                        }

                        // 1 0 0 0
                        if( (self.selectedPrice != "enter price in gram") &&
                            (self.selectedTHC == "All") &&
                            (self.selectedCBD == "All") &&
                            (self.selectedCat == "All") )                   
                        {
                            urlSearch = urlSearch + 
                                "?filter[maximum_price_gram]=" + self.selectedPrice +
                                "&page_size=" + self.pageSize;
                        }

                        // 1 0 0 1
                        if( (self.selectedPrice != "enter price in gram") &&
                            (self.selectedTHC == "All") &&
                            (self.selectedCBD == "All") &&
                            (self.selectedCat != "All") )                   
                        {
                            urlSearch = urlSearch + 
                                "?filter[maximum_price_gram]=" + self.selectedPrice +
                                "&filter[category]=" + self.selectedCat + 
                                "&page_size=" + self.pageSize;
                        }

                        // 1 0 1 0
                        if( (self.selectedPrice != "enter price in gram") &&
                            (self.selectedTHC == "All") &&
                            (self.selectedCBD != "All") &&
                            (self.selectedCat == "All") )                   
                        {
                            urlSearch = urlSearch + 
                                "?filter[maximum_price_gram]=" + self.selectedPrice +
                                "&filter[maximum_cbd]=" + self.selectedCBD + 
                                "&page_size=" + self.pageSize;
                        }

                        // 1 0 1 1
                        if( (self.selectedPrice != "enter price in gram") &&
                            (self.selectedTHC == "All") &&
                            (self.selectedCBD != "All") &&
                            (self.selectedCat != "All") )                   
                        {
                            urlSearch = urlSearch + 
                                "?filter[maximum_price_gram]=" + self.selectedPrice +
                                "&filter[maximum_cbd]=" + self.selectedCBD +
                                "&filter[category]=" + self.selectedCat +
                                "&page_size=" + self.pageSize;
                        }

                        // 1 1 0 0
                        if( (self.selectedPrice != "enter price in gram") &&
                            (self.selectedTHC != "All") &&
                            (self.selectedCBD == "All") &&
                            (self.selectedCat == "All") )                   
                        {
                            urlSearch = urlSearch + 
                                "?filter[maximum_price_gram]=" + self.selectedPrice +
                                "&filter[maximum_thc]=" + self.selectedTHC +
                                "&page_size=" + self.pageSize;
                        }

                        // 1 1 0 1
                        if( (self.selectedPrice != "enter price in gram") &&
                            (self.selectedTHC != "All") &&
                            (self.selectedCBD == "All") &&
                            (self.selectedCat != "All") )                   
                        {
                            urlSearch = urlSearch + 
                                "?filter[maximum_price_gram]=" + self.selectedPrice +
                                "&filter[maximum_thc]=" + self.selectedTHC +
                                "&filter[category]=" + self.selectedCat +
                                "&page_size=" + self.pageSize;
                        }

                        // 1 1 1 0
                        if( (self.selectedPrice != "enter price in gram") &&
                            (self.selectedTHC != "All") &&
                            (self.selectedCBD != "All") &&
                            (self.selectedCat == "All") )                   
                        {
                            urlSearch = urlSearch + 
                                "?filter[maximum_price_gram]=" + self.selectedPrice +
                                "&filter[maximum_thc]=" + self.selectedTHC +
                                "&filter[maximum_cbd]=" + self.selectedCBD +
                                "&page_size=" + self.pageSize;
                        }

                        // 1 1 1 1
                        if( (self.selectedPrice != "enter price in gram") &&
                            (self.selectedTHC != "All") &&
                            (self.selectedCBD != "All") &&
                            (self.selectedCat != "All") )                   
                        {
                            urlSearch = urlSearch + 
                                "?filter[maximum_price_gram]=" + self.selectedPrice +
                                "&filter[maximum_thc]=" + self.selectedTHC +
                                "&filter[maximum_cbd]=" + self.selectedCBD +
                                "&filter[category]=" + self.selectedCat +
                                "&page_size=" + self.pageSize;
                        }

                        self.urlSearchGlobal = urlSearch;
                        console.log(urlSearch);
                        await axios.get(urlSearch)
                            .then(function(response) {      
                                self.products = response.data.data;  
                                self.meta = response.data.meta;
                                console.log("XXXX Search Meta XXXX"); 
                                console.log(self.meta); 
                            })
                            .catch(function(error) {
                                console.log(error);
                            })         
                    },

                    async UpdatePages(url = this.startUrl)
                    {  
                        var self = this;
                        debugger;
                        urlEntries = url.replace("?include=", "/");
                        // urlEntries = "https://api.kushmapper.com/v1/vendors/1/products";
                        urlEntries = urlEntries + "?page_size=" + self.pageSize;

                        if(self.urlSearchGlobal)
                        {
                            index = self.urlSearchGlobal.indexOf("page_size");
                            urlEntries = self.urlSearchGlobal.slice(0, index);
                            urlEntries = urlEntries + "page_size=" + self.pageSize;
                        }

                        console.log(urlEntries);
                        await axios.get(urlEntries)
                            .then(function(response) {
                                self.products = response.data.data;
                                self.meta = response.data.meta;
                        })

                    },

                    async Pagenation(url)
                    {  
                        console.log(url);
                        if (!url) {
                            return;
                        }
                        var self = this;
                        console.log(self.pageSize);
                        await axios.get(url)
                            .then(function(response) {
                                // debugger;
                                self.products = response.data.data;
                                self.meta = response.data.meta;
                                console.log(self.meta.links);
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
            </template>
                <div class="column is-multiline km-products">
                    <div class="tabs is-toggle is-fullwidth is-medium">
                        <ul class="menu">
                            <li class="is-active">
                            <a>
                                <span class="icon"><i class="fas fa-shopping-basket fa-fw" aria-hidden="true"></i></span>
                                <span>PRODUCT MENU</span>
                            </a>
                            </li>
                            <li>
                            <a>
                                <span class="icon"><i class="fas fa-globe-americas fa-fw" aria-hidden="true"></i></span>
                                <span>MAP</span>
                            </a>
                            </li>
                            <li>
                            <a>
                                <span class="icon"><i class="fas fa-image fa-fw" aria-hidden="true"></i></span>
                                <span>PHOTOS</span>
                            </a>
                            </li> 
                            <li>
                            <a>
                                <span class="icon"><i class="fas fa-comments fa-fw" aria-hidden="true"></i></span>
                                <span>REVIEWS</span>
                            </a>
                            </li>
                        </ul>
                    </div>

                    <div class="columns is-multiline is-mobile is-fullwidth is-vcentered km-filters">
                                
                        <div class="column km-filters-column"> 
                            <label class="km-filters-label km-filters-label-price" for="price"> Price/g </label>  
                            <input class="km-filters-price input is-link is-normal is-half" 
                                    type="number" min="0"
                                    placeholder="enter price in gram"
                                    x-model="selectedPrice"
                            />
                        </div>
            
                        <div class="column km-filters-column">  
                            <label class="km-filters-label km-filters-label-thc"  for="thc"> THC </label>
                            <div class="select entrySelect">                        
                                <select id="thc" x-model="selectedTHC">
                                    <option value="All" name="all">All</option>
                                    <option value="20" name="20"><= 20%</option>     
                                    <option value="15" name="15"><= 15%</option>  
                                    <option value="10" name="10"><= 10%</option>   
                                    <option value="5" name="5"><= 5%</option>                                      
                                </select>
                            </div>
                        </div>
            
                        <div class="column km-filters-column">  
                            <label class="km-filters-label km-filters-label-cdb" for="cbd"> CBD </label>
                            <div class="select entrySelect">                        
                                <select id="cbd" x-model="selectedCBD">
                                    <option value="All" name="all">All</option>
                                    <option value="20" name="20"><= 20%</option>  
                                    <option value="15" name="15"><= 15%</option>   
                                    <option value="10" name="10"><= 10%</option>   
                                    <option value="5" name="5"><= 5%</option>                                            
                                </select>
                            </div>                          
                        </div>
            
                        <div class="column km-filters-column km-filters-column-category">  
                            <label class="km-filters-label" for="cat"> Category </label>
                            <div class="select entrySelect">                      
                                <select name="Category" id= "cat" x-model="selectedCat">
                                        <option value="All" name="all">All</option>
                                        <template x-for="category in categories" :key="category">
                                            <option :value="category" x-text="category"></option>
                                        </template>                            
                                </select>     

                            </div>                          
                        </div>                      
                    </div>  

                    <div class="columns is-mobile is-multiline is-fullwidth km-search">
                        <label class="column is-mobile is-half km-centerLabel"> Show 
                            <div class="select entrySelect">
                                <select name="Entries" id= "entries" x-model="pageSize" x-on:change="UpdatePages()">
                                    <option value="5" name="5">5</option>     
                                    <option value="10" name="10">10</option>     
                                    <option value="25" name="25">25</option>             
                                    <option value="50" name="50">50</option>             
                                    <option value="100" name="100">100</option>      
                                </select>
                            </div>
                            entries
                        </label>
                        <label class="column km-centerLabel">
                            <button class="button is-black" x-on:click="SearchFilter()">Search ...</button>
                        </label>
                    </div>

                    <div class="table-container">
                        <table class="table is-narrow is-hoverable is-striped">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th style="min-width: 350px;">Product</th>
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
                                                        <p x-show="product.price_gram != null"><span x-text="product.price_gram"></span> per 1 g</p>
                                                        <p x-show="product.price_oz_eighth != null"><span x-text="product.price_oz_eighth"></span> per 1/8 oz</p>
                                                        <p x-show="product.price_oz_fourth != null"><span x-text="product.price_oz_fourth"></span> per 1/4 oz</p>
                                                        <p x-show="product.price_oz_half != null"><span x-text="product.price_oz_half"></span> per 1/2 oz</p>
                                                        <p x-show="product.price_oz != null"><span x-text="product.price_oz"></span> per 1 oz</p>
                                                        <!-- <p><span x-text="product.price_gram"></span> per 1 g</p>
                                                        <p><span x-text="product.price_oz_eighth"></span> per 1/8 oz</p>
                                                        <p><span x-text="product.price_oz_fourth"></span> per 1/4 oz</p>
                                                        <p><span x-text="product.price_oz_half"></span> per 1/2 oz</p>
                                                        <p><span x-text="product.price_oz"></span> per 1 oz</p> -->
                                                    </strong>
                                                </div>
                                                <div class="column" style="min-width: 200px;">
                                                    <strong>
                                                        <p>THC: <span x-text="product.thc_min"></span>%-<span x-text="product.thc_max"></span>%</p>
                                                        <p>CBD: <span x-text="product.cbd_min"></span>%-<span x-text="product.cbd_max"></span>%</p>
                                                    </strong>
                                                </div>
                                            </div>
                                    </td>
                                    <td>
                                        <strong>    
                                            <p class="is-size-10" x-text="product.category"></p>
                                        </strong>
                                    </td>
                                    <td class="viewButton"><button class="button is-rounded is-black">View</button></td>
                                </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <template x-if="meta">
                        <div>
                            <template x-for="link in meta.links">
                                <a :class="{'button':true, 'is-active':link.active}" :href="link.url" @click.prevent="Pagenation(link.url)" x-html="link.label"></a>
                            </template>
                        </div>
                    </template>
                </div>
        </div>



    <?php
    $html = ob_get_clean();
    return $html;

}//end vendor_products()


function local_styles()
{
    wp_enqueue_style('product', plugin_dir_url(__FILE__).'product.css');
    wp_enqueue_style('font', 'https://use.fontawesome.com/releases/v5.15.3/css/all.css?wpfas=true');

}//end local_styles()


add_action('wp_enqueue_scripts', 'local_styles');
