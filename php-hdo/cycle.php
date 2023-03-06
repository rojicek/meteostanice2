<!DOCTYPE html>
<html> 

<head>
<title>Meteostanice - cykloindex</title>

<meta charset="UTF-8">
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />

<link rel="stylesheet" type="text/css" href="meteo.css">
<link rel=icon href=favicon.png>

</head>
<body>


<?php
// php ktere vrati kompletni info pro meteostanici

require_once '/var/www/rojicek.cz/web/db/includedb.php'; 

require_once 'constants.php';
require_once 'utils1.php';


//indexy pro ikony
$datetime_now = time();
$cycling_today_temp = 1;
$cycling_today_rain = 1;
$cycling_today_snow = 1;
$cycling_today_wind = 1;
$cycling_today_sun = 1;
$cycling_today_aqi = 1;

$today_in = 0;
$cycling_tomorrow_temp = 1;
$cycling_tomorrow_rain = 1;
$cycling_tomorrow_snow = 1;
$cycling_tomorrow_wind = 1;


$cycling_today_total = 1;
$cycling_tomorrow_total = 1;
    
$current_time = time();
$month_today =  intval(date('n', $datetime_now));
$month_tomorrow = intval(date('n', $datetime_now + 86400));

//rain/snow - pokud neexistuje, tak zustane nula (proto nedelam init -99)
$temp_min_today = 99;
$temp_max_today = -99;
$wind_max_today = -99;
$rain_max_today = 0;
$snow_max_today = 0;

//pokud pouziju, tak prepisu
$temp_now = -99;
$wind_now = -99;
$rain_now = 0;
$snow_now = 0;


