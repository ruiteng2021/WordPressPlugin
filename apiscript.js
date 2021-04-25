

async function FetchApiData() 
{
    let response = await fetch('https://api.kushmapper.com/v1/vendors/1?include=products');
    let data = await response.json()
    return data;
}

FetchApiData().then(data => {
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
    html += '<select name="Category" id= "cat" onChange= "UpdateTableCategory(products)">';
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
        html = CreateTableRows(html, products[i]);
    }
    vendorData.innerHTML = html;
});

function UpdateTableCategory(products)
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
            html = CreateTableRows(html, products[i]);
        }
        if ( value.localeCompare('selected enabled') == 0 ) 
        {
            html = CreateTableRows(html, products[i]);
        }
    }
    jQuery('#vendorData tr:last').after(html);
}

function CreateTableRows(trHtml, contents)
{
    trHtml += '<tr>';
    trHtml += '<td><img src="' + contents.image_url + '" alt="" height="200" width="200">' + '</td>';
    trHtml += '<td>' + contents.name + '<br>' + 
        '<p>' + contents.price_oz_eighth + ' per 1/8 oz</p>' + 
        '<p>' + contents.price_oz_fourth + ' per 1/4 oz</p>' + 
        '<p>' + contents.price_oz_half + ' per 1/2 oz</p>' + 
        '<p>' + contents.price_oz + ' per 1 oz</p>' + '</td>';
    trHtml += '<td>' + contents.thc + '</td>';
    trHtml += '<td>' + contents.cbd + '</td>';
    trHtml += '<td>' + contents.price + '</td>';
    trHtml += '<td>' + contents.category + '</td>';
    trHtml += '</tr>';
    return trHtml;
}


