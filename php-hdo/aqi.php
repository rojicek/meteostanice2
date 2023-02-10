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
   
   $co_i = match_range($co, $aqi_limits["co"]);
   $no_i = match_range($no, $aqi_limits["no"]);
   $no2_i = match_range($no2, $aqi_limits["no2"]);
   $o3_i = match_range($o3, $aqi_limits["o3"]);
   $pm2_5_i = match_range($pm2_5, $aqi_limits["pm2_5"]);
   $pm10_i = match_range($pm10, $aqi_limits["pm10"]);
   $nh3_i = match_range($nh3, $aqi_limits["nh3"]);
   
   /*
   echo "aqi: " .$aqi . "<br>";
   echo "co: " . $co . " - " . $co_i . "<br>";       
   echo "no: " .$no . " - " . $no_i . "<br>";   
   echo "no2: " .$no2 . " - " . $no2_i . "<br>";    
   echo "o3: " .$o3 . " - " . $o3_i . "<br>";    
   echo "pm2_5: " .$pm2_5 . " - " . $pm2_5_i . "<br>";    
   echo "pm10: " .$pm10 . " - " . $pm10_i . "<br>";   
   echo "nh3: " .$nh3 . " - " . $nh3_i . "<br>";    
     */       
 } //air ok

 
    
 //echo "<p>konec";
 
   
?>

<body>
<table>

<tr>
<td> Air </td>
<td><img src= "img/air_quality/air<?php echo $aqi;?>.png" width=70></td>
<td><?php echo $aqi;?></td> 
</tr>

<tr>
<td> CO </td>
<td><img src= "img/air_quality/air<?php echo $co_i;?>.png" width=70></td>
<td><?php echo $co;?></td>
</tr>

<tr>
<td> NO </td>
<td><img src= "img/air_quality/air<?php echo $no_i;?>.png" width=70></td>
<td><?php echo $no;?></td>
</tr>

<tr>
<td> NO2 </td>
<td><img src= "img/air_quality/air<?php echo $no2_i;?>.png" width=70></td>
<td><?php echo $no2;?></td>
</tr>

<tr>
<td> O3 </td>
<td><img src= "img/air_quality/air<?php echo $o3_i;?>.png" width=70></td>
<td><?php echo $o3;?></td>
</tr>

<tr>
<td> PM 2.5 </td>
<td><img src= "img/air_quality/air<?php echo $pm2_5_i;?>.png" width=70></td>
<td><?php echo $pm2_5;?></td>
</tr>

<tr>
<td> PM 10 </td>
<td><img src= "img/air_quality/air<?php echo $pm10_i;?>.png" width=70></td>
<td><?php echo $pm10;?></td>
</tr>

<tr>
<td> NH3 </td>
<td><img src= "img/air_quality/air<?php echo $nh3_i;?>.png" width=70></td>
<td><?php echo $nh3;?></td>
</tr>

</table>
</body>