$temp_min_tomorrow = 99;
$temp_max_tomorrow = -99;
$wind_max_tomorrow = -99;
$rain_max_tomorrow = 0;
$snow_max_tomorrow = 0;

   //current weather
   $weather_url = $openweather_url . "?lat=".$lat."&lon=".$lon."&exclude=minutely,daily,alerts&appid=".$openweather_api."&units=metric";
   //echo $weather_url . "<p>"; // debug only
   $weather_content = file_get_contents($weather_url);
   
   if ($weather_content)
   {//weather content
        
    $weather_arr = json_decode($weather_content, true);
    $sunrise = $weather_arr['current']['sunrise'];
    $sunset  = $weather_arr['current']['sunset'];
    
    
   
                      
                      
        for ($i = 0; $i<48; $i++)
          { //for hourly
                                                                  
              if  ((($weather_arr['hourly'][$i]['dt'] > $current_time)) and ($weather_arr['hourly'][$i]['dt'] < $sunset+3600))
              { //today, future only until sunset + 1h
                                                                                                                                                    
                  $cycling_today_temp = cycling_index_check ($weather_arr['hourly'][$i]['temp'],       '<', $temp_limits[$month_today][1], $temp_limits[$month_today][0], $cycling_today_temp); //cold check
                  $cycling_today_temp = cycling_index_check ($weather_arr['hourly'][$i]['temp'],       '>', $temp_limits[$month_today][2],  $temp_limits[$month_today][3], $cycling_today_temp); //hot check
                  $cycling_today_wind = cycling_index_check ($weather_arr['hourly'][$i]['wind_speed'], '>', $hi_wind,  $super_hi_wind, $cycling_today_wind); //wind
                  $cycling_today_rain = cycling_index_check ($weather_arr['hourly'][$i]['rain']['1h'], '>', $rain,  $super_rain, $cycling_today_rain); //rain
                  $cycling_today_snow = cycling_index_check ($weather_arr['hourly'][$i]['snow']['1h'], '>', $snow,  $super_snow, $cycling_today_snow); //snow
                  
                  $temp_min_today = min ($temp_min_today, $weather_arr['hourly'][$i]['temp']);
                  $temp_max_today = max ($temp_max_today, $weather_arr['hourly'][$i]['temp']);
                  $wind_max_today = max ($wind_max_today, $weather_arr['hourly'][$i]['wind_speed']);
                  $rain_max_today = max ($rain_max_today, $weather_arr['hourly'][$i]['rain']['1h']);
                  $snow_max_today = max ($snow_max_today, $weather_arr['hourly'][$i]['snow']['1h']);
                  
                  
                  //$today_in = 1; //dnesek je jeste relevantni                                                              
                  //echo "today index  " . date('Y-m-d H:i:s', $weather_arr['hourly'][$i]['dt'])  . " > " . $weather_arr['hourly'][$i]['temp'] ." >" . $cycling_today_temp . "<br>";                  
                                                                                                                                         
              } //today
                                  
                          
              //cycling index - only between sunrise and sunset (same sun as today)    
              if (($weather_arr['hourly'][$i]['dt'] >= $sunrise+86400) and ($weather_arr['hourly'][$i]['dt'] <= $sunset+86400))
              { //sunrise-sunset for cycling index tomorrow
                  //echo "tomorrow index " . date('Y-m-d H:i:s', $weather_arr['hourly'][$i]['dt'])  .  " - ".$weather_arr['hourly'][$i]['temp']. " (". $temp_limits[$month_tomorrow][2]. ")<br>";        
                  $cycling_tomorrow_temp = cycling_index_check ($weather_arr['hourly'][$i]['temp'],       '<', $temp_limits[$month_tomorrow][1], $temp_limits[$month_tomorrow][0], $cycling_tomorrow_temp); //cold check
                  $cycling_tomorrow_temp = cycling_index_check ($weather_arr['hourly'][$i]['temp'],       '>', $temp_limits[$month_tomorrow][2],  $temp_limits[$month_tomorrow][3], $cycling_tomorrow_temp); //hot check
                  $cycling_tomorrow_wind = cycling_index_check ($weather_arr['hourly'][$i]['wind_speed'], '>', $hi_wind,  $super_hi_wind, $cycling_tomorrow_wind); //wind
                  $cycling_tomorrow_rain = cycling_index_check ($weather_arr['hourly'][$i]['rain']['1h'], '>', $rain,  $super_rain, $cycling_tomorrow_rain); //rain
                  $cycling_tomorrow_snow = cycling_index_check ($weather_arr['hourly'][$i]['snow']['1h'], '>', $snow,  $super_snow, $cycling_tomorrow_snow); //snow 
                  
                  //min/max zalezi na poradi!
                  $temp_min_tomorrow = min ($temp_min_tomorrow, $weather_arr['hourly'][$i]['temp']);
                  $temp_max_tomorrow = max ($temp_max_tomorrow, $weather_arr['hourly'][$i]['temp']);
                  $wind_max_tomorrow = max ($wind_max_tomorrow, $weather_arr['hourly'][$i]['wind_speed']);
                  $rain_max_tomorrow = max ($rain_max_tomorrow, $weather_arr['hourly'][$i]['rain']['1h']);
                  $snow_max_tomorrow = max ($snow_max_tomorrow, $weather_arr['hourly'][$i]['snow']['1h']);
                                                                                    
                  //echo "tomorrow index  " . date('Y-m-d H:i:s', $weather_arr['hourly'][$i]['dt'])  . " > " . $weather_arr['hourly'][$i]['temp'] ." >" . $cycling_tomorrow_temp . "<br>";                                                                                                                     
              } //tomorrow

          } //for hourly                            
     
      
    if ($today_in == 0)
    { 
     //po zapadu - vypisu jen aktualni hodnoty (at to neni prazdna tabulka)
     //musim spocitat i today index
     //min/max zalezi na poradi!
        $temp_now = $weather_arr['hourly'][0]['temp'];
        $wind_now = $weather_arr['hourly'][0]['wind'];                
        $rain_now = max(0, $weather_arr['hourly'][0]['rain']['1h']); //pokud neex tak zustane nula
        $snow_now = max(0, $weather_arr['hourly'][0]['snow']['1h']);         
        
        $cycling_today_temp = cycling_index_check ($temp_now, '<', $temp_limits[$month_today][1], $temp_limits[$month_today][0], $cycling_today_temp); //cold check
        $cycling_today_temp = cycling_index_check ($temp_now, '>', $temp_limits[$month_today][2],  $temp_limits[$month_today][3], $cycling_today_temp); //hot check
        
        $cycling_today_wind = cycling_index_check ($wind_now, '>', $hi_wind,  $super_hi_wind, $cycling_today_wind); //wind
        $cycling_today_rain = cycling_index_check ($rain_now, '>', $rain,  $super_rain, $cycling_today_rain); //rain
        $cycling_today_snow = cycling_index_check ($snow_now, '>', $snow,  $super_snow, $cycling_today_snow); //snow
                 
    }
         
   //extra kontroly pro dnesek, jestli uz neni noc
   //too early today
   if  ($current_time < $sunrise-3600) //hodina pred vychodem           
       $cycling_today_sun = max($cycling_today_sun, 3);
       
   if  ($current_time < $sunrise) // pred vychodem
      $cycling_today_sun = max($cycling_today_sun, 2);   
   //too soon too dark       
   if  ($current_time > $sunset-3600) //hodina pred zapadem slunce
      $cycling_today_sun = max($cycling_today_sun, 2);
   if  ($current_time > $sunset+3600) //hodina po zapadu slunce
      $cycling_today_sun = max($cycling_today_sun, 3);

  // cas do slunce
  //umyslne opakuji ify
  $sun_seconds = 0; //default
  if ($current_time < $sunrise)
  {
    $cycling_sun_sign = "sunrise"; 
    $sun_seconds = $sunrise - $current_time;   
  } 
  elseif ($current_time < $sunset) 
  {
     $cycling_sun_sign = "sunset";
     $sun_seconds = $sunset - $current_time;       
  }
  else
  { //poz apadu
    $cycling_sun_sign = "sunrise"; //dalsi den (trochu nepresne, protoze dnesni vychad, ale to je jedno)
    $sun_seconds = 86400-($current_time - $sunrise);
  }
         
    
   $cycling_sun = sprintf('%d:%02d', ($sun_seconds/ 3600),($sun_seconds/ 60 % 60));
                           
 }//weather content
 
 
 //air quality
 $opts = array (
      'http' => array (
          'method' => 'GET',
          'header'=> "Content-type: application/json; charset=utf-8\r\n"
                   . "x-access-token: " . $golemio_api . "\r\n",
          )
      );
  
  $context  = stream_context_create($opts);
  $air_content= file_get_contents($air_url, false, $context);
    
  $air_weather_arr = array();
  $aqi = 0; 
  
  if ($air_content)
   { //air ok
     $air_quality_arr = json_decode($air_content, true);
     
     $site_properties = get_aq_data($chmu_stanice, $air_quality_arr['features']);
     $measurements = $site_properties['measurement'];

     $aqi = $measurements['AQ_hourly_index'];
     
     // air quality is just one (today only). (options 1A/1B ... 3A/3B) 
     if ($aqi[0] == 2)
        $cycling_today_aqi = max($cycling_today_aqi, 2);
        
     if ($aqi[0] == 3)
        $cycling_today_aqi = max($cycling_today_aqi, 3);
                        
   } //air ok
   
   

