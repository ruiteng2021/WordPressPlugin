<?php
/**
 * Plugin name: Query APIs
 * Description: Get information from kushmapper APIs in WordPress
 * Author: Rui Teng
 * version: 0.1.0
 * License: GPL2 or later.
 * text-domain: query-apis
 */

use AC\Asset\Script;

// If this file is access directly, abort!!!
defined( 'ABSPATH' ) or die( 'Unauthorized Access' );

function add_scripts(){
    ?>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>  
    <?php
    wp_enqueue_script( 'api_script', plugin_dir_url( __FILE__ ) . 'apiscript.js', array('jquery'), '1.0.0', false);
}

add_action( 'wp_enqueue_scripts', 'add_scripts' );

// function our_tutorial(){
// 	if(isset($_REQUEST)){
// 		$testing = $_REQUEST['php_test'];

// 		echo 'This is our JS Variable :'.$testing;

// 		// global $wpdb;
// 		// $wpdb->insert(
// 		// 	$wpdb->prefix.'lms_enroll',
// 		// 	[
// 		// 		'ID' => $testing
// 		// 	]
// 		// );

// 	}
// 	die();
// }
// add_action('wp_ajax_php_tutorial', 'our_tutorial');

// function our_tutorial() {
//     if(isset($_REQUEST))
//     {
//         $data = $_REQUEST['php_test'];
//         echo 'AAAAAAAAAAAAa';
//     }
// }
// add_action( 'wp_ajax_php_tutorial', 'our_tutorial');


// create posts data .
add_shortcode('external_data', 'add_kusgmapper_data');

function add_kusgmapper_data(){

    $url = 'http://api.kushmapper.com/v1/vendors/1?include=products';
    
    $arguments = array(
        'method' => 'GET'
    );

    $response = wp_remote_get( $url, $arguments );

	if ( is_wp_error( $response ) ) {
		$error_message = $response->get_error_message();
		return "Something went wrong: $error_message";
	} 

    // echo '<pre>';
    //  var_dump( wp_remote_retrieve_body( $response ) );
    // echo '</pre>';

    $result = json_decode( wp_remote_retrieve_body( $response ) ); 
    // var_dump($result);
    // var_dump($result->data->produts);

    $html = '';
    $html .= '<table>';

    $html .= '<tr>';
    $html .= '<td>Id</td>';
    $html .= '<td>Name</td>';
    $html .= '<td>THC%</td>';
    $html .= '<td>CBD%</td>';
    $html .= '<td>Price</td>';
    $html .= 
        '<td>
            <form method="POST">
                <select name="Category">
                    <option value="selected enabled">All</option>
                    <option value="Ford">Ford</option>
                    <option value="Benz">Benz</option>
                    <option value="BMW">BMW</option>
                </select>
                <input type="hidden" name="submit" value="Get Selected Car" />
            </form>      
        </td>';
    $html .= '</tr>';

    error_log("XXXXXXXXXXXXXX");
    if (isset($_POST['submit']))
    {
        $getCar=$_POST['Category'];
        var_dump("The selected car :" . $getCar) ;
        error_log("The selected car :");
    }

    foreach( $result->data->products as $result) {
        $html .= '<tr>';
        $html .= '<td>' . $result->id. '</td>';
        $html .= '<td>' . $result->name. '</td>';
        $html .= '<td>' . $result->thc. '</td>';
        $html .= '<td>' . $result->cbd . '</td>';
        $html .= '<td>' . $result->price . '</td>';
        $html .= '<td>' . $result->category . '</td>';
        $html .= '</tr>';
    }

    $html .= '</table>';
    return $html;
    
}






// add_shortcode('external_data_selection', 'add_kusgmapper_data_selection');

// function add_kusgmapper_data_selection(){

//     $html = '';
//     $html .= '<div style="margin: 0 auto;">';
//     $html .= '<select>';
//     $html .= '<option>10</option>';
//     $html .= '<option>25</option>';
//     $html .= '<option>50</option>';
//     $html .= '<option>100</option>';
//     $html .= '</select>';
//     $html .= '</div>';
//     return $html;
// }

// add_action('init', 'handle_preflight');
// function handle_preflight() {
//     error_log("XXXXX handle_preflight XXXXX");
//     header('Access-Control-Allow-Origin: *');

//     // $origin = get_http_origin();
//     // error_log($origin);
//     // if ($origin === 'http://127.0.0.1/wordpress/2021/04/20/13/') {
//     //     error_log("XXXXX handle_preflight XXXXX");
//     //     header("Access-Control-Allow-Origin: http://127.0.0.1/wordpress/2021/04/20/13/");
//     //     header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
//     //     header("Access-Control-Allow-Credentials: true");
//     //     header('Access-Control-Allow-Headers: Origin, X-Requested-With, X-WP-Nonce, Content-Type, Accept, Authorization');
//     //     if ('OPTIONS' == $_SERVER['REQUEST_METHOD']) {
//     //         status_header(200);
//     //         exit();
//     //     }
//     // }
// }

// add_filter('rest_authentication_errors', 'rest_filter_incoming_connections');
// function rest_filter_incoming_connections($errors) {
//     error_log("XXXXX rest_filter_incoming_connections XXXXX");
//     $request_server = $_SERVER['REMOTE_ADDR'];
//     $origin = get_http_origin();
//     if ($origin !== 'http://127.0.0.1/wordpress/2021/04/20/13/') return new WP_Error('forbidden_access', $origin, array(
//         'status' => 403
//     ));
//     return $errors;
// }