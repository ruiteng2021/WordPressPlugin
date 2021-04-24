

async function fetchApiData() 
{
    let response = await fetch('https://api.kushmapper.com/v1/vendors/1?include=products');
    let data = await response.json()
    return data;
}

var products; 

fetchApiData().then(data => {
    console.log(data.data["name"]);
    let vendorData = document.getElementById("vendorData");
    products = data.data["products"];
    let categories = [];
    let html = '';
    html += '<tr>';
    html += '<td>' + data.data["name"] + '</td>';
    html += '<td>Product</td>';
    html += '<td>THC%</td>';
    html += '<td>CBD%</td>';
    html += '<td>Price</td>';
    html += '<td>Category';
    html += '<form method="POST">';
    html += '<select name="Category" id= "cat" onChange= "UpdateTableCategory()">';
    html += '<option value="selected enabled" name="all">All</option>';

    // remove duplicate
    for ( let i in products) {
        categories[i] =  products[i].category;
    }
    let category = [...new Set(categories)];
    console.log(category);

    for ( let i in category) {
        html += '<option value="' + category[i] + '" name="' + category[i] + '">' + category[i] + '</option>';
    }

    html += '</select>'
    html += '</form>'      
    html += '</td>';
    html += '</tr>';

    for ( let i in products) {
        // console.log(products[i].id);
        // console.log(products[i].name);
        console.log(products[i].image_url);
        html +=  '<tr>';
        html += '<td><img src="' + products[i].image_url + '" alt="" height="200" width="200">' + '</td>';
        html += '<td>' + products[i].name + '<br>' + 
                '<p>' + products[i].price_oz_eighth + ' per 1/8 oz</p>' + 
                '<p>' + products[i].price_oz_fourth + ' per 1/4 oz</p>' + 
                '<p>' + products[i].price_oz_half + ' per 1/2 oz</p>' + 
                '<p>' + products[i].price_oz + ' per 1 oz</p>' + '</td>';
        html += '<td>' + products[i].thc + '</td>';
        html += '<td>' + products[i].cbd + '</td>';
        html += '<td>' + products[i].price + '</td>';
        html += '<td>' + products[i].category + '</td>';
        html += '</tr>';
    }
    vendorData.innerHTML = html;
});

function UpdateTableCategory()
{
    let value = jQuery('#cat option:selected').attr('value');
    // delete all rows except table head
    jQuery("#vendorData").find("tr:gt(0)").remove();
    console.log(products);
    // add selected data with categories
    let html = '';
    for ( let i in products) {
        console.log(value);
        console.log(products[i].category);
        if ( value.localeCompare(products[i].category) == 0 ) 
        {
            html += '<tr>';
            html += '<td><img src="' + products[i].image_url + '" alt="" height="200" width="200">' + '</td>';
            html += '<td>' + products[i].name + '<br>' + 
                '<p>' + products[i].price_oz_eighth + ' per 1/8 oz</p>' + 
                '<p>' + products[i].price_oz_fourth + ' per 1/4 oz</p>' + 
                '<p>' + products[i].price_oz_half + ' per 1/2 oz</p>' + 
                '<p>' + products[i].price_oz + ' per 1 oz</p>' + '</td>';
            html += '<td>' + products[i].thc + '</td>';
            html += '<td>' + products[i].cbd + '</td>';
            html += '<td>' + products[i].price + '</td>';
            html += '<td>' + products[i].category + '</td>';
            html += '</tr>';
        }
        if ( value.localeCompare('selected enabled') == 0 ) 
        {
            html += '<tr>';
            html += '<td><img src="' + products[i].image_url + '" alt="" height="200" width="200">' + '</td>';
            html += '<td>' + products[i].name + '<br>' + 
                '<p>' + products[i].price_oz_eighth + ' per 1/8 oz</p>' + 
                '<p>' + products[i].price_oz_fourth + ' per 1/4 oz</p>' + 
                '<p>' + products[i].price_oz_half + ' per 1/2 oz</p>' + 
                '<p>' + products[i].price_oz + ' per 1 oz</p>' + '</td>';
            html += '<td>' + products[i].thc + '</td>';
            html += '<td>' + products[i].cbd + '</td>';
            html += '<td>' + products[i].price + '</td>';
            html += '<td>' + products[i].category + '</td>';
            html += '</tr>';
        }
    }
    jQuery('#vendorData tr:last').after(html);
}




