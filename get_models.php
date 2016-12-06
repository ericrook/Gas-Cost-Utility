<?
require('api_keys.php');

$year = $_POST['year'];
$make = $_POST['make'];

$ch = curl_init();

//Edmunds V1 API Request
//$filter = "modelrepository/findmodelsbymakeandyear?make=".$make."&year=".$year;
//$url = "http://api.edmunds.com/v1/api/vehicle/".$filter."&api_key=$eak&fmt=json";

//V2 Edmunds API Request
$url = "https://api.edmunds.com/api/vehicle/v2/".$make."/models?fmt=json&year=".$year."&api_key=$eak";

curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $url);
$curlResponse = curl_exec($ch);
curl_close($ch);

$jsonDecoded = json_decode($curlResponse);

/*
for($i=0;$i<count($jsonDecoded->modelHolder);$i++){				
	print "<li><a href='#' class='car_model' rel='".$jsonDecoded->modelHolder[$i]->niceName."'>".$jsonDecoded->modelHolder[$i]->name."</a></li>";
}
*/

for($i=0;$i<count($jsonDecoded->models);$i++){				
	print "<li><a href='#' class='car_model' rel='".$jsonDecoded->models[$i]->niceName."'>".$jsonDecoded->models[$i]->name."</a></li>";
}

?>