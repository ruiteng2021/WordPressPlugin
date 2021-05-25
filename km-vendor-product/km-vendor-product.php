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
    // $site_key = "6LeK79gaAAAAAMJkKOHduzuO8EPhNHHUouFfButk";
    // $secret_key = "6LeK79gaAAAAAPU56ZijQTG_g4zzT2XvWmaAIhGK";
    $site_key = "6LfKjiQaAAAAAEB4l9m5d6bbzbuxRJX4i2WFPOFA"; // slyfox
    $secret_key = "6LfKjiQaAAAAAMeizHO-a6JaAxAI-LUAJgFfHgj7"; //SlyFox
    // $site_key = "6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"; // google development key
    // $secret_key = "6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe"; // google development key

    ob_start();

    ?>
        <!-- <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCAEc6aw19DrUE7sN0CoE-VhM20ighnm7Y"type="text/javascript"></script> -->
        <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB44vENDVAXY11oPRg4tSuHH2EEP9xhI1A"type="text/javascript"></script>
        <script async defer src="https://www.google.com/recaptcha/api.js"type="text/javascript"></script>
        <script>          
            function kmData() {
                return {
                    startUrl: '<?php echo $startUrl; ?>',
                    currentUrl: '<?php echo get_permalink(); ?>',
                    name: false,
                    stores: false,
                    products: [],
                    serviceArea: false,
                    data: false,
                    categories: [],

                    // THC max filter begin //
                    thcMaxWeight: 'All',
                    thcMaxPrice: '',
                    // THC max filter end //

                    // filter parmeters begin//
                    selectedTHC: 'All', 
                    selectedCBD: 'All', 
                    selectedCat: 'All',
                    // filter parameters end //

                    pageSize: '5',
                    meta: false,
                    menuTab: 'product',
                    urlSearchGlobal: false,

                    // Google map begin//
                    map: false,
                    transport: "driving",
                    transportUnit: "kilometers",
                    directionsService: false,
                    directionsRenderer: false,
                    infoWindow: false,
                    // Google map end //

                    // google reCaptcha begin//
                    site_key: '<?php echo $site_key; ?>',
                    // google reCaptcha end //

                    async getData(url = this.startUrl) 
                    {
                        var self = this;
                        await axios.get(url)
                            .then(function(response) {
                                // console.log(response);
                                self.data = response.data.data;
                                self.name = response.data.data["name"];
                                self.stores = response.data.data["stores"];
                                self.serviceArea = response.data.data["service_areas"];      
                                self.products = response.data.data["products"];                                
                                // remove duplicate categories
                                for ( let i in self.products) {
                                    self.categories[i] =  self.products[i].category;
                                }
                                self.categories = [...new Set(self.categories)];
                                // console.log(self.categories.toString());
                                urlPage = url.replace("?include=", "/") + "?page_size=" + self.pageSize +"&page=1";                                
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

                        self.directionsService = new google.maps.DirectionsService();
                        self.directionsRenderer = new google.maps.DirectionsRenderer();
                        let mapOptions = {
                            center: new google.maps.LatLng(42.976348, -81.2514795),
                            zoom: 10,
                            mapTypeId: google.maps.MapTypeId.ROADMAP
                        }
                        self.map = new google.maps.Map(document.getElementById("km-map"), mapOptions);
                        self.directionsRenderer.setMap(self.map);     
                        self.infoWindow = new google.maps.InfoWindow();                       
                    },

                    async SearchFilter(url = this.startUrl)
                    {                        
                        var self = this;
                        // debugger;
                        urlSearch = url.replace("?include=", "/");
                        // urlSearch = "https://api.kushmapper.com/v1/vendors/1/products";
                        // http://api.kushmapper.com/v1/vendors/1/products       

                        weightSelect = "";
                        sortPrice = "";
                        if ( self.thcMaxWeight == "1g"){
                            weightSelect = "filter[minimum_price_gram]=0&filter[maximum_price_gram]=";
                            sortPrice = "sort=-price_gram&filter[minimum_price_gram]=0";
                        }

                        if ( self.thcMaxWeight == "1/8oz"){
                            weightSelect = "filter[minimum_price_oz_eighth]=0&filter[maximum_price_oz_eighth]=";
                            sortPrice = "sort=-price_oz_eighth&filter[minimum_price_oz_eighth]=0";
                        }

                        if ( self.thcMaxWeight == "1/4oz"){
                            weightSelect = "filter[minimum_price_oz_fourth=0]&filter[maximum_price_oz_fourth]=";
                            sortPrice = "sort=-price_oz_fourth&filter[minimum_price_oz_fourth]=0";
                        }

                        if ( self.thcMaxWeight == "1/2oz"){
                            weightSelect = "filter[minimum_price_oz_half=0]&filter[maximum_price_oz_half]=";
                            sortPrice = "sort=-price_oz_half&filter[minimum_price_oz_half]=0";
                        }

                        if ( self.thcMaxWeight == "1oz"){
                            weightSelect = "filter[minimum_price_oz=0]&filter[maximum_price_oz]=";
                            sortPrice = "sort=-price_oz&filter[minimum_price_oz]=0";
                        }

                        // 0 0 0 0 0
                        if( (self.thcMaxWeight == "All") &&
                            (self.thcMaxPrice == "") &&
                            (self.selectedTHC == "All") &&
                            (self.selectedCBD == "All") &&
                            (self.selectedCat == "All") )                   
                        {
                            urlSearch = urlSearch + "?page_size=" + self.pageSize;
                        }

                        // 0 0 0 0 1
                        if( (self.thcMaxWeight == "All") &&
                            (self.thcMaxPrice == "") &&
                            (self.selectedTHC == "All") &&
                            (self.selectedCBD == "All") &&
                            (self.selectedCat != "All") )                   
                        {
                            urlSearch = urlSearch + 
                                "?filter[category]=" + self.selectedCat + 
                                "&page_size=" + self.pageSize;
                        }

                        // 0 0 0 1 0
                        if( (self.thcMaxWeight == "All") &&
                            (self.thcMaxPrice == "") &&
                            (self.selectedTHC == "All") &&
                            (self.selectedCBD != "All") &&
                            (self.selectedCat == "All") )                   
                        {
                            cbd = self.selectedCBD.split("-");
                            urlSearch = urlSearch + 
                                "?filter[minimum_cbd]=" + cbd[0] + "&filter[maximum_cbd]=" + cbd[1] +
                                "&page_size=" + self.pageSize;;
                        }

                        // 0 0 0 1 1
                        if( (self.thcMaxWeight == "All") &&
                            (self.thcMaxPrice == "") &&
                            (self.selectedTHC == "All") &&
                            (self.selectedCBD != "All") &&
                            (self.selectedCat != "All") )                   
                        {
                            cbd = self.selectedCBD.split("-");
                            urlSearch = urlSearch + 
                                "?filter[minimum_cbd]=" + cbd[0] + "&filter[maximum_cbd]=" + cbd[1] +
                                "&filter[category]=" + self.selectedCat + 
                                "&page_size=" + self.pageSize;
                        }

                        // 0 0 1 0 0
                        if( (self.thcMaxWeight == "All") &&
                            (self.thcMaxPrice == "") &&
                            (self.selectedTHC != "All") &&
                            (self.selectedCBD == "All") &&
                            (self.selectedCat == "All") )                   
                        {
                            thc = self.selectedTHC.split("-");
                            urlSearch = urlSearch + 
                                "?filter[minimum_thc]=" + thc[0] + "&filter[maximum_thc]=" + thc[1] +
                                "&page_size=" + self.pageSize;
                        }

                        // 0 0 1 0 1
                        if( (self.thcMaxWeight == "All") &&
                            (self.thcMaxPrice == "") &&
                            (self.selectedTHC != "All") &&
                            (self.selectedCBD == "All") &&
                            (self.selectedCat != "All") )                   
                        {
                            thc = self.selectedTHC.split("-");
                            urlSearch = urlSearch + 
                                "?filter[minimum_thc]=" + thc[0] + "&filter[maximum_thc]=" + thc[1] +
                                "&filter[category]=" + self.selectedCat +
                                "&page_size=" + self.pageSize;
                        }

                        // 0 0 1 1 0
                        if( (self.thcMaxWeight == "All") &&
                            (self.thcMaxPrice == "") &&
                            (self.selectedTHC != "All") &&
                            (self.selectedCBD != "All") &&
                            (self.selectedCat == "All") )                   
                        {
                            thc = self.selectedTHC.split("-");
                            cbd = self.selectedCBD.split("-");
                            urlSearch = urlSearch + 
                                "?filter[minimum_thc]=" + thc[0] + "&filter[maximum_thc]=" + thc[1] +
                                "&filter[minimum_cbd]=" + cbd[0] + "&filter[maximum_cbd]=" + cbd[1] +
                                "&page_size=" + self.pageSize;
                        }

                        // 0 0 1 1 1
                        if( (self.thcMaxWeight == "All") &&
                            (self.thcMaxPrice == "") &&
                            (self.selectedTHC != "All") &&
                            (self.selectedCBD != "All") &&
                            (self.selectedCat != "All") )                   
                        {
                            thc = self.selectedTHC.split("-");
                            cbd = self.selectedCBD.split("-");
                            urlSearch = urlSearch + 
                                "?filter[minimum_thc]=" + thc[0] + "&filter[maximum_thc]=" + thc[1] +
                                "&filter[minimum_cbd]=" + cbd[0] + "&filter[maximum_cbd]=" + cbd[1] +
                                "&filter[category]=" + self.selectedCat +
                                "&page_size=" + self.pageSize;
                        }

                        // 0 1 0 0 0
                        if( (self.thcMaxWeight == "All") &&
                            (self.thcMaxPrice != "") &&
                            (self.selectedTHC == "All") &&
                            (self.selectedCBD == "All") &&
                            (self.selectedCat == "All") )                   
                        {
                            console.log("0 1 0 0 0");
                            urlSearch = urlSearch + 
                                "?" + "filter[maximum_price_any]=" + self.thcMaxPrice +
                                "&page_size=" + self.pageSize;
                        }

                        // 0 1 0 0 1
                        if( (self.thcMaxWeight == "All") &&
                            (self.thcMaxPrice != "") &&
                            (self.selectedTHC == "All") &&
                            (self.selectedCBD == "All") &&
                            (self.selectedCat != "All") )                   
                        {
                            price = self.thcMaxPrice.split("-");
                            urlSearch = urlSearch + 
                                "?" + "filter[maximum_price_any]=" + self.thcMaxPrice +
                                "&filter[category]=" + self.selectedCat + 
                                "&page_size=" + self.pageSize;
                        }

                        // 0 1 0 1 0
                        if( (self.thcMaxWeight == "All") &&
                            (self.thcMaxPrice != "") &&
                            (self.selectedTHC == "All") &&
                            (self.selectedCBD != "All") &&
                            (self.selectedCat == "All") )                   
                        {
                            cbd = self.selectedCBD.split("-");
                            urlSearch = urlSearch + 
                                "?" + "filter[maximum_price_any]=" + self.thcMaxPrice +
                                "&filter[minimum_cbd]=" + cbd[0] + "&filter[maximum_cbd]=" + cbd[1] +
                                "&page_size=" + self.pageSize;
                        }

                        // 0 1 0 1 1
                        if( (self.thcMaxWeight == "All") &&
                            (self.thcMaxPrice != "") &&
                            (self.selectedTHC == "All") &&
                            (self.selectedCBD != "All") &&
                            (self.selectedCat != "All") )                   
                        {
                            cbd = self.selectedCBD.split("-");
                            urlSearch = urlSearch + 
                                "?" + "filter[maximum_price_any]=" + self.thcMaxPrice +
                                "&filter[minimum_cbd]=" + cbd[0] + "&filter[maximum_cbd]=" + cbd[1] +
                                "&filter[category]=" + self.selectedCat +
                                "&page_size=" + self.pageSize;
                        }

                        // 0 1 1 0 0
                        if( (self.thcMaxWeight == "All") &&
                            (self.thcMaxPrice != "") &&
                            (self.selectedTHC != "All") &&
                            (self.selectedCBD == "All") &&
                            (self.selectedCat == "All") )                   
                        {
                            thc = self.selectedTHC.split("-");
                            urlSearch = urlSearch + 
                                "?" + "filter[maximum_price_any]=" + self.thcMaxPrice +
                                "&filter[minimum_thc]=" + thc[0] + "&filter[maximum_thc]=" + thc[1] +
                                "&page_size=" + self.pageSize;
                        }

                        // 0 1 1 0 1
                        if( (self.thcMaxWeight == "All") &&
                            (self.thcMaxPrice != "") &&
                            (self.selectedTHC != "All") &&
                            (self.selectedCBD == "All") &&
                            (self.selectedCat != "All") )                   
                        {
                            thc = self.selectedTHC.split("-");
                            urlSearch = urlSearch + 
                                "?" + "filter[maximum_price_any]=" + self.thcMaxPrice +
                                "&filter[minimum_thc]=" + thc[0] + "&filter[maximum_thc]=" + thc[1] +
                                "&filter[category]=" + self.selectedCat +
                                "&page_size=" + self.pageSize;
                        }

                        // 0 1 1 1 0
                        if( (self.thcMaxWeight == "All") &&
                            (self.thcMaxPrice != "") &&
                            (self.selectedTHC != "All") &&
                            (self.selectedCBD != "All") &&
                            (self.selectedCat == "All") )                   
                        {
                            thc = self.selectedTHC.split("-");
                            cbd = self.selectedCBD.split("-");
                            urlSearch = urlSearch + 
                                "?" + "filter[maximum_price_any]=" + self.thcMaxPrice +
                                "&filter[minimum_thc]=" + thc[0] + "&filter[maximum_thc]=" + thc[1] +
                                "&filter[minimum_cbd]=" + cbd[0] + "&filter[maximum_cbd]=" + cbd[1] +
                                "&page_size=" + self.pageSize;
                        }

                        // 0 1 1 1 1
                        if( (self.thcMaxWeight == "All") &&
                            (self.thcMaxPrice != "") &&
                            (self.selectedTHC != "All") &&
                            (self.selectedCBD != "All") &&
                            (self.selectedCat != "All") )                   
                        {
                            thc = self.selectedTHC.split("-");
                            cbd = self.selectedCBD.split("-");
                            urlSearch = urlSearch + 
                                "?" + "filter[maximum_price_any]=" + self.thcMaxPrice +
                                "&filter[minimum_thc]=" + thc[0] + "&filter[maximum_thc]=" + thc[1] +
                                "&filter[minimum_cbd]=" + cbd[0] + "&filter[maximum_cbd]=" + cbd[1] +
                                "&filter[category]=" + self.selectedCat +
                                "&page_size=" + self.pageSize;
                        }

                        // 1 0 0 0 0
                        if( (self.thcMaxWeight != "All") &&
                            (self.thcMaxPrice == "") &&
                            (self.selectedTHC == "All") &&
                            (self.selectedCBD == "All") &&
                            (self.selectedCat == "All") )                   
                        {
                            urlSearch = urlSearch + 
                                "?" + sortPrice +
                                "&page_size=" + self.pageSize;
                        }

                        // 1 0 0 0 1 
                        if( (self.thcMaxWeight != "All") &&
                            (self.thcMaxPrice == "") &&
                            (self.selectedTHC == "All") &&
                            (self.selectedCBD == "All") &&
                            (self.selectedCat != "All") )                   
                        {
                            urlSearch = urlSearch + 
                                "?filter[category]=" + self.selectedCat + 
                                "&" + sortPrice +
                                "&page_size=" + self.pageSize;
                        }

                        // 1 0 0 1 0
                        if( (self.thcMaxWeight != "All") &&
                            (self.thcMaxPrice == "") &&
                            (self.selectedTHC == "All") &&
                            (self.selectedCBD != "All") &&
                            (self.selectedCat == "All") )                   
                        {
                            cbd = self.selectedCBD.split("-");
                            urlSearch = urlSearch + 
                                "?filter[minimum_cbd]=" + cbd[0] + "&filter[maximum_cbd]=" + cbd[1] +
                                "&" + sortPrice +
                                "&page_size=" + self.pageSize;;
                        }

                        // 1 0 0 1 1
                        if( (self.thcMaxWeight != "All") &&
                            (self.thcMaxPrice == "") &&
                            (self.selectedTHC == "All") &&
                            (self.selectedCBD != "All") &&
                            (self.selectedCat != "All") )                   
                        {
                            cbd = self.selectedCBD.split("-");
                            urlSearch = urlSearch + 
                                "?filter[minimum_cbd]=" + cbd[0] + "&filter[maximum_cbd]=" + cbd[1] +
                                "&filter[category]=" + self.selectedCat + 
                                "&" + sortPrice +
                                "&page_size=" + self.pageSize;
                        }

                        // 1 0 1 0 0  ///////////////////
                        if( (self.thcMaxWeight != "All") &&
                            (self.thcMaxPrice == "") &&
                            (self.selectedTHC != "All") &&
                            (self.selectedCBD == "All") &&
                            (self.selectedCat == "All") )                   
                        {
                            thc = self.selectedTHC.split("-");
                            urlSearch = urlSearch + 
                                "?filter[minimum_thc]=" + thc[0] + "&filter[maximum_thc]=" + thc[1] +
                                "&" + sortPrice +
                                "&page_size=" + self.pageSize;
                        }

                        // 1 0 1 0 1
                        if( (self.thcMaxWeight != "All") &&
                            (self.thcMaxPrice == "") &&
                            (self.selectedTHC != "All") &&
                            (self.selectedCBD == "All") &&
                            (self.selectedCat != "All") )                   
                        {
                            thc = self.selectedTHC.split("-");
                            urlSearch = urlSearch + 
                                "?filter[minimum_thc]=" + thc[0] + "&filter[maximum_thc]=" + thc[1] +
                                "&filter[category]=" + self.selectedCat +
                                "&" + sortPrice +
                                "&page_size=" + self.pageSize;
                        }

                        // 1 0 1 1 0
                        if( (self.thcMaxWeight != "All") &&
                            (self.thcMaxPrice == "") &&
                            (self.selectedTHC != "All") &&
                            (self.selectedCBD != "All") &&
                            (self.selectedCat == "All") )                   
                        {
                            thc = self.selectedTHC.split("-");
                            cbd = self.selectedCBD.split("-");
                            urlSearch = urlSearch + 
                                "?filter[minimum_thc]=" + thc[0] + "&filter[maximum_thc]=" + thc[1] +
                                "&filter[minimum_cbd]=" + cbd[0] + "&filter[maximum_cbd]=" + cbd[1] +
                                "&" + sortPrice +
                                "&page_size=" + self.pageSize;
                        }

                        // 1 0 1 1 1
                        if( (self.thcMaxWeight != "All") &&
                            (self.thcMaxPrice == "") &&
                            (self.selectedTHC != "All") &&
                            (self.selectedCBD != "All") &&
                            (self.selectedCat != "All") )                   
                        {
                            thc = self.selectedTHC.split("-");
                            cbd = self.selectedCBD.split("-");
                            urlSearch = urlSearch + 
                                "?filter[minimum_thc]=" + thc[0] + "&filter[maximum_thc]=" + thc[1] +
                                "&filter[minimum_cbd]=" + cbd[0] + "&filter[maximum_cbd]=" + cbd[1] +
                                "&filter[category]=" + self.selectedCat +
                                "&" + sortPrice +
                                "&page_size=" + self.pageSize;
                        }

                        // 1 1 0 0 0
                        if( (self.thcMaxWeight != "All") &&
                            (self.thcMaxPrice != "") &&
                            (self.selectedTHC == "All") &&
                            (self.selectedCBD == "All") &&
                            (self.selectedCat == "All") )                   
                        {
                            console.log("1 0 0 0");
                            urlSearch = urlSearch + 
                                "?" + weightSelect + self.thcMaxPrice +
                                "&" + sortPrice +
                                "&page_size=" + self.pageSize;
                        }

                        // 1 1 0 0 1
                        if( (self.thcMaxWeight != "All") &&
                            (self.thcMaxPrice != "") &&
                            (self.selectedTHC == "All") &&
                            (self.selectedCBD == "All") &&
                            (self.selectedCat != "All") )                   
                        {
                            urlSearch = urlSearch + 
                                "?" + weightSelect + self.thcMaxPrice +
                                "&filter[category]=" + self.selectedCat + 
                                "&" + sortPrice +
                                "&page_size=" + self.pageSize;
                        }

                        // 1 1 0 1 0
                        if( (self.thcMaxWeight != "All") &&
                            (self.thcMaxPrice != "") &&
                            (self.selectedTHC == "All") &&
                            (self.selectedCBD != "All") &&
                            (self.selectedCat == "All") )                   
                        {
                            cbd = self.selectedCBD.split("-");
                            urlSearch = urlSearch + 
                                "?" + weightSelect + self.thcMaxPrice +
                                "&filter[minimum_cbd]=" + cbd[0] + "&filter[maximum_cbd]=" + cbd[1] +
                                "&" + sortPrice +
                                "&page_size=" + self.pageSize;
                        }

                        // 1 1 0 1 1
                        if( (self.thcMaxWeight != "All") &&
                            (self.thcMaxPrice != "") &&
                            (self.selectedTHC == "All") &&
                            (self.selectedCBD != "All") &&
                            (self.selectedCat != "All") )                   
                        {
                            cbd = self.selectedCBD.split("-");
                            urlSearch = urlSearch + 
                                "?" + weightSelect + self.thcMaxPrice +
                                "&filter[minimum_cbd]=" + cbd[0] + "&filter[maximum_cbd]=" + cbd[1] +
                                "&filter[category]=" + self.selectedCat +
                                "&" + sortPrice +
                                "&page_size=" + self.pageSize;
                        }

                        // 1 1 1 0 0
                        if( (self.thcMaxWeight != "All") &&
                            (self.thcMaxPrice != "") &&
                            (self.selectedTHC != "All") &&
                            (self.selectedCBD == "All") &&
                            (self.selectedCat == "All") )                   
                        {
                            thc = self.selectedTHC.split("-");
                            urlSearch = urlSearch + 
                                "?" + weightSelect + self.thcMaxPrice +
                                "&filter[minimum_thc]=" + thc[0] + "&filter[maximum_thc]=" + thc[1] +
                                "&" + sortPrice +
                                "&page_size=" + self.pageSize;
                        }

                        // 1 1 1 0 1
                        if( (self.thcMaxWeight != "All") &&
                            (self.thcMaxPrice != "") &&
                            (self.selectedTHC != "All") &&
                            (self.selectedCBD == "All") &&
                            (self.selectedCat != "All") )                   
                        {
                            thc = self.selectedTHC.split("-");
                            urlSearch = urlSearch + 
                                "?" + weightSelect + self.thcMaxPrice +
                                "&filter[minimum_thc]=" + thc[0] + "&filter[maximum_thc]=" + thc[1] +
                                "&filter[category]=" + self.selectedCat +
                                "&" + sortPrice +
                                "&page_size=" + self.pageSize;
                        }

                        // 1 1 1 1 0
                        if( (self.thcMaxWeight != "All") &&
                            (self.thcMaxPrice != "") &&
                            (self.selectedTHC != "All") &&
                            (self.selectedCBD != "All") &&
                            (self.selectedCat == "All") )                   
                        {
                            thc = self.selectedTHC.split("-");
                            cbd = self.selectedCBD.split("-");
                            urlSearch = urlSearch + 
                                "?" + weightSelect + self.thcMaxPrice +
                                "&filter[minimum_thc]=" + thc[0] + "&filter[maximum_thc]=" + thc[1] +
                                "&filter[minimum_cbd]=" + cbd[0] + "&filter[maximum_cbd]=" + cbd[1] +
                                "&" + sortPrice +
                                "&page_size=" + self.pageSize;
                        }

                        // 1 1 1 1 1
                        if( (self.thcMaxWeight != "All") &&
                            (self.thcMaxPrice != "") &&
                            (self.selectedTHC != "All") &&
                            (self.selectedCBD != "All") &&
                            (self.selectedCat != "All") )                   
                        {
                            thc = self.selectedTHC.split("-");
                            cbd = self.selectedCBD.split("-");
                            urlSearch = urlSearch + 
                                "?" + weightSelect + self.thcMaxPrice +
                                "&filter[minimum_thc]=" + thc[0] + "&filter[maximum_thc]=" + thc[1] +
                                "&filter[minimum_cbd]=" + cbd[0] + "&filter[maximum_cbd]=" + cbd[1] +
                                "&filter[category]=" + self.selectedCat +
                                "&" + sortPrice +
                                "&page_size=" + self.pageSize;
                        }

                        self.urlSearchGlobal = urlSearch;
                        console.log(urlSearch);
                        await axios.get(urlSearch)
                            .then(function(response) {      
                                self.products = response.data.data;  
                                self.meta = response.data.meta;
                            })
                            .catch(function(error) {
                                console.log(error);
                            })         
                    },

                    async UpdatePages(url = this.startUrl)
                    {  
                        var self = this;
                        // debugger;
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
                    
                    UpdateInputType()
                    {
                        jQuery(".km-max-thc-input").attr('type', 'number'); 
                        jQuery(".km-max-thc-input").attr('min', '0');                    
                    },

                    GetCurrentCoordinate()
                    {
                        var self = this;
                        console.log("XXXXX in map GetCurrentCoordinate XXXXX");
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(
                                (position) => {
                                    const pos = {
                                        lat: position.coords.latitude,
                                        lng: position.coords.longitude,
                                    };
                                    self.infoWindow.setPosition(pos);
                                    // self.infoWindow.setContent("Location found.");
                                    // self.infoWindow.open(self.map);
                                    console.log(pos);
                                    console.log("AAAA" + pos.lat + "," + pos.lng + "AAAA");
                                    // pos.lat = 43.00756;
                                    // pos.lng = -81.21131;
                                    coordinate = pos.lat + ", " + pos.lng;
                                    document.getElementById("Coordinate").value = coordinate; 
                                    self.map.setCenter(pos);

                                    var marker = new google.maps.Marker({
                                        position: pos,
                                        // title:"Hello World!"
                                    });

                                    // To add the marker to the map, call setMap();
                                    marker.setMap(self.map);
                                },
                                () => {
                                    HandleLocationError(true, self.infoWindow, self.map.getCenter());
                                }
                            );
                        } 
                        else 
                        {
                            // Browser doesn't support Geolocation
                            HandleLocationError(false, self.infoWindow, self.map.getCenter());
                            console.log("XXXXX Error in GetVendorDirection XXXXX");
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
                        infoWindow.open(self.map);
                    },

                    GetVendorDirection()
                    {
                        var self = this;
                        console.log("XXXXX in map GetVendorDirection XXXXX");
                        coord = document.getElementById('Coordinate').value;
                        coord = coord.split(",");
                        console.log("XXXX " + coord[0] + "," + coord[1] + " XXXX");
                        start = new google.maps.LatLng(coord[0], coord[1]);
                        console.log(start);
                        var request = {
                            origin: start,
                            destination: "los angeles, ca",
                            travelMode: 'DRIVING',
                            unitSystem: google.maps.UnitSystem.METRIC,
                            // unitSystem: google.maps.UnitSystem.IMPERIAL,
                        };
                        self.directionsService.route(request, function(result, status) {
                            if (status == 'OK') {
                            self.directionsRenderer.setDirections(result);
                            }
                        });
                    },

                    ResizeGoogleRecaptcha() 
                    {
                    },
                   
                };
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
                        <li :class="{'is-active' : menuTab === 'photos'}">
                        <a href="#km-photos"
                            @click.prevent="menuTab = 'photos'"
                        >
                            <span class="icon"><i class="fas fa-image fa-fw" aria-hidden="true"></i></span>
                            <span>PHOTOS</span>
                        </a>
                        </li> 
                        <li :class="{'is-active' : menuTab === 'reviews'}">
                        <a href="#km-product-reviews"
                            @click.prevent="menuTab = 'reviews'; ResizeGoogleRecaptcha()"
                        >
                            <span class="icon"><i class="fas fa-comments fa-fw" aria-hidden="true"></i></span>
                            <span>REVIEWS</span>
                        </a>
                        </li>
                    </ul>
                </div>
                <!-- Product infomation  -->
                <div id="km-product-menu" x-show="menuTab === 'product'">
                    <!-- Filter dropsown lists -->
                    <div class="columns km-filters">                                            
                        <div class="column km-filters-column"> 
                            <fieldset class="km-max-thc">
                                <legend>Max Price</legend>
                                <div>
                                    <div class="select">
                                        <select x-model="thcMaxWeight">
                                            <option value="All">All</option>        
                                            <option value="1g">1g</option>     
                                            <option value="1/8oz">1/8oz</option>  
                                            <option value="1/4oz">1/4oz</option>   
                                            <option value="1/2oz">1/2oz</option>              
                                            <option value="1oz">1oz</option>                                   
                                        </select>
                                    </div>
                                    <div class="km-max-thc-currency">
                                        <input class="km-max-thc-input" type="text" id="thcMax" placeholder="price" name="thc max" x-model="thcMaxPrice" x-on:change="UpdateInputType()"/>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
            
                        <div class="column km-filters-column">  
                            <div class="km-filters-label-thc" >
                                <div class="select">                        
                                    <select id="thc" x-model="selectedTHC">
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
                                    <select id="cbd" x-model="selectedCBD">
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
                                    <select name="Category" id= "cat" x-model="selectedCat">
                                            <option value="All" name="all">All</option>
                                            <template x-for="category in categories" :key="category">
                                                <option :value="category" x-text="category"></option>
                                            </template>                            
                                    </select>     
                                </div>
                            </div>                          
                        </div>                      
                    </div>  
    
                    <!-- Search button, entyry dropdown list and thc max search -->
                    <div class="km-search">
                        <label style="min-width: 200px" class="km-search-items"> Show 
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
                        <div class="km-search-items km-search-button-label">
                            <button class="button is-black km-search-button" x-on:click="SearchFilter()">Search ...</button>
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
                                        <img :src="product.image_url" alt="product img" />
                                    </figure>        
                                </div>
                                <div class="km-product-price">                
                                    <strong>
                                        <a :href="product.url"><p class="is-size-4" x-text="product.name" ></p></a>
                                        <p style="text-decoration: underline;" x-show="product.price_gram != null"><span x-text="product.price_gram"></span><span class="km-small-text">&nbsp;per 1 g</span></p>
                                        <p style="text-decoration: underline;" x-show="product.price_oz_eighth != null"><span x-text="product.price_oz_eighth"></span> <span class="km-small-text">per 1/8 oz</span></p>
                                        <p style="text-decoration: underline;" x-show="product.price_oz_fourth != null"><span x-text="product.price_oz_fourth"></span><span class="km-small-text">&nbsp;per 1/4 oz</span></p>
                                        <p style="text-decoration: underline;" x-show="product.price_oz_half != null"><span x-text="product.price_oz_half"></span><span class="km-small-text">&nbsp;per 1/2 oz</span></p>
                                        <p style="text-decoration: underline;" x-show="product.price_oz != null"><span x-text="product.price_oz"></span><span class="km-small-text">&nbsp;per 1 oz</span></p>
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
                                    <!-- <button class="button is-rounded is-dark">View</button> -->
                                    <a class="button is-rounded is-dark" :href="currentUrl + '/view-product/?vendor=' + product.vendor_id + '&id=' + product.id">View</a>
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
                            <template x-if="stores">
                                <div class="km-location-store">    
                                    <strong><p class="is-size-6"> Store:</p> </strong>
                                    <p x-text="stores[0].address1"> </p>
                                    <p x-text="stores[0].address2"> </p>
                                    <p><span x-text="stores[0].city"></span>&nbsp;<span x-text="stores[0].state"></span> </p>
                                    <p x-text="stores[0].country"> </p>
                                    <p x-text="stores[0].postal_code"> </p>
                                </div>
                            </template>
                            <template x-if="serviceArea">
                                <div class="km-location-service">  
                                    <strong><p class="is-size-6"> Service Area:</p> </strong>   
                                    <p> <span x-text="serviceArea[0].city"></span>&nbsp;<span x-text="serviceArea[0].state"> </span></p>
                                    <p x-text="serviceArea[0].country"> </p>
                                    <!-- <p x-text="serviceArea[0].details"> </p> -->
                                </div>
                            </template>
                        </div>

                        <div id="km-map-container"> 
                            <div id="km-map"> 

                            </div>
                            <div class="km-map-direction"> 
                                <input id="Coordinate" class="input is-link" type="text" placeholder="Link input">
                                <button class="button is-primary fas fa-location-arrow" x-on:click="GetCurrentCoordinate()"></button>
                                <button class="button is-dark" x-on:click="GetVendorDirection()">Get Directions</button>
                            </div>
                            <div class="km-map-driving"> 
                                <div class="select" x-model="transport">
                                    <select>
                                        <option value="driving">Driving</option>
                                        <option value="walking">Walking</option>
                                        <option value="bicycling">Bicycling</option>
                                        <option value="public">Public Transport</option>
                                    </select>
                                </div>
                                <div class="select" x-model="transportUnit">
                                    <select>
                                        <option value="kilometers">Kilometers</option>
                                        <option value="miles">Miles</option>
                                    </select>
                                </div>
                            </div>
                            
                        </div>
                        
                    <!-- </div> -->
                </div>
                <div id="km-photos" x-show="menuTab === 'photos'">
                    <strong><p> this is photos </p> </strong>
                </div>
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
