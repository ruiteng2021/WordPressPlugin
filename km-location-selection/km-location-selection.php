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
    $startUrl =  'https://api.kushmapper.com/v1/locations';
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
                    
                    getData(url = this.startUrl) 
                    {
                        var self = this;
                        axios.get(url)
                            .then(function(response) {
                                self.data = response.data.data;
                                // console.log(response);
                                currentPos = {};
                                currentPos = self.getCurrentLocation();   
                                geocoder = new google.maps.Geocoder();

                                // collect city info 
                                for (let data of self.data) 
                                {            
                                    let cityInfo = {
                                        country: "",
                                        province: "",
                                        city: "",
                                        latitude: 0.0,
                                        longitude: 0.0,
                                        distance: 0.0,
                                    };                                
                                    
                                    addressString = "https://maps.googleapis.com/maps/api/geocode/json?address=";
                                    cityInfo.address = data.city + "+" + data.state + "+" + data.country;
                                    addressString = addressString + cityInfo.address + "&key=AIzaSyB44vENDVAXY11oPRg4tSuHH2EEP9xhI1A";
                                    // console.log(addressString);
                                    axios.get(addressString)
                                    .then(function(response) {     
                                        // console.log(response);
                                        cityInfo.latitude = response.data.results[0].geometry.location.lat;
                                        cityInfo.longitude = response.data.results[0].geometry.location.lng;
                                        cityInfo.country = response.config.url.split("+")[2].split("&")[0];
                                        cityInfo.province = response.config.url.split("+")[1];
                                        cityInfo.city = response.config.url.split("+")[0].split("=")[1];
                                        // console.log(cityInfo.city);   
                                        cityPos = {};                                     
                                        cityPos.lat = cityInfo.latitude,
                                        cityPos.lng = cityInfo.longitude,
                                        distance = self.haversine_distance (currentPos, cityPos);
                                        cityInfo.distance = distance;
                                        console.log("distance: " + distance);
                                        self.storeAddresses.push(cityInfo); 
                                        // sort city distance to acsending order
                                        self.storeAddresses.sort((a,b)=> (a.distance > b.distance ? 1 : -1))
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
                                    currentPosition.lat = pos.lat;
                                    currentPosition.lng = pos.lng;
                                },
                            );
                        } 
                        return currentPosition;
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
                        <option :value="'/wordpress/location/' + address.country + '/' + address.province  + '/' + address.city" x-text="address.city"></option>
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