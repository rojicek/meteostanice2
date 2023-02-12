<!DOCTYPE html>
<html> 

<head>
<title>Meteostanice</title>

<meta charset="UTF-8">
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />

<link rel="stylesheet" type="text/css" href="meteo.css">
<link rel=icon href=favicon.png>

</head>


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
   
   //echo $air_url . "<br>";
   /*
   echo $air_url . "<br>";
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
<table style="air">

<tr>
<td class="air"><b>Celková kvalita vzduchu&nbsp;</b></td>
<td></td>
<td><img src= "img/air_quality/air<?php echo $aqi;?>.png" width=70></td>
<td></td>
<td class="air" style="text-align:right;font-size: 25pt; "><b><center><?php echo $aqi;?>/5</center></b></td> 
<td></td>
</tr>

<tr>
<td colspan=6 style="text-align: right;">Poslední aktualizace: <?php echo date('d.m.Y H:i:s', $air_quality_arr['list'][0]['dt']) ?></td>
</tr>

<tr>
<td colspan=6><hr></td>
</tr>

<tr>
<td class="air"><b>PM 2.5</b><br>polétavý prach 2.5&#181;m</td>
<td></td>
<td><img src= "img/air_quality/air<?php echo $pm2_5_i;?>.png" width=70></td>
<td></td>
<td class="air"><b><?php echo $pm2_5;?> &#181;g/m<sup>3</sup></b><br>
max <?php echo $aqi_limits["pm2_5"][0];?> &#181;g/m<sup>3</sup></td>
</tr>


<tr>
<td class="air"><b>PM 10</b><br>polétavý prach 10&#181;m</td>
<td></td> 
<td><img src= "img/air_quality/air<?php echo $pm10_i;?>.png" width=70></td>
<td></td>
<td class="air"><b><?php echo $pm10;?> &#181;g/m<sup>3</sup></b><br>
max <?php echo $aqi_limits["pm10"][0];?> &#181;g/m<sup>3</sup></td>
</tr>

<tr>
<td class="air"><b>NO<sub>2</sub></b><br>oxid dusičitý</td>
<td></td>
<td><img src= "img/air_quality/air<?php echo $no2_i;?>.png" width=70></td>
<td></td>
<td class="air"><b><?php echo $no2;?> &#181;g/m<sup>3</sup></b><br>
max <?php echo $aqi_limits["no2"][0];?> &#181;g/m<sup>3</sup></td>
</tr>

<tr>
<td class="air"><b>O<sub>3</sub></b><br>ozón</td>
<td></td>
<td><img src= "img/air_quality/air<?php echo $o3_i;?>.png" width=70></td>
<td></td>
<td class="air"><b><?php echo $o3;?> &#181;g/m<sup>3</sup></b><br>
max <?php echo $aqi_limits["o3"][0];?> &#181;g/m<sup>3</sup></td>
</tr>

<tr>
<td colspan=6><hr></td>
</tr>

<tr>
<td class="air"><b>CO</b><br>oxid uhelnatý</td>
<td></td>
<td><img src= "img/air_quality/air<?php echo $co_i;?>.png" width=70></td>
<td></td>
<td class="air"><b><?php echo $co;?> &#181;g/m<sup>3</sup></b><br>
max <?php echo $aqi_limits["co"][0];?> &#181;g/m<sup>3</sup></td>
</tr>


<tr>
<td class="air"><b>NO</b><br>oxid dusnatý</td>
<td></td>
<td><img src= "img/air_quality/air<?php echo $no_i;?>.png" width=70></td>
<td></td>
<td class="air"><b><?php echo $no;?> &#181;g/m<sup>3</sup></b><br>
max <?php echo $aqi_limits["no"][0];?> &#181;g/m<sup>3</sup></td>
</tr>


<tr>
<td class="air"><b>NH<sub>3</sub></b><br>amoniak</td>
<td></td>
<td><img src= "img/air_quality/air<?php echo $nh3_i;?>.png" width=70></td>
<td></td>
<td class="air"><b><?php echo $nh3;?> &#181;g/m<sup>3</sup></b><br>
max <?php echo $aqi_limits["nh3"][0];?> &#181;g/m<sup>3</sup></td>
</tr>


</table>
</body>
</html>