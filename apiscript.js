console.log('XXXXX');

const options = {
    method: 'GET',
    // mode: 'no-cors',
    headers: {
        'Content-type': 'application/json'
    }
  };


// fetch('https://reqres.in/api/users')
// fetch('https://api.edamam.com/search?q=chicken&app_id=24dddfe0&app_key=0b3613d622b2fea76fa8009aabab6dd1', options)
async function fetchApiData() 
{
  let response = await fetch('https://api.kushmapper.com/v1/vendors/1?include=products');
  let data = await response.json()
  console.log(data);
  return data;
}

let finalData = fetchApiData();


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