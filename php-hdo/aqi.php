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
   
   $aqi = $air_quality_arr['list'][0]['main']['aqi'];
   $co = $air_quality_arr['list'][0]['components']['co'];
   $no = $air_quality_arr['list'][0]['components']['no'];
   $no2 = $air_quality_arr['list'][0]['components']['no2'];
   $o3 = $air_quality_arr['list'][0]['components']['o3'];
   $so2 = $air_quality_arr['list'][0]['components']['so2'];
   $pm2_5 = $air_quality_arr['list'][0]['components']['pm2_5'];
   $pm10 = $air_quality_arr['list'][0]['components']['pm10'];
   $nh3 = $air_quality_arr['list'][0]['components']['nh3'];
   
   echo "aqi: " .$aqi . "<br>";
   echo "co: " .$co . "<br>";       
   echo "no: " .$no . "<br>";
   echo "no2: " .$no2 . "<br>";
   echo "o3: " .$o3 . "<br>";
   echo "pm2_5: " .$pm2_5 . "<br>";
   echo "pm10: " .$pm10 . "<br>";
   echo "nh3: " .$nh3 . "<br>";
   
           
 } //air ok

 
    
 echo "<p>konec";
 
   
?>