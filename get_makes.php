<?
require('api_keys.php');

$year = $_POST['year'];

$ch = curl_init();

//Edmunds V1 API Request
//$filter = "makerepository/findmakesbymodelyear?year=".$year;
//$url = "http://api.edmunds.com/v1/api/vehicle/".$filter."&api_key=$eak&fmt=json";

//Edmunds V2 API Request
$url = "http://api.edmunds.com/api/vehicle/v2/makes?fmt=json&year=".$year."&api_key=$eak";

curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $url);
$curlResponse = curl_exec($ch);
curl_close($ch);

$jsonDecoded = json_decode($curlResponse);

/*
for($i=0;$i<count($jsonDecoded->makeHolder);$i++){				
	print "<li><a href='#' class='car_make' rel='".$jsonDecoded->makeHolder[$i]->niceName."'>".$jsonDecoded->makeHolder[$i]->name."</a></li>";
}
*/
for($i=0;$i<count($jsonDecoded->makes);$i++){				
	print "<li><a href='#' class='car_make' rel='".$jsonDecoded->makes[$i]->niceName."'>".$jsonDecoded->makes[$i]->name."</a></li>";
}
?>