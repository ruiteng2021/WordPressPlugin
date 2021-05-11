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
        <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB44vENDVAXY11oPRg4tSuHH2EEP9xhI1A&callback=LocationMap"type="text/javascript"></script>
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
                    menuTab: 'product',
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
                    
                    InitMarkers()
                    {
                        console.log("AAAAA in map initMarkers AAAAA");
                        // if(google != undefined)
                        // {
                        //     user.marker = new google.maps.Marker({
                        //     position: e.position,
                        //     map: map,
                        //     icon: '/img.png'
                        //     });
                        // }
                    },
                };
            }

            function LocationMap()
            {
                // console.log("AAAAA X=" + x + "in map AAAAA");
                // console.log("AAAAA Y=" + y + "in map AAAAA");
                console.log("AAAAAAAAAAAAAA");
                // debugger;
                var mapOptions = {
                    center: new google.maps.LatLng(42.976348, -81.2514795),
                    zoom: 10,
                    mapTypeId: google.maps.MapTypeId.HYBRID
                }
                var map = new google.maps.Map(document.getElementById("km-map"), mapOptions);
                var mapReadyEvent = new CustomEvent('map-ready');
                window.dispatchEvent(mapReadyEvent);
            }
        </script>

        <div class="columns is-full" x-data="kmData()" x-init="getData()" @map-ready.window="getData()">
        <template x-if="data">
            <!-- Left column for logo -->
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
            <!-- Right column for product -->
            <div class="column km-products">
                <!-- Menu tabs -->
                <div class="tabs is-toggle is-fullwidth is-medium">
                    <ul class="menu">
                        <li :class="{'is-active' : menuTab === 'product'}">
                        <a href="#km-product-memu"
                            @click.prevent="menuTab = 'product'"
                        >
                            <span class="icon"><i class="fas fa-shopping-basket fa-fw" aria-hidden="true"></i></span>
                            <span>PRODUCT MENU</span>
                        </a>
                        </li>
                        <li :class="{'is-active' : menuTab === 'map'}">
                        <a href="#km-location"
                            @click.prevent="menuTab = 'map'; InitMarkers()"
                        >
                            <span class="icon"><i class="fas fa-globe-americas fa-fw" aria-hidden="true"></i></span>
                            <span>LOCATION</span>
                        </a>
                        </li>
                        <li :class="{'is-active' : menuTab === 'photos'}">
                        <a href="#km-photos"
                            @click.prevent="menuTab = 'photos'"
                        >
                            <span class="icon"><i class="fas fa-image fa-fw" aria-hidden="true"></i></span>
                            <span>PHOTOS</span>
                        </a>
                        </li> 
                        <li :class="{'is-active' : menuTab === 'reviews'}">
                        <a href="#km-reviews"
                            @click.prevent="menuTab = 'reviews'"
                        >
                            <span class="icon"><i class="fas fa-comments fa-fw" aria-hidden="true"></i></span>
                            <span>REVIEWS</span>
                        </a>
                        </li>
                    </ul>
                </div>

                <div id="km-product-menu" x-show="menuTab === 'product'">
                    <!-- Filter dropsown lists -->
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
    
                    <!-- Search button and entyry dropdown list -->
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
    
                    <!-- Table colums -->
                    <div class="columns km-products-info">
                        <div class="column is-two-thirds">
                            <div class="km-product-pic-title">     
                            </div>
                            <div class="km-product-price-title">   
                                <strong>Product</strong>
                            </div>
                            <div class="km-product-concentrate-title"> 
                            </div>
                        </div>
                        <div class="column is-one-thirds">
                            <div class="km-product-category-title">                  
                                <strong>Category</strong>
                            </div>
                            <div class="km-product-view-title">                   
                            </div>
                        </div>
                    </div>
    
                    <div class="km-table">
                        <template x-for="product in products">
                        <div class="columns km-products-info">
                            <div class="column is-two-thirds">
                                <div class="km-product-pic">                  
                                    <figure class="image is-128x128">
                                        <img :src="product.image_url" alt="product img" />
                                    </figure>        
                                </div>
                                <div class="km-product-price">                
                                    <strong>
                                        <a :href="product.url"><p class="is-size-5" x-text="product.name" ></p></a>
                                        <p x-show="product.price_gram != null"><span x-text="product.price_gram"></span> per 1 g</p>
                                        <p x-show="product.price_oz_eighth != null"><span x-text="product.price_oz_eighth"></span> per 1/8 oz</p>
                                        <p x-show="product.price_oz_fourth != null"><span x-text="product.price_oz_fourth"></span> per 1/4 oz</p>
                                        <p x-show="product.price_oz_half != null"><span x-text="product.price_oz_half"></span> per 1/2 oz</p>
                                        <p x-show="product.price_oz != null"><span x-text="product.price_oz"></span> per 1 oz</p>
                                        <p class="km-no-dispaly">THC: <span x-text="product.thc_min"></span>%-<span x-text="product.thc_max"></span>%</p>
                                        <p class="km-no-dispaly">CBD: <span x-text="product.cbd_min"></span>%-<span x-text="product.cbd_max"></span>%</p>
                                    </strong>
                                </div>
                                <div class="km-product-concentrate">                  
                                    <strong>
                                        <p>THC: <span x-text="product.thc_min"></span>%-<span x-text="product.thc_max"></span>%</p>
                                        <p>CBD: <span x-text="product.cbd_min"></span>%-<span x-text="product.cbd_max"></span>%</p>
                                    </strong>
                                </div>
                            </div>
                            <div class="column is-one-thirds">
                                <div class="km-product-category">                  
                                    <strong>    
                                        <p class="is-size-10" x-text="product.category"></p>
                                    </strong>
                                </div>
                                <div class="km-product-view">                   
                                    <button class="button is-rounded is-dark">View</button>
                                </div>
                            </div>
                        </div>
                        </template>
                    </div>
                    
                    <template x-if="meta">
                        <div>
                            <template x-for="link in meta.links">
                                <a :class="{'button':true, 'is-active':link.active}" :href="link.url" @click.prevent="Pagenation(link.url)" x-html="link.label"></a>
                            </template>
                        </div>
                    </template>
                </div>
                <div id="km-location" x-show="menuTab === 'map'" >
                    <strong><p> Still under construction! </p> </strong>
                    <div id="km-map" style="width:400px;height:400px;background:grey"> </div>
                </div>
                <div id="km-photos" x-show="menuTab === 'photos'">
                    <strong><p> this is photos </p> </strong>
                </div>
                <div id="km-reviews" x-show="menuTab === 'reviews'">
                    <strong><p> this is reviews </p> </strong>
                </div>
               
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
    // wp_enqueue_script('map', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyCAEc6aw19DrUE7sN0CoE-VhM20ighnm7Y&callback=LocationMap#asyncload', array() );
}//end local_styles()


add_action('wp_enqueue_scripts', 'local_styles');
