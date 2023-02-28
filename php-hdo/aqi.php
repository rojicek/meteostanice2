<!DOCTYPE html>
<html> 

<head>
<title>Meteostanice - kvalita vzduchu</title>

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
 
$opts = array (
    'http' => array (
        'method' => 'GET',
        'header'=> "Content-type: application/json; charset=utf-8\r\n"
                 . "x-access-token: " . $golemio_api . "\r\n",
        )
    );

$context  = stream_context_create($opts);
$air_content= file_get_contents($air_url, false, $context);

$air_quality_arr = json_decode($air_content, true);



//echo "<pre>";
//var_dump($air_content);
//echo "</pre>";


$site_properties = get_aq_data($chmu_stanice, $air_quality_arr['features']);
$updated = strtotime($site_properties['updated_at']);
//echo $updated . "<br>";
//echo $site_properties['updated_at'] . " at " .  $site_properties['name'] . "<br>";

$measurements = $site_properties['measurement'];

$aqi = $measurements['AQ_hourly_index'];
$components = $measurements['components'];



/////////////////////////
$no2 = get_component('no2', $components);
$o3  = get_component('o3', $components);
$pm10 = get_component('pm10', $components);
$pm2_5 = get_component('pm2_5', $components);
$so2 = get_component('so2', $components);



$no2_i = match_range($no2, $aqi_limits["no2"]);
$o3_i = match_range($o3, $aqi_limits["o3"]);
$pm2_5_i = match_range($pm2_5, $aqi_limits["pm2_5"]);
$pm10_i = match_range($pm10, $aqi_limits["pm10"]);
$so2_i = match_range($so2, $aqi_limits["so2"]);


/*

echo "o3 = " . $o3 . "<br>";
echo "o3i = " . $o3_i . "<br>";


echo "aqi = " . $aqi . "<br>";
echo "no2 = " . $no2 . "<br>";
echo "so2 = " . $so2 . "<br>";
echo "o3 = " . $o3 . "<br>";
echo "pm10 = " . $pm10 . "<br>";
echo "pm2_5 = " . $pm2_5 . "<br>";

echo "<p>--------------";


echo "<pre>";
var_dump($measurements);
echo "</pre>";

 

echo "<p>konec";
*/
?>



<body>
<table style="air">

<tr>
<td class="air"><b>Celková kvalita vzduchu&nbsp;</b></td>
<td></td>
<td><img src= "img/air_quality/air<?php echo $aqi;?>.png" width=70></td>
<td></td>
<td class="air" style="text-align:right;font-size: 25pt; "><b><center><?php echo $aqi;?></center></b></td> 
<td></td>
</tr>

<tr>
<td colspan=6 style="text-align: right;">Poslední aktualizace: <?php echo date('d.m.Y H:i', $updated) . ", " .  $site_properties['name'] . "<br>"; ?></td>
</tr>

<tr>
<td colspan=6><hr></td>
</tr>

<tr>
<td class="air"><b>PM 2.5</b><br>polétavý prach 2.5&#181;m</td>
<td></td>
<td><img src= "img/air_quality/air<?php echo $pm2_5_i;?>.png" width=70></td>
<td></td>
<td class="air"><b>
<?php 
if ($pm2_5 != "NA")
   echo $pm2_5 . " &#181;g/m<sup>3</sup>";    
?>
</b><br>
max <?php echo $aqi_limits["pm2_5"][0];?> &#181;g/m<sup>3</sup></td>
</tr>


<tr>
<td class="air"><b>PM 10</b><br>polétavý prach 10&#181;m</td>
<td></td> 
<td><img src= "img/air_quality/air<?php echo $pm10_i;?>.png" width=70></td>
<td></td>
<td class="air"><b>
<?php 
if ($pm10 != "NA")
   echo $pm10 . " &#181;g/m<sup>3</sup>";    
?>
</b><br>
max <?php echo $aqi_limits["pm10"][0];?> &#181;g/m<sup>3</sup></td>
</tr>

<tr>
<td class="air"><b>NO<sub>2</sub></b><br>oxid dusičitý</td>
<td></td>
<td><img src= "img/air_quality/air<?php echo $no2_i;?>.png" width=70></td>
<td></td>
<td class="air"><b>
<?php 
if ($no2 != "NA")
   echo $no2 . " &#181;g/m<sup>3</sup>";    
?>
</b><br>
max <?php echo $aqi_limits["no2"][0];?> &#181;g/m<sup>3</sup></td>
</tr>

<tr>
<td class="air"><b>O<sub>3</sub></b><br>ozón</td>
<td></td>
<td><img src= "img/air_quality/air<?php echo $o3_i;?>.png" width=70></td>
<td></td>
<td class="air"><b>
<?php 
if ($o3 != "NA")
   echo $o3 . " &#181;g/m<sup>3</sup>";    
?>
 </b><br>
 
max <?php echo $aqi_limits["o3"][0];?> &#181;g/m<sup>3</sup></td>
</tr>

<tr>
<td class="air"><b>SO<sub>2</sub></b><br>oxid siřičitý</td>
<td></td>
<td><img src= "img/air_quality/air<?php echo $so2_i;?>.png" width=70></td>
<td></td>
<td class="air"><b>
<?php 
if ($so2 != "NA")
   echo $so2 . " &#181;g/m<sup>3</sup>";    
?>
</b><br>
max <?php echo $aqi_limits["so2"][0];?> &#181;g/m<sup>3</sup></td>
</tr>


<tr>
<td colspan=6><hr></td>
</tr>




</table>


</body>
</html>

