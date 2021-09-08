storeAddresses = [];
jQuery(document).ready(function() {
    
    startUrl = 'https://api.kushmapper.com/v1/locations';
    getCityData(startUrl).then(adresses => {
        // form data
        let preloadData = preparePreloadData(adresses);
        // console.log(preloadData);
        jQuery("km-location-select").html('').select2({preloadData});
        jQuery('.km-location-select').select2({
            theme: "classic",
            data: preloadData,
            escapeMarkup: function(markup) {
                return markup;
            },
            templateResult: formatState,
        }).trigger('change');
        jQuery('.select2').css({ "width": "100%" });
    });
});

function formatState (data) 
{
    // console.log(data);
    if (data.id == null) {
        return data.text;
    }
    
    if( data.history == true){

        html = "";
        html += '<i class="fas fa-history" style="margin-right: 10px;"></i><strong>' + data.text + '</strong>';
        html += '<label for="' + data.text + '">'+ "" +'</label>';
        html += '<input style="padding: 5px 10px 5px 10px; color: black; margin-left: 40px; border: 0; float:right; background:white;" class="info" type="button" id="' + data.text + '" value="x"><br>';
        
        // var $option = jQuery(html);
        // html = "";
        // html += '<label for="' + data.text + '">'+ "" +'</label>';
        // html += '<input style="padding: 5px 10px 5px 10px; color: black; margin-left: 40px; border: 0; float:right; background:white;" class="info" type="button" id="' + data.text + '" value="x"><br>';

        var $option = jQuery("<span></span>");
        
        var $preview = jQuery(html);
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
            idRemove = ".km-location-select " + "option[value='" + data.id + "']";
            jQuery(idRemove).remove();
        });

        // $option.text(data.text);
        $option.append($preview);
    }
    else 
    {
        html = "";
        html += '<span><i class="fas fa-map-marker-alt" style="margin-right: 10px;"></i><strong>' + data.text + '</strong></span>';
        var $option = jQuery(html);
    }
    
    return $option;
};           

const getCityData = async (url) =>
{
    data = false;
    adresses = [];
    currentPos = {};
    // currentPos = getCurrentLocationByIp();  

    currentLocationUrl = "https://api.ipdata.co?api-key=02d8d5bc5cda112d76192486d95426dc5ccb3e6e394cc105bf5ad0dd";

    const currentLocationInfo = axios.get(currentLocationUrl);
    const cityDataInfo = axios.get(url);

    let res = await axios.all([currentLocationInfo, cityDataInfo]).then(axios.spread(function(currentPosRes, cityDataRes) 
    {
        cityData = cityDataRes.data.data;
        currentPos.lat = parseFloat(currentPosRes.data.latitude);
        currentPos.lng = parseFloat(currentPosRes.data.longitude);

        // let res = await axios.get(url).then(function(response) {
        // cityData = response.data.data;
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
            // console.log(currentPos);
            distance = haversine_distance (currentPos, cityPos);
            // console.log(distance);
            cityInfo.distance = distance;
            adresses.push(cityInfo); 
            // sort city distance to acsending order
            adresses.sort((a,b)=> (a.distance > b.distance ? 1 : -1));
        }                          
        storeAddresses = adresses;
        // console.log(storeAddresses);
        return adresses;
    }))
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
                // localStorage.setItem("myPosLat", JSON.stringify(pos.lat));
                // localStorage.setItem("myPosLng", JSON.stringify(pos.lng));
            },
        );
    } 
    return currentPosition;
}

function getCurrentLocationByIp(){
    currentPosition = {};
    // url = "http://ip-api.com/json";
    url = "https://api.ipdata.co?api-key=02d8d5bc5cda112d76192486d95426dc5ccb3e6e394cc105bf5ad0dd";
    axios.get(url)
    .then(function(response) {     
        // console.log(response);
        // currentPosition.lat = response.data.lat;
        // currentPosition.lng = response.data.lon;
        currentPosition.lat = parseFloat(response.data.latitude);
        currentPosition.lng = parseFloat(response.data.longitude);
    })
    .catch(function(error) {
        console.log(error);
    })    
    console.log(currentPosition);
    return currentPosition;
}

function sendLocation(citySlug)
{
    if(citySlug.selectedIndex == -1)
        return;            

    citySlug = citySlug.options[citySlug.selectedIndex].value;
    for (let address of storeAddresses) 
    {   
        if (citySlug == address.city_slug)
        {
            // location = '/wordpress/location/' + address.country + '/' + address.state  + '/' + address.city_slug;
            location = '/location/' + address.country.toLowerCase() + '/' + address.state.toLowerCase()  + '/' + address.city_slug;
            // console.log(location);
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
    if(city == "Select a City" || city == "")
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
    cities = JSON.parse(localStorage.getItem("cities"));  
    console.log(cities);       
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
    if(cities == null){
        return;
    }
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

function preparePreloadData(addresses)
{
    result = [ {id: "", text: ""}];               
    cities = JSON.parse(localStorage.getItem("cities"));  
    if(cities){
        for (let city of cities) 
        {   
            let preLoad = { 
                id: '', 
                text: '', 
                history: false
            };

            for (let address of addresses) 
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
    }

    for (let address of addresses) 
    {   
        let preLoad = { 
            id: '', 
            text: '', 
            history: false
        };
        preLoad.id = address.city_slug;
        preLoad.text = address.city;  
        result.push(preLoad);   
    }     
    return result;
}
