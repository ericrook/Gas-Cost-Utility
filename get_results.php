<?php
require('api_keys.php');
	
//SET ALL VARIABLES
$outputHTML = "";
$make = $_POST['make'];
$model = $_POST['name'];
$year = $_POST['year'];
$tank_full = $_POST['gas'];
$origin = $_POST['origin'];
$origin = urlencode($origin);
$fromLat = $_POST['fromLat'];
$fromLon = $_POST['fromLon'];
$destination = $_POST['destination'];
$gas_price = 3.27;//set incase no prices are returned

/*************************/
/*****GEOCODE_ADDRESS*****/
/*************************/	
if(!empty($origin)){
	$url = "http://maps.googleapis.com/maps/api/geocode/json?address=".$origin."&sensor=false";
	
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    $curlResponse = curl_exec($ch);
    curl_close($ch);
	$jsonDecoded = json_decode($curlResponse);		
	
	$fromAddress =  $jsonDecoded->results[0]->formatted_address;
	$fromLat = $jsonDecoded->results[0]->geometry->location->lat;
	$fromLon = $jsonDecoded->results[0]->geometry->location->lng;
}

/*************************/
/******GAS_PRICE_API******/
/*************************/
if(!empty($fromLat) && !empty($fromLon)){
	$baseurl = "http://api.mygasfeed.com/";
	$gaskey = "kctgqxwomp";
	
	//$baseurl = "http://devapi.mygasfeed.com/";
	//$gaskey = "rfej9napna";
	
	$url = $baseurl."stations/radius/".$fromLat."/".$fromLon."/2/reg/price/".$gaskey.".json";
	
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    $curlResponse = curl_exec($ch);
    curl_close($ch);
	$jsonDecoded = json_decode($curlResponse);
	
	if(!empty($jsonDecoded->stations[0]->reg_price)){
		$gas_price = $jsonDecoded->stations[0]->reg_price;//closest gas station price for Regular gas
	}
	$station_name = $jsonDecoded->stations[0]->station;//closest gas station name
	$station_address = $jsonDecoded->stations[0]->address." ".$jsonDecoded->stations[0]->zip;//closest gas station address
	
	$origin = $fromLat.",".$fromLon;//if LAT/LNG are set set origin to coordinates
}

