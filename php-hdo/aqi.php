<?php
// php ktere vrati air quality
 

require_once 'constants.php';
require_once 'utils1.php';


 
 //////////////////////////////
 // air quality
 
 
 //$openweather_api = 'xxxxx'; //just for testing

$air_url = "https://api.openweathermap.org/data/2.5/air_pollution?lat=".$lat."&lon=".$lon."&appid=".$openweather_api;  
$air_content = file_get_contents($air_url);


if ($air_content)
 { //air ok
   $air_quality_arr = json_decode($air_content, true);
   
   var_dump($air_quality_arr);      
           
 } //air ok

 
    
 echo "<p>konec";
 
   
?>