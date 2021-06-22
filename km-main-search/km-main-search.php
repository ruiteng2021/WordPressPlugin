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
                            // console.log(params);
                            return {
                                q: params.term, // search term
                                page: params.page
                            };
                        },
                        processResults: function (data, params) {

                            let displayData = [];                              
                            
                            // displayData = [
                            //     { 
                            //         "text": "Group 1", 
                            //         "children" : [
                            //             {
                            //                 "id": 1,
                            //                 "text": "Option 1.1"
                            //             },
                            //             {
                            //                 "id": 2,
                            //                 "text": "Option 1.2"
                            //             }
                            //         ]
                            //     },
                            //     { 
                            //         "text": "Group 2", 
                            //         "children" : [
                            //             {
                            //                 "id": 21,
                            //                 "text": "Option 2.1"
                            //             },
                            //             {
                            //                 "id": 22,
                            //                 "text": "Option 2.2"
                            //             }
                            //         ]
                            //     },
                            //     { 
                            //         "text": "Group 3", 
                            //         "children" : [
                            //             {
                            //                 "id": 7,
                            //                 "text": "Option 3.1"
                            //             },
                            //             {
                            //                 "id": 8,
                            //                 "text": "Option 3.2"
                            //             }
                            //         ]
                            //     }
                            // ];

                            if (data.data.locations)
                                displayData = getDisplayData( displayData, data.data.locations);    

                            if (data.data.products)
                                displayData = getDisplayData( displayData, data.data.products);  

                            if (data.data.vendors)
                                displayData = getDisplayData( displayData, data.data.vendors);  
                            console.log(displayData);

                            params.page = params.page || 1;
                            return {
                                results: displayData,
                                // pagination: {
                                //     // more: (params.page * 30) < data.total_count
                                //     more: 1
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
                    // console.log("YYY repo YYY");
                    // console.log(repo);
                    return repo.name || repo.text;
                }

                function formatRepoSelection (repo) {
                    // console.log("XXX repo XXX");
                    // console.log(repo);
                    if(repo.url)
                    {
                        url = repo.url; 
                        // let url = repo.url.replace("https://v2.kushmapper.com","");
                        // url = "/wordpress" + url;
                        window.location.assign(url);
                    }                        
                    return repo.name || repo.text;
                }

                function getDisplayData(displayData, rawData)
                {
                    let parent = {
                        text:"",
                        children : []
                    };

                    if(rawData)
                    {
                        parent.text = rawData[0].type;
                        parent.text = parent.text.charAt(0).toUpperCase() + parent.text.slice(1).toLowerCase();
                    }
                    
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
                        parent.children.push(tempData);                               
                    }

                    displayData.push(parent);
                    return displayData;
                }
            });

        </script>
        
    <?php
    return $html;

} // end main_search()