//total values
//overall
    $cycling_today_total = max($cycling_today_temp, $cycling_today_wind, $cycling_today_rain,  $cycling_today_snow, $cycling_today_aqi, $cycling_today_sun);
    $cycling_tomorrow_total = max($cycling_tomorrow_temp, $cycling_tomorrow_wind, $cycling_tomorrow_rain,  $cycling_tomorrow_snow);
    
  // default values  


/*
echo "today<br>";
echo "temp " . $cycling_today_temp . "<br>";
echo "wind " . $cycling_today_wind . "<br>";
echo "rain " . $cycling_today_rain . "<br>";
echo "snow " . $cycling_today_snow . "<br>";
echo "sun " . $cycling_today_sun . "<br>";
echo "aqi " . $cycling_today_aqi . "<br>";



echo "<p>tomorrow<br>";
echo "temp " . $cycling_tomorrow_temp . "<br>";
echo "wind " . $cycling_tomorrow_wind . "<br>";
echo "rain " . $cycling_tomorrow_rain . "<br>";
echo "snow " . $cycling_tomorrow_snow . "<br>";
*/


?>


<table style="cycle">



<tr>
<td colspan=2></td>


<td colspan=2 style="text-align: center;">
<b>Dneska</b>
</td>


<td>&nbsp;&nbsp;&nbsp;</td>

<td colspan=2 style="text-align: center;">
<b>Zítra</b>
</td>
</tr>


<tr>
<td>Celkově</td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td>
<img src="img/cycle/cycle_<?php echo $cycling_today_total ?>.png"  style=vertical-align:middle width=70>
</td>
<td><b><center><?php echo $cycling_today_total; ?></center></b></td>

<td>&nbsp;&nbsp;&nbsp;</td>

<td>
<img src="img/cycle/cycle_<?php echo $cycling_tomorrow_total ?>.png"  style=vertical-align:middle width=70>
</td>
<td><b><center><?php echo $cycling_tomorrow_total; ?></center></b></td> 
</tr>

<tr>
<td colspan=7><hr style="height:3px;background-color:black;width=100%;"></td>
</tr>

<tr>
<td>Teplota</td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td>
<img src="img/cycle/cycle_<?php echo $cycling_today_temp ?>.png"  style=vertical-align:middle width=70>
</td>
<td>
<div class="cycle_key"><?php echo $cycling_today_temp; ?></div>
<br>
<?php if ($today_in == 1) { ?>
<div class="cycle">&nbsp;<?php echo round($temp_min_today, 0) . "&degC / " . round($temp_max_today,0) . "&degC"; ?></div>
<?php } else { ?>
<div class="cycle">&nbsp;<?php echo round($temp_now, 0) . "&degC"; ?></div>
<?php } ?>
</td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td>
<img src="img/cycle/cycle_<?php echo $cycling_tomorrow_temp ?>.png"  style=vertical-align:middle width=70>
</td>

