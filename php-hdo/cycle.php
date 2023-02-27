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


$datetime_now = time();
$cycling_today_temp = 1;
$cycling_today_rain = 1;
$cycling_today_snow = 1;
$cycling_today_wind = 1;
$cycling_today_sun = 1;
$cycling_today_aqi = 1;

$cycling_tomorrow_temp = 1;
$cycling_tomorrow_rain = 1;
$cycling_tomorrow_snow = 1;
$cycling_tomorrow_wind = 1;


$cycling_today_total = 1;
$cycling_tomorrow_total = 1;
    
$current_time = time();
$month_today =  intval(date('n', $datetime_now));
$month_tomorrow = intval(date('n', $datetime_now + 86400));

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
                                                                                    
                  //echo "tomorrow index  " . date('Y-m-d H:i:s', $weather_arr['hourly'][$i]['dt'])  . " > " . $weather_arr['hourly'][$i]['temp'] ." >" . $cycling_tomorrow_temp . "<br>";                                                                                                                     
              } //tomorrow

          } //for hourly                            
      

         
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


<table style="air">



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
<td><b><?php echo $cycling_today_total; ?></b></td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td>
<img src="img/cycle/cycle_<?php echo $cycling_tomorrow_total ?>.png"  style=vertical-align:middle width=70>
</td>
<td><b><?php echo $cycling_tomorrow_total; ?></b></td> 
</tr>

<tr>
<td colspan=6><hr style="height:3px;background-color:black;width=100%;"></td>
</tr>

<td>Teplota</td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td>
<img src="img/cycle/cycle_<?php echo $cycling_today_temp ?>.png"  style=vertical-align:middle width=70>
</td>
<td><b><?php echo $cycling_today_temp; ?></b></td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td>
<img src="img/cycle/cycle_<?php echo $cycling_tomorrow_temp ?>.png"  style=vertical-align:middle width=70>
</td>
<td><b><?php echo $cycling_tomorrow_temp; ?></b></td> 
</tr>

<tr>
<td>Vítr</td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td>
<img src="img/cycle/cycle_<?php echo $cycling_today_wind ?>.png"  style=vertical-align:middle width=70>
</td>
<td><b><?php echo $cycling_today_wind; ?></b></td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td>
<img src="img/cycle/cycle_<?php echo $cycling_tomorrow_wind ?>.png"  style=vertical-align:middle width=70>
</td>
<td><b><?php echo $cycling_tomorrow_wind; ?></b></td> 
</tr>


<tr>
<td>Déšť</td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td>
<img src="img/cycle/cycle_<?php echo $cycling_today_rain ?>.png"  style=vertical-align:middle width=70>
</td>
<td><b><?php echo $cycling_today_rain; ?></b></td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td>
<img src="img/cycle/cycle_<?php echo $cycling_tomorrow_rain ?>.png"  style=vertical-align:middle width=70>
</td>
<td><b><?php echo $cycling_tomorrow_rain; ?></b></td> 
</tr>

<tr>
<td>Sníh</td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td>
<img src="img/cycle/cycle_<?php echo $cycling_today_snow ?>.png"  style=vertical-align:middle width=70>
</td>
<td><b><?php echo $cycling_today_snow; ?></b></td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td>
<img src="img/cycle/cycle_<?php echo $cycling_tomorrow_snow ?>.png"  style=vertical-align:middle width=70>
</td>
<td><b><?php echo $cycling_tomorrow_snow; ?></b></td> 
</tr>


<tr>
<td>Vzduch</td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td>
<img src="img/cycle/cycle_<?php echo $cycling_today_aqi ?>.png"  style=vertical-align:middle width=70>
</td>
<td><b><?php echo $cycling_today_aqi; ?></b></td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td></td>
<td></td> 
</tr>

<tr>
<td>Světlo</td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td>
<img src="img/cycle/cycle_<?php echo $cycling_today_sun ?>.png"  style=vertical-align:middle width=70>
</td>
<td><b><?php echo $cycling_today_sun; ?></b></td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td></td>
<td></td> 
</tr>

</table>

</body>
</html>