
async function fetchApiData() 
{
    let response = await fetch('https://api.kushmapper.com/v1/vendors/1?include=products');
    let data = await response.json()
    return data;
}

fetchApiData().then(data => {
    console.log(data.data["name"]);
    console.log(data.data["products"]);
    console.log(data.data["products"][0].id);
    let html = '';

    html += '<tr>';
    html += '<td>Id</td>';
    html += '<td>Name</td>';
    html += '<td>THC%</td>';
    html += '<td>CBD%</td>';
    html += '<td>Price</td>';
    html += '<td>Category';
            // <form method="POST">
            //     <select name="Category">
            //         <option value="selected enabled">All</option>
            //         <option value="Ford">Ford</option>
            //         <option value="Benz">Benz</option>
            //         <option value="BMW">BMW</option>
            //     </select>
            //     <input type="hidden" name="submit" value="Get Selected Car" />
            // </form>      
    html += '</td>';
    html += '</tr>';

    let vendorData = document.getElementById("vendorData");
    let products = data.data["products"];
    for ( let i in products) {
        console.log(products[i].id);
        console.log(products[i].name);
        html +=  '<tr>';
        html += '<td>' + products[i].id + '</td>';
        html += '<td>' + products[i].name + '</td>';
        html += '<td>' + products[i].thc + '</td>';
        html += '<td>' + products[i].cbd + '</td>';
        html += '<td>' + products[i].price + '</td>';
        html += '<td>' + products[i].category + '</td>';
        html += '</tr>';
    }
    vendorData.innerHTML = html;
});






















// fetch('https://api.kushmapper.com/v1/vendors/1?include=products')
//     .then(res => res.json())
//     .then(data => {
//         console.log(data);
//         //return data;
//     })


// apidata = fetch_data();
// console.log(apidata);

// jQuery(document).ready(function($){
//     var apidata;
//     // fetch('http://localhost:3000/all')
//     fetch('http://api.kushmapper.com/v1/vendors/1?include=products', option)
//     .then(res => res.json())
//     .then(function(data){
//         apidata = data;
//         console.log(data);
//         var test = 55;
//         $.ajax({
//             url: '/wordpress/wp-admin/admin-ajax.php',
//             data: {
//                 'action': 'php_tutorial',
//                 'php_test': test
//             },
//             success: function(data){
//                 console.log("Happy")
//             }
//         });
//     })
// });