<td>
<div class="cycle_key"><?php echo $cycling_tomorrow_temp; ?></div>
<br>
<div class="cycle">&nbsp;<?php echo round($temp_min_tomorrow,0) . "&degC / " . round($temp_max_tomorrow,0) . "&degC"; ?></div>
</td> 

</tr>

<tr>
<td colspan=7><hr style="height:1px;background-color:black;width=100%;"></td>
</tr>


<tr>
<td>Vítr</td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td>
<?php if ($today_in == 1) { ?>
<img src="img/cycle/cycle_<?php echo $cycling_today_wind ?>.png"  style=vertical-align:middle width=70>
<?php } ?>
</td>
<td>
<?php if ($today_in == 1) { ?>
<div class="cycle_key"><?php echo $cycling_today_wind; ?></div>
<br>
<div class="cycle"><?php echo round($wind_max_today,1) . "m/s"; ?></div>
<?php } ?>
</td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td>
<img src="img/cycle/cycle_<?php echo $cycling_tomorrow_wind ?>.png"  style=vertical-align:middle width=70>
</td>
<td>
<div class="cycle_key"><?php echo $cycling_tomorrow_wind; ?></div>
<br>
<div class="cycle"><?php echo round($wind_max_tomorrow,1) . "m/s"; ?></div>
</td> 
</tr>

<tr>
<td colspan=7><hr style="height:1px;background-color:black;width=100%;"></td>
</tr>

<tr>
<td>Déšť</td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td>
<?php if ($today_in == 1) { ?>
<img src="img/cycle/cycle_<?php echo $cycling_today_rain ?>.png"  style=vertical-align:middle width=70>
<?php } ?>
</td>
<td>
<?php if ($today_in == 1) { ?>
<div class="cycle_key"><?php echo $cycling_today_rain; ?></div>
<br>
<div class="cycle"><?php echo round($rain_max_today,0) . "mm/h"; ?></div>
<?php } ?>
</td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td>
<img src="img/cycle/cycle_<?php echo $cycling_tomorrow_rain ?>.png"  style=vertical-align:middle width=70>
</td>
<td>
<div class="cycle_key"><?php echo $cycling_tomorrow_rain; ?></div>
<br>
<div class="cycle"><?php echo round($rain_max_tomorrow,0) . "mm/h"; ?></div>
</td> 
</tr>

<tr>
<td colspan=7><hr style="height:1px;background-color:black;width=100%;"></td>
</tr>

<tr>
<td>Sníh</td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td>
<?php if ($today_in == 1) { ?>
<img src="img/cycle/cycle_<?php echo $cycling_today_snow ?>.png"  style=vertical-align:middle width=70>
<?php } ?>
</td>
<td>
<?php if ($today_in == 1) { ?>
<div class="cycle_key"><?php echo $cycling_today_snow; ?></div>
<br>
<div class="cycle"><?php echo round($snow_max_today,0) . "mm/h"; ?></div>
<?php } ?>
</td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td>
<img src="img/cycle/cycle_<?php echo $cycling_tomorrow_snow ?>.png"  style=vertical-align:middle width=70>
</td>
<td>
<div class="cycle_key"><?php echo $cycling_tomorrow_snow; ?></div>
<br>
<div class="cycle"><?php echo round($snow_max_tomorrow,0) . "mm/h"; ?></div>
</td> 
</tr>

<tr>
<td colspan=7><hr style="height:1px;background-color:black;width=100%;"></td>
</tr>

<tr>
<td>Vzduch</td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td>
<img src="img/cycle/cycle_<?php echo $cycling_today_aqi ?>.png"  style=vertical-align:middle width=70>
</td>
<td>
<div class="cycle_key"><?php echo $cycling_today_aqi; ?></div><br>
<div class="cycle"><?php echo $aqi; ?></div>
</td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td></td>
<td></td> 
</tr>

<tr>
<td colspan=7><hr style="height:1px;background-color:black;width=100%;"></td>
</tr>

<tr>
<td>Světlo</td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td>
<img src="img/cycle/cycle_<?php echo $cycling_today_sun ?>.png"  style=vertical-align:middle width=70>
</td>
<td>
<div class="cycle_key"><?php echo $cycling_today_sun; ?></div>
<br>
<div class="cycle">
<img src = "img/sun/<?php echo $cycling_sun_sign; ?>.png" width=45 style="vertical-align:middle"><?php echo $cycling_sun; ?>
</div>
</td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td></td>
<td></td> 
</tr>

</table>

</body>
</html>




