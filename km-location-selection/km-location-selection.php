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
    $startUrl = 'http://api.kushmapper.com/v1/stores';
   
    ob_start();

    ?>
        <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB44vENDVAXY11oPRg4tSuHH2EEP9xhI1A"type="text/javascript"></script>
        <script>          
            function kmData() {
                return {
                    startUrl: '<?php echo $startUrl; ?>',
                    data: false,
                    storeAddresses: [],
                    currentPos: {},

                    async getData(url = this.startUrl) 
                    {
                        var self = this;
                        await axios.get(url)
                            .then(function(response) {
                                self.data = response.data.data;
                                geocoder = new google.maps.Geocoder();

                                for (let data of self.data) 
                                {
                                    let store = {
                                        address: "",
                                        city: "",
                                        latitude: 0.0,
                                        longitude:0.0,
                                    };
                                
                                    addressString = "https://maps.googleapis.com/maps/api/geocode/json?address=";
                                    store.address = data.address1 + "+" + data.city + "+" + data.state + "+" + data.country;
                                    addressString = addressString + store.address + "&key=AIzaSyB44vENDVAXY11oPRg4tSuHH2EEP9xhI1A";
                                    // console.log(addressString);
                                    axios.get(addressString)
                                    .then(function(response) {      
                                        // console.log(response);
                                        store.latitude = response.data.results[0].geometry.location.lat;
                                        store.longitude = response.data.results[0].geometry.location.lng;
                                        store.city = response.config.url.split("+")[1];
                                        console.log(store.city);
                                        self.storeAddresses.push(store); 
                                    })
                                    .catch(function(error) {
                                        console.log(error);
                                    })
                                }
                            })
                            .catch(function(error) {
                                console.log(error);
                            })    
                            self.getCurrentLocation();       
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


                    getStoreLocation()
                    {
                        cityPos = {};
                        distances = [];
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

                        for (let data of this.storeAddresses) 
                        {
                            if(data.latitude !=null && data.longitude !=null)
                            {
                                cityPos.lat = data.latitude,
                                cityPos.lng = data.longitude,
                                distance = this.haversine_distance (this.currentPos, cityPos);
                                // console.log("distance: " + data.city);
                                // console.log("distance: " + distance);
                                distances.push(distance);                  
                            }               
                        }
                        minDistancePos = 0;
                        minDistancePos =  distances.indexOf(Math.min(...distances));
                        console.log("min distance Pos: " + minDistancePos);
                        console.log("min distance city: " + this.storeAddresses[minDistancePos].city);
                        return this.storeAddresses[minDistancePos].city;        
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
            <template x-if="data">
                <button class="button is-dark" x-on:click="getStoreLocation()">Get Store Location</button>
            </template>
        </div>
        
    <?php
    $html = ob_get_clean();
    return $html;

}//end location_selection()