/************************/
/******CAR_FUEL_API******/
/************************/
if(!empty($make) && !empty($model) && !empty($year) && !empty($origin) && !empty($destination)){
	//PULL STYLE ID BASED ON YEAR, MAKE, MODEL FOR NEXT API CALL
	$filter = "modelyearrepository/foryearmakemodel?make=".$make."&model=".$model."&year=".$year;
	$url = "http://api.edmunds.com/v1/api/vehicle/".$filter."&api_key=$eak&fmt=json";
	
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    $curlResponse = curl_exec($ch);
    curl_close($ch);
	
	$jsonDecoded = json_decode($curlResponse);
				
	$styleId = $jsonDecoded->modelYearHolder[0]->styles[0]->id;
		
	//GET MPG AND TANK_SIZE BASED ON STYLE ID
	$filter = "stylerepository/findbyid?id=".$styleId;
	$url = "http://api.edmunds.com/v1/api/vehicle/".$filter."&api_key=$eak&fmt=json";
	
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    $curlResponse = curl_exec($ch);
    curl_close($ch);
	$jsonDecoded = json_decode($curlResponse);
	
	$mpg = $jsonDecoded->styleHolder[0]->attributeGroups->SPECIFICATIONS->attributes->EPA_COMBINED_MPG->value;
	$tank_size = $jsonDecoded->styleHolder[0]->attributeGroups->SPECIFICATIONS->attributes->FUEL_CAPACITY->value;
	
	//GET DISTANCE FROM CURRENT LOCATION TO ADDRESS INPUT
	$destination = urlencode($destination);
	$url = "http://maps.googleapis.com/maps/api/distancematrix/json?origins=".$origin."&destinations=".$destination."&mode=car&sensor=false&units=imperial";
	
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    $curlResponse = curl_exec($ch);
    curl_close($ch);
	$jsonDecoded = json_decode($curlResponse);
	
	$origin_full = $jsonDecoded->origin_addresses[0];
	$destination_full = $jsonDecoded->destination_addresses[0];
	$distance = $jsonDecoded->rows[0]->elements[0]->distance->value;
	$distance_in_miles = $jsonDecoded->rows[0]->elements[0]->distance->text;
	$duration = $jsonDecoded->rows[0]->elements[0]->duration->text;
	
	/************************/
	/******CALCULATIONS******/
	/************************/
	
	$gas_price = round($gas_price, 2);
	$gas_price = number_format($gas_price, 2, '.', '');
	
	$kpg = 1000*($mpg/0.6215);
	$kpg = round($kpg, 2);
	
	$gallons_required = $distance/$kpg;
	$gallons_required = round($gallons_required, 2);
	
	$gallons_have = $tank_size * $tank_full;
	$gallons_have = round($gallons_have, 2);
	
	$gallons_need = $gallons_required-$gallons_have;
	$gallons_need = round($gallons_need, 2);
	if($gallons_need<0){
		$gallons_need = 0;
	}
	
	$cost = $gallons_need * $gas_price;
	$cost = round($cost, 2);
	$cost = number_format($cost, 2, '.', '');
	$rtCost = $cost*2;
	
	/************************/
	/*********OUTPUT*********/
	/************************/
	
	//OUTPUT CAR FUEL INFO
	$outputHTML .= "<h4>Your Cars Fuel Info:</h4>";
	if(!empty($mpg))
		$outputHTML .= "Estimated Combined MPG: ".$mpg."<br/>";
	else{
		$outputHTML .= "Could not find MPG data for the vehicle you requested<br/>";
		$outputHTML .= "Instead we'll estimate at 17.5MPG<br/>";
		$mpg = 17.5;
	}
	if(!empty($tank_size))
		$outputHTML .= "Fuel Capacity: ".$tank_size."<br/>";
	else{
		$outputHTML .= "Fuel Tank Capacity not found<br/>";
		$outputHTML .= "Instead we'll assume an fuel capacity of 17 gallons<br/>";
		$tank_size=17;
	}
	
	//OUTPUT TRIP INFO
	$outputHTML .= "<h4>Trip Info:</h4>";
	$outputHTML .= "<b>From:</b> ".$origin_full;
	$outputHTML .= "<br/><b>To:</b> ".$destination_full;
	$outputHTML .= "<br/><b>Trip Distance:</b> ".$distance_in_miles;
	$outputHTML .= "<br/><b>Trip Duration:</b> ".$duration;
	
	//OUTPUT COST INFO
	$outputHTML .= "<br/><b>Your trip will take:</b> ".$gallons_required." gallons";
	$outputHTML .= "<br/><b>You have:</b> ".$gallons_have." gallons";
	$outputHTML .= "<br/><b>You need:</b> ".$gallons_need." gallons";
	$outputHTML .= "<br/><b>Gas price:</b> $".$gas_price."/gallon at <a href=\"https://maps.google.com/maps?hl=en&tab=wl&q=".$station_address."\" target=\"_blank\">".$station_name."</a>";
	$outputHTML .= "<br/><b>The trip will cost you:</b> \$".$cost."";
	$outputHTML .= "<br/><b>Round trip will cost you:</b> \$".$rtCost."<br/>";
	$outputHTML .= "<a class=\"button active\" href=\"http://maps.google.com/maps?saddr=".$origin_full."&daddr=".$destination_full."\" target=\"_blank\">View Route on Google Maps</a>";
}

//IF ALL VARIABLES ARE NOT SET
else {
	$outputHTML = "<h4>Sorry</h4>";
	$outputHTML .= "We couldn't get your information. Please reload the page to try again.";
	$outputHTML .= "<h5>Thanks</h5>";
	$outputHTML .= "<em>GCU Team</em>";
}

print $outputHTML;

?>