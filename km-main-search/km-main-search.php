<?php
/**
 * Plugin Name: Kushmapper main search bar
 * Description: search keyword with city, vendor, and product etc.
 * Author: SlyFox
 * Version: 0.1.1
 */

add_shortcode('main-search', 'main_search');


function main_search()
{
    $startUrl =  'https://api.kushmapper.com/v1/locations';
    $citiesUrl =  'https://api.kushmapper.com/v1/locations';
    $vendorUrl =  'https://api.kushmapper.com/v1/locations';
    $productUrl =  'https://api.kushmapper.com/v1/locations';


    $html = '';
    $html .= '<div class="columns is-full">';
    $html .= '<div class="column">';
    $html .= '<select style="width:100%" class="km-main-search-bar">';
    $html .= '</select>';
    $html .= '</div>';
    $html .= '</div>';

    ?>
        <script>
            jQuery(document).ready(function() {
                jQuery(".km-main-search-bar").select2({
                    ajax: {
                        url: " https://api.kushmapper.com/v1/search",
                        contentType: 'application/json',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            console.log(params);
                            return {
                                q: params.term, // search term
                                page: params.page
                            };
                        },
                        processResults: function (data, params) {

                            let displayData = [];                                     
                            if (data.data.locations)
                                displayData = getDisplayData( displayData, data.data.locations);    

                            if (data.data.products)
                                displayData = getDisplayData( displayData, data.data.products);  

                            if (data.data.vendors)
                                displayData = getDisplayData( displayData, data.data.vendors);  
                            console.log(displayData);

                            params.page = params.page || 1;
                            return {
                                results: displayData
                                // pagination: {
                                //     // more: (params.page * 30) < data.total_count
                                //     more: (params.page * 20) < data.meta.total
                                // }
                            };
                            
                        },
                        cache: true
                    },
                    placeholder: 'Search for location, vendor, product',
                    minimumInputLength: 1,
                    escapeMarkup: function(markup) { return markup;},
                    templateResult: formatRepo, 
                    templateSelection: formatRepoSelection
                });

                function formatRepo (repo) {
                    return repo.name || repo.text;
                }

                function formatRepoSelection (repo) {
                    // console.log(repo);
                    if(repo.url)
                    {
                        let url = repo.url.replace("https://v2.kushmapper.com","");
                        // url = "/wordpress" + url;
                        window.location.replace(url);
                    }                        
                    return repo.name || repo.text;
                }

                function getDisplayData(displayData, rawData)
                {
                    for (let item of rawData)
                    {
                        let tempData = {
                            id:"",
                            name:"",
                            url: ""                                
                        };
                        tempData.url = item.url;
                        if(item.type == "locations")
                            tempData.name = item.searchable.city;
                        else
                            tempData.name = item.searchable.name;
                        tempData.id = item.searchable.id;
                        displayData.push(tempData);                               
                    }
                    return displayData;
                }
            });

        </script>
        
    <?php
    return $html;

} // end main_search()
