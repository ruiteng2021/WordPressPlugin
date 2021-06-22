<?php
include 'config.php';

// if(!isset($_POST['searchTerm'])){
// 	$fetchData = mysqli_query($con,"select * from users order by name limit 5");
// }else{
// 	$search = $_POST['searchTerm'];
// 	$fetchData = mysqli_query($con,"select * from users where name like '%".$search."%' limit 5");
// }
	
$slug = get_query_var('slug');
$request = wp_remote_get( 'https://api.kushmapper.com/v1/vendors');
$body = wp_remote_retrieve_body( $request );
$data = json_decode( $body );
$apidata = $data->data;

$dataFinal = array();
foreach ($apidata as $value) {
    $dataFinal[] = array("id"=>$value->slug, "text"=>$value->name);
}

echo json_encode($dataFinal);
