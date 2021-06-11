<?php
/**
 * Plugin Name: Kushmapper location selection
 * Description: Nearst location auto detect and list all locations
 * Author: SlyFox
 * Version: 0.1.1
 */

add_shortcode('location-selection', 'location_selection');


function location_selection()
{
    $startUrl =  'https://api.kushmapper.com/v1/locations';


    $html = '';
    $html .= '<div class="columns is-full">';
    $html .= '<div class="column">';
    $html .= '<select class="km-location-select" data-placeholder="Select a City" onchange="sendLocation(this); storeHistory(this)">';
    $html .= '</select>';
    $html .= '</div>';
    $html .= '</div>';

    // ob_start();

    ?>
        <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB44vENDVAXY11oPRg4tSuHH2EEP9xhI1A"type="text/javascript"></script>
        <script>
            jQuery(document).ready(function() {

                storeAddresses = [];
                startUrl = '<?php echo $startUrl; ?>';
   
                getCityData(startUrl).then(adresses => {
                    // console.log(adresses);
                    let html = '';
                    html += '<option></option>';
                    //  get from local storage histroy first.
                    html += getCityHistory();
                    jQuery('.km-location-select').html(html);  

                    // form data
                    let preloadData = preparePreloadData(adresses);
                    // console.log(preloadData);
                    jQuery('.km-location-select').select2({
                        theme: "classic",
                        data: preloadData,
                        escapeMarkup: function(markup) {
                            return markup;
                        },
                        templateResult: formatState,
                    }).trigger('change');

                    jQuery('.select2').css({ "width": "30%" });
                    // console.log(storeAddresses);
                    // append new data without forming
                    for (let address of adresses) 
                    {
                        let newOption = new Option(address.city, address.city_slug, false, false);
                        jQuery('.km-location-select').append(newOption).trigger('change');
                    }   
                });
            });

            function formatState (data) {
                // console.log(data);
                if (data.id == null) {
                    return data.text;
                }
                
                if( data.history == true){

                    html = "";
                    html += '<label for="' + data.text + '">'+ "" +'</label>';
                    html += '<input style="padding: 5px 10px 5px 10px; color: black; margin-left: 40px; border: 0; float:right; background:white;" class="info" type="button" id="' + data.text + '" value="x"><br>';

                    var $option = jQuery("<span></span>");
                    // console.log($option);
                    var $preview = jQuery(html);
                    // console.log($preview);
                    $preview.prop("href", data.id);
                    $preview.on('mouseup', function (evt) {
                        // Select2 will remove the dropdown on `mouseup`, which will prevent any `click` events from being triggered
                        // So we need to block the propagation of the `mouseup` event
                        evt.stopPropagation();
                    });
                    
                    $preview.on('click', function (evt) {
                        // click here to remove option items 
                        id = "#" + data._resultId;
                        jQuery(id).css("display", "none");  
                        removeHistory(data.text);
                    });
                    
                    $option.text(data.text);
                    $option.append($preview);
                }
                else 
                {
                    html = "";
                    var $option = jQuery("<span></span>");
                    var $preview = jQuery(html);
                    $preview.prop("href", data.id);
                    $option.text(data.text);
                    $option.append($preview);
                }
                
                return $option;
            };           

            // async function getCityData(url) 
            const getCityData = async (url) =>
            {
                data = false;
                adresses = [];
                currentPos = {};
                currentPos = getCurrentLocation();  

                let res = await axios.get(url)
                    .then(function(response) {
                        cityData = response.data.data;
                        // console.log(cityData);
                        for (let data of cityData) 
                        {            
                            let cityInfo = {
                                city: "",
                                city_slug: "",
                                state: "",
                                country: "",
                                lat: 0.0,
                                lng: 0.0,
                                distance: 0.0,
                            }; 

                            cityInfo.city = data.city;
                            cityInfo.city_slug = data.city_slug;
                            cityInfo.state = data.state;
                            cityInfo.country = data.country;
                            cityInfo.lat = parseFloat(data.lat);
                            cityInfo.lng = parseFloat(data.lng);
                            cityPos = {};                                     
                            cityPos.lat = parseFloat(data.lat);
                            cityPos.lng = parseFloat(data.lng);
                            // console.log(cityPos);
                            distance = haversine_distance (currentPos, cityPos);
                            cityInfo.distance = distance;
                            adresses.push(cityInfo); 
                            // sort city distance to acsending order
                            adresses.sort((a,b)=> (a.distance > b.distance ? 1 : -1));
                        }                          
                        storeAddresses = adresses;
                        // console.log(storeAddresses);
                        return adresses;
                    })
                    .catch(function(error) {
                        console.log(error);
                    })   

                    return res;
            }

            function haversine_distance(mk1, mk2) 
            {
                let R = 3958.8; // Radius of the Earth in miles
                let rlat1 = mk1.lat * (Math.PI/180); // Convert degrees to radians
                let rlat2 = mk2.lat * (Math.PI/180); // Convert degrees to radians
                let difflat = rlat2-rlat1; // Radian difference (latitudes)
                let difflon = (mk2.lng-mk1.lng) * (Math.PI/180); // Radian difference (longitudes)
                let d = 2 * R * Math.asin(Math.sqrt(Math.sin(difflat/2)*Math.sin(difflat/2)+Math.cos(rlat1)*Math.cos(rlat2)*Math.sin(difflon/2)*Math.sin(difflon/2)));
                return d;
            }
            
            function getCurrentLocation(){
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
            }

            function getCurrentLocationByIp(){
                currentPosition = {};
                url = "http://ip-api.com/json";
                axios.get(url)
                .then(function(response) {     
                    console.log(response);
                    currentPosition.lat = response.data.lat;
                    currentPosition.lng = response.data.lon;
                })
                .catch(function(error) {
                    console.log(error);
                })    
                console.log(currentPosition);
                return currentPosition;
            }

            function sendLocation(citySlug)
            {
                // console.log("XXXXX  sendLocation XXXXX");
                // console.log(citySlug.selectedIndex);

                if(citySlug.selectedIndex == -1)
                    return;            

                // console.log(storeAddresses);
                // console.log(citySlug.options[citySlug.selectedIndex].value);

                citySlug = citySlug.options[citySlug.selectedIndex].value;
                for (let address of storeAddresses) 
                {   
                    if (citySlug == address.city_slug)
                    {
                        location = '/wordpress/location/' + address.country + '/' + address.state  + '/' + address.city_slug;
                        // location = '/location/' + address.country + '/' + address.state  + '/' + address.city_slug;
                        console.log(location);
                        return  location;
                    }
                }               
            }

            function storeHistory(citySlug)
            {
                if(citySlug.selectedIndex == -1)
                    return;

                let cities;
                let city = citySlug.options[citySlug.selectedIndex].text;
                if(city == "Select a City")
                    return;

                cities = JSON.parse(localStorage.getItem("cities"));
                if(cities == null){
                    localStorage.setItem("cities", JSON.stringify([city]));
                }
                else{
                    // console.log(cities);
                    if(!cities.find(e => e === city))
                    {
                        cities.push(city);
                        localStorage.setItem("cities", JSON.stringify(cities));
                    }
                }
            }

            function removeHistory(item)
            {
                console.log(item);       
                cities = JSON.parse(localStorage.getItem("cities"));  
                cities = cities.filter(f => f !== item);
                console.log(cities);   
                localStorage.setItem("cities", JSON.stringify(cities));
            }

            function getCityHistory()
            {
                cities = JSON.parse(localStorage.getItem("cities"));
                // console.log(cities);
                // console.log(storeAddresses);
                html = '';
                for ( let city of cities) {

                    let cityShow = {
                        city : "",
                        city_slug : "",
                    };

                    for (let address of storeAddresses) 
                    {   
                        if (city == address.city)
                        {
                            cityShow.city = city;
                            cityShow.city_slug = address.city_slug;
                            html += '<option value="' + cityShow.city_slug + '">' + cityShow.city + '</option>';
                            break;
                        }
                    }        
                    
                }           
                // console.log(html);
                return html;
            }

            function preparePreloadData(addresses){
                result = [];               
                cities = JSON.parse(localStorage.getItem("cities"));  
                for (let city of cities) 
                {   
                    let preLoad = { 
                        id: '', 
                        text: '', 
                        history: false
                    };

                    for (let address of storeAddresses) 
                    {   
                        if (city == address.city)
                        {
                            preLoad.id = address.city_slug;
                            preLoad.text = address.city;
                            preLoad.history = true;    
                            result.push(preLoad);   
                            break;
                        }
                    }     
                }       
                return result;
            }

        </script>
        
    <?php
    // $html = ob_get_clean();
    return $html;

}//end location_selection()

function location_selection_styles()
{
    wp_enqueue_style('locationSelect2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
    wp_enqueue_script( 'locationSelect2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array(), true );
}//end location_selection_styles()

add_action('wp_enqueue_scripts', 'location_selection_styles');