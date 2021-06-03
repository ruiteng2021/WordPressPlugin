<?php
/**
 * Plugin Name: Kushmapper location selection Product
 * Description: loction auto detect and list all locations
 * Author: SlyFox
 * Version: 0.1.1
 */

add_shortcode('location-selection', 'location_selection');


function location_selection()
{
    // $startUrl = 'http://api.kushmapper.com/v1/stores';
    // $startUrl = 'http://api.kushmapper.com/v1/stores';
    $startUrl =  'http://api.kushmapper.com/v1/vendors?page_size=1000';
    ob_start();

    ?>
        <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB44vENDVAXY11oPRg4tSuHH2EEP9xhI1A"type="text/javascript"></script>
        <script>
            jQuery(document).ready(function() {
                jQuery('.km-location-select').select2({theme: "classic"});
                jQuery('.select2').css({ "width": "100%" });
            });
        </script>     
        
        <script>          
            function kmData() {
                return {
                    startUrl: '<?php echo $startUrl; ?>',
                    data: false,
                    storeAddresses: [],
                    currentPos: {},
                    distances: [],
                    
                    getData(url = this.startUrl) 
                    {
                        var self = this;
                        self.getCurrentLocation();   
                        axios.get(url)
                            .then(function(response) {
                                self.data = response.data.data;
                                // console.log(response);

                                stores = [];
                                for (let data of self.data) 
                                {
                                    if(data.stores.length)
                                        stores.push(data.stores[0]);
                                    else
                                        continue; // skip stores not exist
                                }
                                // remove the duplicate cities                            
                                let adressesNoDuplicate = [...new Map(stores.map(item => [item.city, item])).values()];
                                // console.log(adressesNoDuplicate);

                                geocoder = new google.maps.Geocoder();
                                // let n = 0;
                                // for (let data of self.data) 
                                for (let data of adressesNoDuplicate) 
                                {            
                                    let store = {
                                        address: "",
                                        country: "",
                                        province: "",
                                        city: "",
                                        latitude: 0.0,
                                        longitude: 0.0,
                                        distance: 0.0,
                                    };
                                
                                    // n = n + 1;
                                    // if(n%50 == 0) {
                                    //     // delay every 50 time
                                    //     // limitation of geolocation 
                                    //     // only 50 time requests every second.
                                    //     let now = new Date().getTime();
                                    //     console.log(now);
                                    //     while(new Date().getTime() < now + 500){ /* do nothing */ } 
                                    // }
                                    
                                    addressString = "https://maps.googleapis.com/maps/api/geocode/json?address=";
                                    store.address = data.address1 + "+" + data.city + "+" + data.state + "+" + data.country;
                                    addressString = addressString + store.address + "&key=AIzaSyB44vENDVAXY11oPRg4tSuHH2EEP9xhI1A";
                                    // console.log(addressString);
                                    axios.get(addressString)
                                    .then(function(response) {     
                                        console.log(response);
                                        store.latitude = response.data.results[0].geometry.location.lat;
                                        store.longitude = response.data.results[0].geometry.location.lng;
                                        store.country = response.config.url.split("+")[3].split("&")[0];
                                        store.province = response.config.url.split("+")[2];
                                        store.city = response.config.url.split("+")[1];
                                        console.log(store.city);   
                                        cityPos = {};                                     
                                        cityPos.lat = store.latitude,
                                        cityPos.lng = store.longitude,
                                        distance = self.haversine_distance (self.currentPos, cityPos);
                                        store.distance = distance;
                                        console.log("distance: " + distance);
                                        // distances.push(distance); 
                                        self.storeAddresses.push(store); 
                                        self.storeAddresses.sort((a,b)=> (a.distance > b.distance ? 1 : -1))
                                        // console.log(self.storeAddresses);
                                    })
                                    .catch(function(error) {
                                        console.log(error);
                                    })
                                }
                            })
                            .catch(function(error) {
                                console.log(error);
                            })       
                    },


                    getCurrentLocation(){
                        currentPosition = {};
                        if (navigator.geolocation) 
                        {
                            navigator.geolocation.getCurrentPosition(
                                (position) => {
                                    const pos = {
                                        lat: position.coords.latitude,
                                        lng: position.coords.longitude,
                                    };
                                    this.currentPos.lat = pos.lat;
                                    this.currentPos.lng = pos.lng;
                                },
                            );
                        } 
                    },

                    getStoreLocation(address)
                    {   
                        // cityPos = {};
                        // distances = [];
                        // for (let data of this.data) 
                        // {
                        //     if(data.latitude !=null && data.longitude !=null)
                        //     {
                        //         cityPos.lat = data.latitude,
                        //         cityPos.lng = data.longitude,
                        //         distance = this.haversine_distance (this.currentPos, cityPos);
                        //         distances.push(distance);                  
                        //     }
                        // }    
                        // for (let address of this.storeAddresses) 
                        // {
                        //     // console.log(address);
                        //     if(address.latitude !=null && address.longitude !=null)
                        //     {
                        //         cityPos.lat = address.latitude,
                        //         cityPos.lng = address.longitude,
                        //         distance = this.haversine_distance (this.currentPos, cityPos);
                        //         console.log("distance: " + distance);
                        //         distances.push(distance);                  
                        //     }               
                        // }
                        // console.log(this.distances);
                        // minDistancePos = 0;
                        // minDistancePos =  this.distances.indexOf(Math.min(...this.distances));
                        // console.log("min distance Pos: " + minDistancePos);
                        // console.log("min distance city: " + this.storeAddresses[minDistancePos].city);
                        // minDistanceAddress = this.storeAddresses[minDistancePos];
                        // console.log(minDistanceAddress);
                        // delete this.storeAddresses[minDistancePos];
                        // this.storeAddresses.unshift(minDistanceAddress);  
                        console.log(address);    
                        console.log(this.storeAddresses);  
                    },

                    haversine_distance(mk1, mk2) 
                    {
                        let R = 3958.8; // Radius of the Earth in miles
                        let rlat1 = mk1.lat * (Math.PI/180); // Convert degrees to radians
                        let rlat2 = mk2.lat * (Math.PI/180); // Convert degrees to radians
                        let difflat = rlat2-rlat1; // Radian difference (latitudes)
                        let difflon = (mk2.lng-mk1.lng) * (Math.PI/180); // Radian difference (longitudes)
                        let d = 2 * R * Math.asin(Math.sqrt(Math.sin(difflat/2)*Math.sin(difflat/2)+Math.cos(rlat1)*Math.cos(rlat2)*Math.sin(difflon/2)*Math.sin(difflon/2)));
                        return d;
                    },

                };
            }
        </script>

        <div class="columns is-full" x-data="kmData()" x-init="getData()">
            <div class="column">
                <select class="km-location-select" name="state" onchange="location = this.options[this.selectedIndex].value;">
                    <option value="city">Select a City</option>
                    <template x-for="address in storeAddresses" :key="address">
                        <option :value="address.country + '/' + address.province  + '/' + address.city" x-text="address.city"></option>
                    </template>       
                </select>
            </div>
        </div>
        
    <?php
    $html = ob_get_clean();
    return $html;

}//end location_selection()

function location_selection_styles()
{
    wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
    wp_enqueue_script( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array() );
}//end location_selection_styles()

add_action('wp_enqueue_scripts', 'location_selection_styles');