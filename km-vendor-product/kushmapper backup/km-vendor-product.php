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

    $slug = get_query_var('slug');
    $startUrl = "https://api.kushmapper.com/v1/vendors/slug/". $slug . "?include=products";
    $site_key = "6LfKjiQaAAAAAEB4l9m5d6bbzbuxRJX4i2WFPOFA"; // slyfox
    $secret_key = "6LfKjiQaAAAAAMeizHO-a6JaAxAI-LUAJgFfHgj7"; //SlyFox
    // $site_key = "6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"; // google development key
    // $secret_key = "6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe"; // google development key

    ob_start();

    ?>
        <!-- <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB44vENDVAXY11oPRg4tSuHH2EEP9xhI1A"type="text/javascript"></script> -->
        <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB44vENDVAXY11oPRg4tSuHH2EEP9xhI1A&callback=initMap"type="text/javascript"></script>
        <script async defer src="https://www.google.com/recaptcha/api.js"type="text/javascript"></script>
        <script>             
        
            let map, infoWindow, directionsService, directionsRenderer;
            function initMap() {
                directionsService = new google.maps.DirectionsService();
                directionsRenderer = new google.maps.DirectionsRenderer();
                let mapOptions = {
                    center: new google.maps.LatLng(42.976348, -81.2514795),
                    zoom: 9,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                }
                map = new google.maps.Map(document.getElementById("km-map"), mapOptions);
                directionsRenderer.setMap(map);     
                infoWindow = new google.maps.InfoWindow();     
                let mapReadyEvent = new CustomEvent('map-ready');
                window.dispatchEvent(mapReadyEvent);   
            }

            function kmData() {
                return {
                    startUrl: '<?php echo $startUrl; ?>',

                    // google reCaptcha begin//
                    site_key: '<?php echo $site_key; ?>',
                    // google reCaptcha end //

                    // filter info //
                    filter: {
                        page_size: 10,
                        category: 'All',
                        maxPrice: '',
                        weight: 'All',
                        minThc: 'All',
                        minCbd: 'All',
                    },

                    // hours: {
                    //     mon_open: "9:00",
                    //     mon_close: "8:00",
                    //     tue_open: "9:00",
                    //     tue_close: "8:00",
                    //     wed_open: "9:00",
                    //     wed_close: "8:00",
                    //     thu_open: "9:00",
                    //     thu_close: "8:00",
                    //     fri_open: "9:00",
                    //     fri_close: "8:00",
                    //     sat_open: "9:00",
                    //     sat_close: "8:00",
                    //     sun_open: "9:00",
                    //     sun_close: "8:00"
                    // },
                    hours: false,
                    day: false,
                    // global used info //
                    data: false, // for logo and location tab
                    menuTab: 'product',    
                    pageSize: '5',
                    categories: [],
                    products: [],
                    meta: false,
                    urlSearchGlobal: false,
                    store: false,
                    serviceArea: false,
                    // global used info end //                                   

                    // Google map begin//
                    googleMap: {
                        map: false,
                        transport: "driving",
                        transportUnit: "kilometers",
                        directionsService: false,
                        directionsRenderer: false,
                        infoWindow: false,
                    },
                    // Google map end //

                    async getData(url = this.startUrl) 
                    {
                        var self = this;
                        productUrl = url.replace("?include=", "/") + "?page_size=" + self.pageSize +"&page=1";  
                        const generalInfo = axios.get(url);
                        const productInfo = axios.get(productUrl);
                        await axios.all([generalInfo, productInfo]).then(axios.spread(function(generalInfoRes, productInfoRes) {
                                self.data = generalInfoRes.data.data;
                                products = generalInfoRes.data.data["products"];                                
                                // remove duplicate categories
                                for ( let i in products) {
                                    self.categories[i] =  products[i].category;
                                }
                                self.categories = [...new Set(self.categories)];

                                self.products = productInfoRes.data.data;
                                self.meta = productInfoRes.data.meta;  
                            }))
                            .catch(function(error) {
                                console.log(error);
                            }) 

                        // if (Object.keys(self.data.service_areas).length == 0)
                        // {
                        //     // create dummy date to avoid display error
                        //     self.data.service_areas = [{city: "", state: '', country: ''}];
                        //     console.log(self.data.service_areas);
                        // }

                        if(Object.keys(self.data.service_areas).length != 0)
                            self.serviceArea = true;

                        if(self.data.stores.length != 0)
                            self.store = true;

                        // self.detectWeekday();
                        self.setGooglemapMarkers();
                        // // self.map = map;
                        // // self.infoWindow = infoWindow;
                        // // self.googleMap.directionsService = directionsService;
                        // // self.googleMap.directionsRenderer = directionsRenderer;

                        // var marker = new google.maps.Marker({
                        //     position: pos,
                        //     // title:"Hello World!"
                        // });
                        // marker.setMap(map);
                                   
                    },


                    setGooglemapMarkers() 
                    {
                        var self = this;
                        let locations = [];

                        if (self.store)
                        {
                            for (let store of self.data.stores)
                            {
                                let location = [];
                                location.push(store.name);
                                location.push(store.lat);
                                location.push(store.lng);
                                locations.push(location);
                            }
                        }

                        if (self.serviceArea)
                        {
                            for (let serviceArea of self.data.service_areas)
                            {
                                let location = [];
                                location.push(serviceArea.city);
                                location.push(serviceArea.lat);
                                location.push(serviceArea.lng);
                                locations.push(location);
                            }
                        }

                        if (self.store || self.serviceArea)
                        {
                            console.log(locations);
                            map.setCenter(new google.maps.LatLng(locations[0][1], locations[0][2]));
                            for (i = 0; i < locations.length; i++) {  
                                marker = new google.maps.Marker({
                                    position: new google.maps.LatLng(locations[i][1], locations[i][2]),
                                    map: map
                                });
                            }       
                        }
                    },

                    async updateApiData(url)
                    {
                        var self = this;
                        // console.log(url);
                        await axios.get(url)
                        .then(function(response) {      
                            self.products = response.data.data;  
                            self.meta = response.data.meta;
                        })
                        .catch(function(error) {
                            console.log(error);
                        })    
                    },

                    searchProduct() 
                    {
                        url = this.getApiString();
                        this.urlSearchGlobal = url;
                        this.updateApiData(url);   
                    },

                    UpdatePages(url = this.startUrl)
                    {  
                        var self = this;
                        urlEntries = url.replace("?include=", "/");
                        // urlEntries = "https://api.kushmapper.com/v1/vendors/1/products";
                        urlEntries = urlEntries + "?page_size=" + self.pageSize;
                        if(self.urlSearchGlobal)
                        {
                            index = self.urlSearchGlobal.indexOf("page_size");
                            urlEntries = self.urlSearchGlobal.slice(0, index);            
                            urlEntries = urlEntries + "page_size=" + self.pageSize;
                            index = self.urlSearchGlobal.indexOf("&filter");
                            urlEntriesPart2 = self.urlSearchGlobal.slice(index);  
                            urlEntries = urlEntries + urlEntriesPart2;
                        }
                        this.updateApiData(urlEntries);
                    },

                    Pagenation(url)
                    {  
                        if (!url) {
                            return;
                        }
                        this.updateApiData(url);
                    },      
                    
                    // dynamically build api url string based on form inputs.  Use ternary structure to set empty string if input is empty.
                    getApiString(url = this.startUrl) 
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

                        this.filter.page_size = this.pageSize;
                        // let baseString = "https://api.kushmapper.com/v1/products";                        
                        // let baseString = "https://api.kushmapper.com/v1/vendors/1/products";
                        let baseString = url.replace("?include=", "/");
                        let pageSizeString = "?page_size=" + this.filter.page_size;
                        let maxPriceString = this.filter.weight == 'All' && this.filter.maxPrice != '' ? "&filter[maximum_price_any]=" + this.filter.maxPrice : '';
                        let weightStringAll = this.filter.weight != 'All' && this.filter.maxPrice != '' ? sortPrice + weightSelect + this.filter.maxPrice : '';
                        let weightStringSingle = this.filter.weight != 'All' && this.filter.maxPrice == '' ? sortPrice : '';
                        let categoryString = this.filter.category != 'All' ? "&filter[category]=" + this.filter.category : '';
                        let minThcString = this.filter.minThc != 'All' ? thcString : '';
                        let minCbdString = this.filter.minCbd != 'All' ? cbdString : '';

                        return baseString + pageSizeString + maxPriceString + weightStringAll + weightStringSingle + categoryString + minThcString + minCbdString;
                    },
                    
                    resetFilter()
                    { 
                        this.filter.category = 'All';
                        this.filter.maxPrice = '';
                        this.filter.weight = 'All';
                        this.filter.minThc = 'All';
                        this.filter.minCbd = 'All';
                        this.searchProduct();
                    },

                    UpdateInputType()
                    {
                        jQuery(".km-max-thc-input").attr('type', 'number'); 
                        jQuery(".km-max-thc-input").attr('min', '0');                    
                    },

                    getCurrentLocation()
                    {
                        var self = this;
                        // console.log("XXXXX in map GetCurrentCoordinate XXXXX");
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(
                                (position) => {
                                    const pos = {
                                        lat: position.coords.latitude,
                                        lng: position.coords.longitude,
                                    };
                                    // self.googleMap.infoWindow.setPosition(pos);
                                    infoWindow.setPosition(pos);
                                    // self.infoWindow.setContent("Location found.");
                                    // self.infoWindow.open(self.map);
                                    console.log(pos);
                                    console.log("AAAA" + pos.lat + "," + pos.lng + "AAAA");
                                    // pos.lat = 43.00756;
                                    // pos.lng = -81.21131;
                                    coordinate = pos.lat + ", " + pos.lng;
                                    document.getElementById("Coordinate").value = coordinate; 
                                    // self.googleMap.map.setCenter(pos);
                                    map.setCenter(pos);

                                    var marker = new google.maps.Marker({
                                        position: pos,
                                        // title:"Hello World!"
                                    });

                                    // To add the marker to the map, call setMap();
                                    // marker.setMap(self.googleMap.map);
                                    marker.setMap(map);
                                },
                                () => {
                                    // this.HandleLocationError(true, self.googleMap.infoWindow, self.googleMap.map.getCenter());
                                    this.HandleLocationError(true, infoWindow, map.getCenter());
                                }
                            );
                        } 
                        else 
                        {
                            // Browser doesn't support Geolocation
                            // this.HandleLocationError(false, self.googleMap.infoWindow, self.googleMap.map.getCenter());
                            this.HandleLocationError(false, infoWindow, map.getCenter());
                        }
                    },

                    HandleLocationError(browserHasGeolocation, infoWindow, pos) 
                    {
                        var self = this;
                        infoWindow.setPosition(pos);
                        infoWindow.setContent(
                            browserHasGeolocation
                            ? "Error: The Geolocation service failed."
                            : "Error: Your browser doesn't support geolocation."
                        );
                        infoWindow.open(self.googleMap.map);
                    },

                    GetVendorDirection()
                    {
                        var self = this;
                        if(!self.store)
                        {
                            return;
                        }
                        coord = document.getElementById('Coordinate').value;
                        coord = coord.split(",");
                        start = new google.maps.LatLng(coord[0], coord[1]);
                        console.log(start);
                        end = self.data.stores[0].city + "," + self.data.stores[0].state;
                        console.log(self.googleMap.transport.toUpperCase());
                        console.log(self.googleMap.transportUnit);
                        var request = {
                            origin: start,
                            destination: end,
                            travelMode: self.googleMap.transport.toUpperCase(),
                            unitSystem: self.googleMap.transportUnit == "kilometers" ? google.maps.UnitSystem.METRIC : google.maps.UnitSystem.IMPERIAL,
                        };
                        self.googleMap.directionsService.route(request, function(result, status) {
                            if (status == 'OK') {
                            self.googleMap.directionsRenderer.setDirections(result);
                            }
                        });
                    },
                   
                    detectWeekday()
                    {
                        var self = this;                      
                        if(self.store && self.data.stores[0].hours){
                             self.hours.mon_open    = self.data.stores[0].hours.days.mon.open;
                             self.hours.mon_close   = self.data.stores[0].hours.days.mon.close;
                             self.hours.tue_open    = self.data.stores[0].hours.days.tue.open;
                             self.hours.tue_close   = self.data.stores[0].hours.days.tue.close;
                             self.hours.wed_open    = self.data.stores[0].hours.days.wed.open;
                             self.hours.wed_close   = self.data.stores[0].hours.days.wed.close;
                             self.hours.thu_open    = self.data.stores[0].hours.days.thu.open;
                             self.hours.thu_close   = self.data.stores[0].hours.days.thu.close;
                             self.hours.fri_open    = self.data.stores[0].hours.days.fri.open;
                             self.hours.fri_close   = self.data.stores[0].hours.days.fri.close;
                             self.hours.sat_open    = self.data.stores[0].hours.days.sat.open;
                             self.hours.sat_close   = self.data.stores[0].hours.days.sat.close;  
                             self.hours.sun_open    = self.data.stores[0].hours.days.sun.open;
                             self.hours.sun_close   = self.data.stores[0].hours.days.sun.close;
                        }
                       
                        function setColorTimer() {                           
                            let date = new Date();
                            day = date.getDay();
                            // self.day = date.getDay();
                            console.log("day:" + day);
                            // reset color to original
                            jQuery(".km-sunday").attr("style", "color:#4a4a4a !important");
                            jQuery(".km-monday").attr("style", "color:#4a4a4a !important");
                            jQuery(".km-tuesday").attr("style", "color:#4a4a4a !important");
                            jQuery(".km-wednsday").attr("style", "color:#4a4a4a !important");
                            jQuery(".km-thursday").attr("style", "color:#4a4a4a !important");
                            jQuery(".km-friday").attr("style", "color:#4a4a4a !important");
                            jQuery(".km-saturday").attr("style", "color:#4a4a4a !important");

                            switch(day) {
                                case 0:
                                    jQuery(".km-sunday").attr("style", "color:#44c553 !important");
                                    break;
                                case 1:
                                    jQuery(".km-monday").attr("style", "color: #44c553 !important");
                                    break;
                                case 2:
                                    jQuery(".km-tuesday").attr("style", "color:#44c553 !important");
                                    break;
                                case 3:
                                    jQuery(".km-wednsday").attr("style", "color:#44c553 !important");
                                    break;
                                case 4:
                                    jQuery(".km-thursday").attr("style", "color:#44c553 !important");
                                    break;
                                case 5:
                                    jQuery(".km-friday").attr("style", "color:#44c553 !important");
                                    break;
                                case 6:
                                    jQuery(".km-saturday").attr("style", "color:#44c553 !important");
                                    break;
                            }
                        }  
                        setColorTimer();
                        // update color every hour
                        setInterval(setColorTimer, 3600000);                      
                    },

                    async getCurrentLocationByIp(){
                        var self = this;    
                        currentPosition = {};
                        // url = "http://ip-api.com/json";
                        url = "https://api.ipdata.co?api-key=02d8d5bc5cda112d76192486d95426dc5ccb3e6e394cc105bf5ad0dd";
                        await axios.get(url)
                        .then(function(response) {     
                            console.log(response);
                            currentPosition.lat = parseFloat(response.data.latitude);
                            currentPosition.lng = parseFloat(response.data.longitude);

                            var pos = new google.maps.LatLng(currentPosition.lat, currentPosition.lng);
                            self.googleMap.map.setCenter(pos);

                            var marker = new google.maps.Marker({
                                position: pos,
                                // title:"Hello World!"
                            });

                            // To add the marker to the map, call setMap();
                            marker.setMap(self.googleMap.map);

                            coordinate = currentPosition.lat + ", " + currentPosition.lng;
                            document.getElementById("Coordinate").value = coordinate; 

                        })
                        .catch(function(error) {
                            console.log(error);
                        })    
                        // console.log(currentPosition);
                        return currentPosition;
                    },

                };
            }
        </script>

        <div class="km-vendor-product" x-data="kmData()" x-init="getData()" @map-ready.window="getData()">
        <!-- <div class="km-vendor-product" x-data="kmData()" x-init="getData()"> -->
            <div class="columns is-full">            
                <!-- Left column for logo -->
                <div class="km-logo column is-one-quarter">    
                    <template x-if="data">
                        <div>
                            <figure class="vendorlogo">
                                <img :src="data.logo_url" alt="product img" />
                            </figure>   
                            <p x-text="data.name"> </p>
                            <p x-text="data.phone"> </p>
                            <a x-show ="data.website" class="vendorWebsite" :href="data.website"><p class="vendorMail" x-text="data.website"> </p></a>
                            <a x-show ="data.is_claimable==true" style="width: 100%; margin-bottom: 20px" class="button is-dark" :href="'https://account.kushmapper.com/claim/listing/' + data.id">Claim Listing</a>
                            <!-- <button x-show ="data.is_claimable==true" style="width: 100%; margin-bottom: 20px" class="button is-black">Claim Listing</button> -->
                        </div>
                    </template>
                    <template x-if="hours">                
                        <div class="columns is-full is-mobile km-monday km-hour">
                            <div class="column is-one-quarter">
                                <p>Mon</p>
                            </div>
                            <div class="column is-three-quarter">
                                <!-- <p>9:00 am - 8:00 pm</p> -->
                                <p><span x-text="hours.mon_open"></span> am - <span x-text="hours.mon_close"></span> pm</p>
                            </div>
                        </div>
                        <div class="columns is-full is-mobile km-tuesday km-hour">
                            <div class="column is-one-quarter">
                                <p>Tue</p>
                            </div>
                            <div class="column is-three-quarter">
                                <!-- <p>9:00 am - 8:00 pm</p> -->
                                <p><span x-text="hours.tue_open"></span> am - <span x-text="hours.tue_close"></span> pm</p>
                            </div>
                        </div>
                        <div class="columns is-full is-mobile km-wednsday km-hour">
                            <div class="column is-one-quarter">
                                <p>Wed</p>
                            </div>
                            <div class="column is-three-quarter">
                                <!-- <p>9:00 am - 8:00 pm</p> -->
                                <p><span x-text="hours.wed_open"></span> am - <span x-text="hours.wed_close"></span> pm</p>
                            </div>
                        </div>
                        <div class="columns is-full is-mobile km-thursday km-hour">
                            <div class="column is-one-quarter">
                                <p>Thu</p>
                            </div>
                            <div class="column is-three-quarter">
                                <!-- <p>9:00 am - 8:00 pm</p> -->
                                <p><span x-text="hours.thu_open"></span> am - <span x-text="hours.thu_close"></span> pm</p>
                            </div>
                        </div>
                        <div class="columns is-full is-mobile km-friday km-hour">
                            <div class="column is-one-quarter">
                                <p>Fri</p>
                            </div>
                            <div class="column is-three-quarter">
                                <!-- <p>9:00 am - 8:00 pm</p> -->
                                <p><span x-text="hours.fri_open"></span> am - <span x-text="hours.fri_close"></span> pm</p>
                            </div>
                        </div>
                        <div class="columns is-full is-mobile km-saturday km-hour">
                            <div class="column is-one-quarter">
                                <p>Sat</p>
                            </div>
                            <div class="column is-three-quarter">
                                <!-- <p>9:00 am - 8:00 pm</p> -->
                                <p><span x-text="hours.sat_open"></span> am - <span x-text="hours.sat_close"></span> pm</p>
                            </div>
                        </div>
                        <div class="columns is-full is-mobile km-sunday km-hour">
                            <div class="column is-one-quarter">
                                <p>Sun</p>
                            </div>
                            <div class="column is-three-quarter">
                                <!-- <p>9:00 am - 8:00 pm</p> -->
                                <p><span x-text="hours.sun_open"></span> am - <span x-text="hours.sun_close"></span> pm</p>
                            </div>
                        </div>
                    </template>
                    <form class="box km-request-form" method="post" action="/request-response/" style="width: 100%">
                        <!-- <form class="box km-request-form" method="post" action="/wordpress/request-response/" style="width: 100%"> -->
                        <div class="columns is-multiline"> 
                            <div class="column is-full">
                                <h3 class="title is-5">Product Request Form</h3>
                            </div>
                            <!-- name -->
                            <div class="column is-full">
                            <label class="label">Name</label>
                                <div class="control">
                                    <input class="input" name="author" type="text" placeholder="Name (required)" required>
                                </div>
                            </div>
                            <!-- email -->
                            <div class="column is-full">
                                <label class="label">Email</label>
                                <div class="control">
                                    <input class="input" type="email" name="email" placeholder="Email (required)" required>
                                </div>                                    
                            </div> 
                            <!-- phone -->
                            <div class="column is-full">
                                <label class="label">Phone</label>
                                <div class="control">
                                    <input class="input" type="phone" name="phone" placeholder="phone">
                                </div>                                    
                            </div> 
                            <!-- text comment area -->
                            <div class="column is-full">
                                <textarea class="textarea" name="comment" placeholder="Request Products (required)" rows="5" required></textarea>
                            </div>                                
                            <input class="button is-black is-fullwidth" type="submit" name="submit" value="Send Request">                            
                        </div>
                    </form>
                </div>
                <!-- Right column for product -->
                <div class="column km-all-products">
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
                                @click.prevent="menuTab = 'map'"
                            >
                                <span class="icon"><i class="fas fa-globe-americas fa-fw" aria-hidden="true"></i></span>
                                <span>LOCATION</span>
                            </a>
                            </li>
                            <li :class="{'is-active' : menuTab === 'profile'}">
                            <a href="#km-profile"
                                @click.prevent="menuTab = 'profile'"
                            >
                                <span class="icon"><i class="fas fa-home fa-fw" aria-hidden="true"></i></span>
                                <span>PROFILE</span>
                            </a>
                            </li> 
                            <!-- <li :class="{'is-active' : menuTab === 'photos'}">
                            <a href="#km-photos"
                                @click.prevent="menuTab = 'photos'"
                            >
                                <span class="icon"><i class="fas fa-image fa-fw" aria-hidden="true"></i></span>
                                <span>PHOTOS</span>
                            </a>
                            </li>  
                            <li :class="{'is-active' : menuTab === 'reviews'}">
                            <a href="#km-product-reviews"
                                @click.prevent="menuTab = 'reviews'"
                            >
                                <span class="icon"><i class="fas fa-comments fa-fw" aria-hidden="true"></i></span>
                                <span>REVIEWS</span>
                            </a>
                            </li>-->
                        </ul>
                    </div>
                    <!-- Product infomation  -->
                    <div id="km-product-menu" x-show="menuTab === 'product'">
                        <!-- Filter dropsown lists -->
                        <div class="columns km-filters is-multiline">                                            
                            <div class="column km-filters-column"> 
                                <fieldset class="km-max-thc">
                                    <legend>Max Price</legend>
                                    <div>
                                        <div class="select">
                                            <select x-model="filter.weight" x-on:change="searchProduct()">
                                                <option value="All">All</option>        
                                                <option value="1g">1g</option>     
                                                <option value="1/8oz">1/8oz</option>  
                                                <option value="1/4oz">1/4oz</option>   
                                                <option value="1/2oz">1/2oz</option>              
                                                <option value="1oz">1oz</option>                                   
                                            </select>
                                        </div>
                                        <div class="km-max-thc-currency">
                                            <input class="km-max-thc-input" 
                                            type="text" 
                                            id="thcMax" 
                                            placeholder="price" 
                                            x-model="filter.maxPrice" 
                                            x-on:change="searchProduct()"
                                            x-on:click="UpdateInputType()" />
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                
                            <div class="column km-filters-column">  
                                <div class="km-filters-label-thc" >
                                    <div class="select">                        
                                        <select id="thc" x-model="filter.minThc" x-on:change="searchProduct()">
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
                
                            <div class="column km-filters-column">  
                                <div class="km-filters-label-cbd">
                                    <div class="select">                        
                                        <select id="cbd" x-model="filter.minCbd" x-on:change="searchProduct()">
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
                
                            <div class="column km-filters-column">  
                                <div class="km-filters-label-category"> 
                                    <div class="select">                      
                                        <select name="Category" id= "cat" x-model="filter.category" x-on:change="searchProduct()">
                                                <option value="All" name="all">All</option>
                                                <template x-for="category in categories" :key="category">
                                                    <option :value="category" x-text="category"></option>
                                                </template>                            
                                        </select>     
                                    </div>
                                </div>                          
                            </div>       
                            
                            <div class="column km-filters-column">
                                <button class="button is-black" x-on:click="resetFilter()">Reset</button>
                            </div>
                        </div>  
        
                        <!-- Search button, entyry dropdown list and thc max search -->
                        <div class="km-search">
                            <label class="km-search-items">Show 
                                <div class="select">
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
                            <div class="km-search-items km-search-button-label">
                                <!-- <button class="button is-black km-search-button" x-on:click="SearchFilter()">Search ...</button> -->
                                <button class="button is-black km-search-button" x-on:click="searchProduct()">Search ...</button>
                            </div>
                        </div>
        
                        <!-- Table colums -->
                        <div class="columns km-products-info">
                            <div class="column is-two-thirds">
                                <div class="km-product-pic-title">     
                                </div>
                                <div class="km-product-price-title is-size-5">   
                                    <strong>Product</strong>
                                </div>
                                <div class="km-product-concentrate-title"> 
                                </div>
                            </div>
                            <div class="column is-one-thirds">
                                <div class="km-product-category-title is-size-5">                  
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
                                        <figure class="image">
                                            <a :href="'/product/' + product.slug"><img :src="product.image_url" alt="product img" /></a>
                                        </figure>        
                                    </div>
                                    <div class="km-product-price">                
                                        <strong>
                                            <!-- <a :href="product.url"><p class="is-size-4 km-product-price-name" x-text="product.name" ></p></a> -->
                                            <a :href="'/product/' + product.slug"><p class="is-size-4 km-product-price-name" x-text="product.name" ></p></a>
                                            <p style="text-decoration: underline;" x-show="product.price_gram != null">$<span x-text="product.price_gram"></span><span class="km-small-text">&nbsp;per 1 g</span></p>
                                            <p style="text-decoration: underline;" x-show="product.price_oz_eighth != null">$<span x-text="product.price_oz_eighth"></span> <span class="km-small-text">per 1/8 oz</span></p>
                                            <p style="text-decoration: underline;" x-show="product.price_oz_fourth != null">$<span x-text="product.price_oz_fourth"></span><span class="km-small-text">&nbsp;per 1/4 oz</span></p>
                                            <p style="text-decoration: underline;" x-show="product.price_oz_half != null">$<span x-text="product.price_oz_half"></span><span class="km-small-text">&nbsp;per 1/2 oz</span></p>
                                            <p style="text-decoration: underline;" x-show="product.price_oz != null">$<span x-text="product.price_oz"></span><span class="km-small-text">&nbsp;per 1 oz</span></p>
                                            <template x-if="product.thc_min && product.thc_max">
                                                <p class="km-no-dispaly">THC: <span x-text="product.thc_min"></span>%-<span x-text="product.thc_max"></span>%</p>
                                            </template>
                                            <template x-if="!product.thc_min || !product.thc_max">
                                                <p class="km-no-dispaly">THC: <span>Not Available</span></p>
                                            </template>
                                            <template x-if="product.cbd_min && product.cbd_max">
                                                <p class="km-no-dispaly">CBD: <span x-text="product.cbd_min"></span>%-<span x-text="product.cbd_max"></span>%</p>
                                            </template>
                                            <template x-if="!product.cbd_min || !product.cbd_max">
                                                <p class="km-no-dispaly">CBD: <span>Not Available</span></p>
                                            </template> 
                                        </strong>
                                    </div>
                                    <div class="km-product-concentrate">                  
                                        <strong>
                                            <template x-if="product.thc_min && product.thc_max">
                                                <p>THC: <span x-text="product.thc_min"></span>%-<span x-text="product.thc_max"></span>%</p>
                                            </template>
                                            <template x-if="!product.thc_min || !product.thc_max">
                                                <p>THC: <span>Not Available</span></p>
                                            </template>
                                            <template x-if="product.cbd_min && product.cbd_max">
                                                <p>CBD: <span x-text="product.cbd_min"></span>%-<span x-text="product.cbd_max"></span>%</p>
                                            </template>
                                            <template x-if="!product.cbd_min || !product.cbd_max">
                                                <p>CBD: <span>Not Available</span></p>
                                            </template>
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
                                        <!-- <a class="button is-rounded is-dark" :href="'/wordpress/product/' + product.slug">View</a> -->
                                        <a class="button is-rounded is-dark" :href="'/product/' + product.slug">View</a>
                                    </div>
                                </div>
                            </div>
                            </template>
                        </div>
                        
                        <template x-if="meta">
                            <div style="float: left">
                                <template x-for="link in meta.links">
                                    <a :class="{'button':true, 'is-active':link.active}" :href="link.url" @click.prevent="Pagenation(link.url)" x-html="link.label"></a>
                                </template>
                            </div>
                        </template>
                    </div>
                    <div id="km-location" x-show="menuTab === 'map'" >
                        <!-- <div class="columns">  -->
                            <div id="km-address">             
                                <div class="km-location-store">    
                                    <strong><p class="is-size-6"> Stores:</p> </strong>
                                    <template x-if="store">                                        
                                        <div class="card km-location-card">
                                            <div class="card-content">
                                                <div class="media">
                                                    <div class="media-left">
                                                        <figure class="image is-48x48">
                                                            <img :src="data.logo_url" alt="product img" />                                                        
                                                        </figure>
                                                    </div>
                                                    <div class="media-content km-media-content">
                                                        <p x-text="data.name" class="title is-4"></p>
                                                    </div>
                                                </div>
                                                <template x-for="item in data.stores">
                                                    <div class="content km-store-content">
                                                        <p> <span x-text="item.address1"> </span></p>
                                                        <p x-text="item.address2"> </p>
                                                        <p><span x-text="item.city"></span>&nbsp;<span x-text="item.state"></span> </p>
                                                        <p> <span x-text="item.country"> </span></p>
                                                        <p> <span x-text="item.postal_code">  </span></p>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>                                   
                                    </template>
                                </div>                            
                            
                                <div class="km-location-service">  
                                    <strong><p class="is-size-6"> Delivery Area:</p> </strong>   
                                    <template x-if="serviceArea">
                                        <div class="card km-location-card">
                                            <div class="card-content">
                                                <div class="media">
                                                    <div class="media-left">
                                                        <figure class="image is-48x48">
                                                            <img :src="data.logo_url" alt="product img" />                                                        
                                                        </figure>
                                                    </div>
                                                    <div class="media-content km-media-content">
                                                        <p x-text="data.name" class="title is-4"></p>
                                                    </div>
                                                </div>
                                                <template x-for="item in data.service_areas">
                                                    <div class="content km-store-content">
                                                        <p> <span x-text="item.city"></span>&nbsp;<span x-text="item.state"> </span></p>
                                                        <p x-text="item.country"> </p> 
                                                    </div>
                                                </template>
                                            </div>
                                        </div> 
                                    </template>                     
                                </div>    
                                            
                            </div>        

                            <div id="km-map-container"> 
                                <div id="km-map"> 

                                </div>
                                <div class="km-map-direction"> 
                                    <input id="Coordinate" class="input is-link" type="text" placeholder="Enter your location">
                                    <button class="button is-primary fas fa-location-arrow" x-on:click="getCurrentLocation()" title="use my location"></button>
                                    <button class="button is-dark" x-on:click="GetVendorDirection()">Get Directions</button>
                                </div>
                                <div class="km-map-driving"> 
                                    <div class="select" x-model="googleMap.transport">
                                        <select>
                                            <option value="DRIVING">Driving</option>
                                            <option value="WALKING">Walking</option>
                                            <option value="BICYCLING">Bicycling</option>
                                            <option value="TRANSIT">Public Transport</option>
                                        </select>
                                    </div>
                                    <div class="select" x-model="googleMap.transportUnit">
                                        <select>
                                            <option value="kilometers">Kilometers</option>
                                            <option value="miles">Miles</option>
                                        </select>
                                    </div>
                                </div>
                                
                            </div>
                            
                        <!-- </div> -->
                    </div>
                    <div id="km-profile" style="width: 70%;" x-show="menuTab === 'profile'">
                        <strong><p style="font-weight: 400; padding-left: 30px;" x-text="data.description"></p> </strong>
                    </div>
                    <!-- <div id="km-photos" x-show="menuTab === 'photos'">
                        <strong><p> this is photos </p> </strong>
                    </div> -->
                    <div id="km-product-reviews" x-show="menuTab === 'reviews'">
                        <!-- <form method="post" action="http://127.0.0.1/wordpress/test-page/" class="box" style="width: 100%"> -->
                        <form method="post" action="https://kushmapper.com/wp-comments-post.php" class="box" style="width: 100%">
                            <div class="columns is-multiline">
                                <div class="column is-full has-background-info km-reviews-general" style="color: white;">
                                    <div><i class="fas fa-info-circle"></i> Your email address will not be published.</div>                        
                                </div>   
                                <!-- text comment area -->
                                <div class="column is-full">
                                    <textarea class="textarea" name="comment" placeholder="10 lines of textarea" rows="10"></textarea>
                                </div>
                                <!-- rating buttons -->
                                <div class="column is-full">
                                    <div class="field">
                                        <div class="control">
                                        <div class="container">
                                            <div class="star-widget">
                                                <div class="radios">
                                                    <input type="radio" name="rate" id="rate-5" value="5">
                                                    <label for="rate-5" class="fas fa-star"></label>
                                                    <input type="radio" name="rate" id="rate-4" value="4">
                                                    <label for="rate-4" class="fas fa-star"></label>
                                                    <input type="radio" name="rate" id="rate-3" value="3">
                                                    <label for="rate-3" class="fas fa-star"></label>
                                                    <input type="radio" name="rate" id="rate-2" value="2">
                                                    <label for="rate-2" class="fas fa-star"></label>
                                                    <input type="radio" name="rate" id="rate-1" value="1">
                                                    <label for="rate-1" class="fas fa-star"></label>
                                                    <header></header>
                                                </div>
                                                
                                            </div>
                                            
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Author name -->
                                <div class="column is-full">
                                    <div class="field">
                                        <div class="control">
                                            <input class="input" name="author" type="text" placeholder="Name (required)" required>
                                        </div>
                                    </div>
                                </div>
                                <!-- email -->
                                <div class="column is-full">
                                    <div class="field">
                                        <div class="control">
                                            <input class="input" type="email" name="email" placeholder="Email (required)" required>
                                        </div>
                                    </div>
                                </div>
                                <!-- website -->
                                <div class="column is-full">
                                    <div class="field">
                                        <div class="control">
                                            <input class="input" name="url" type="url" placeholder="Website" required>
                                        </div>
                                    </div>
                                </div>
                                <!-- google reCAPCHA -->
                                <div class="column is-full">
                                    <div class="field">
                                        <div class="control">
                                            <!-- <input class="input" type="url" placeholder="Website"> -->
                                            <div class="g-recaptcha" x-bind:data-sitekey=site_key></div>
                                        </div>
                                    </div>
                                </div>
                                <!-- check box -->
                                <div class="column is-full">
                                    <label class="checkbox">
                                        <input type="checkbox" name="check_box">
                                        Save my name, email, and website in this browser for the next time I comment.
                                    </label>
                                </div>  
                                <input class="button is-black is-fullwidth" type="submit" name="submit" value="POST REVIEW">                            
                                <!-- <button class="button is-black is-fullwidth">POST REVIEW</button>                      -->
                            </div>
                        </form>
                    </div>
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
    wp_enqueue_style('emoji', 'https://emoji-css.afeld.me/emoji.css');
}//end local_styles()


add_action('wp_enqueue_scripts', 'local_styles');
