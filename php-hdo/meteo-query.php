<?php
// php ktere vrati kompletni info pro meteostanici

include '/var/www/rojicek.cz/web/db/includedb.php'; 
require_once 'weather_checks.php';


if ($_GET["pwd"] != $pwd)
{
    echo "{\"log\": \"no way jose\"}";
    exit();
}


$datetime_now = time();

//HDO

$date_today = date('Y-m-d', $datetime_now);
$date_tomorrow = date('Y-m-d', $datetime_now + 86400);


$holiday_today_sql = "select count(*) as pocet from holidays_tbl where datum = '" . $date_today . "'";
$holiday_today_q = $databaseConnection -> query($holiday_today_sql);
$row = $holiday_today_q->fetch_row();
$holiday_today = (int)$row[0];
if ($holiday_today == 1)
    $DoWdnes = " 'HOL' ";
else
    $DoWdnes = " ucase(SUBSTRING(DAYNAME(now()),1,3)) ";  


$holiday_tomorrow_sql = "select count(*) as pocet from holidays_tbl where datum = '" . $date_tomorrow . "'";
$holiday_tomorrow_q = $databaseConnection -> query($holiday_tomorrow_sql);
$row = $holiday_tomorrow_q->fetch_row();
$holiday_tomorrow = (int)$row[0];
if ($holiday_tomorrow == 1)
    $DoWzitra = " 'HOL' ";
else
    $DoWzitra = " ucase(SUBSTRING(DAYNAME(DATE_ADD(now(), INTERVAL 1 DAY) ),1,3)) ";  


$selectDNESKA = "select DoW, peakStart, peakStop from hdo_tbl where date(now())>=dateStart AND date(now())<=dateEnd AND " . $DoWdnes . "  = DoW order by timeid";  
$hdoResultsDNESKA  = $databaseConnection->query ($selectDNESKA);
   
$selectZITRA = "select DoW, peakStart, peakStop from hdo_tbl where date(now())>=dateStart AND date(now())<=dateEnd AND " . $DoWzitra . "  = DoW order by timeid";  
$hdoResultsZITRA  = $databaseConnection->query ($selectZITRA);


  
//$hdo_obj = new stdClass();
$hdo_arr = array();
 
  while($row = $hdoResultsDNESKA->fetch_assoc())
  {
     $zacatek = $date_today . " " . $row['peakStart'];
     $konec = $date_today . " " . $row['peakStop'];

     if (strtotime($zacatek) >= $datetime_now)
     {  
        array_push($hdo_arr, array(strtotime($zacatek), strtotime($konec)));
     }        
     elseif (strtotime($konec) >= $datetime_now)
     { 
        //array_push($hdo_arr, array(date('Y-m-d H:i:s', $datetime_now), $konec));
        array_push($hdo_arr, array($datetime_now, strtotime($konec)));
     }
  }
 
  // no need to cf act time - it's tomorrow
  while($row = $hdoResultsZITRA->fetch_assoc())
  {
     $zacatek = $date_tomorrow . " " . $row['peakStart'];
     $konec = $date_tomorrow . " " . $row['peakStop'];
     array_push($hdo_arr, array(strtotime($zacatek), strtotime($konec)));    
  }
 
 //play safe :)
 asort($hdo_arr);
 $hdo_arr_for_json= array();
 $hdo_arr_for_json["hdo_intervals"] = $hdo_arr;
 
 //$hdo_obj->intervals = $hdo_arr;
 
 //////////////////////////////
 // air quality
 $lat = "50.0973722";
 $lon = "14.4074581";
 //$temp_placeholder = 99; //for min/max
  
 $max_time_gap = 7200;
 $current_time = time();
 
 //$openweather_api = 'xxxxx'; //just for testing
 try
 {
  $air_url = "https://api.openweathermap.org/data/2.5/air_pollution?lat=".$lat."&lon=".$lon."&appid=".$openweather_api;  
  $air_content = file_get_contents($air_url);
  $air_weather_arr = array();
    
  // default values  
  $aqi = 0; 
  $sunrise = 0;
  $sunset = 0;
  $temp = 0;
  $temp_feel = 0;
  $wind_speed = -1;
  $wind_deg = -1;
  $w_desc = "NA";
  $w_icon = "";
  
  $temp_trend = 0;
  $hours_ahead = 6; //hours ahead for temp trend
  $cycling_today = 1;
  $cycling_tomorrow = 1;
  
  if ($air_content)
   { //air ok
     $air_quality_arr = json_decode($air_content, true);
      if (array_key_exists("list", $air_quality_arr))
          if ($current_time - $air_quality_arr['list'][0]['dt'] < $max_time_gap)
             $aqi = $air_quality_arr['list'][0]['main']['aqi'];
             
   } //air ok
   $air_weather_arr['weather']['aqi'] = $aqi;
   // konec air quality 
   
   //current weather
   $weather_url = "https://api.openweathermap.org/data/3.0/onecall?lat=".$lat."&lon=".$lon."&exclude=minutely,daily,alerts&appid=".$openweather_api."&units=metric";
   // echo $weather_url . "<p>"; // debug only
   $weather_content = file_get_contents($weather_url);
   if ($weather_content)
   {//weather content
    
    $weather_arr = json_decode($weather_content, true);
    $up_to_date = 0;
    
    //if (array_key_exists("current", $weather_arr)) - test jak to prezije bez kontroly
        if ($current_time - $weather_arr['current']['dt'] < $max_time_gap)
          { //json ok and up to date
             $up_to_date = 1;   
             $sunrise = $weather_arr['current']['sunrise'];
             $sunset  = $weather_arr['current']['sunset'];
             $temp = $weather_arr['current']['temp'];
             $temp_feel = $weather_arr['current']['feels_like'];
             $wind_speed = $weather_arr['current']['wind_speed'];
             $wind_deg = $weather_arr['current']['wind_deg'];
             $w_desc = $weather_arr['current']['weather'][0]['main'];
             $w_icon = $weather_arr['current']['weather'][0]['icon'];
          } //json ok and up to date
          
    // if (array_key_exists("hourly", $weather_arr)) - test jak to prezije bez kontroly
        if ($up_to_date == 1)
            {
              $today_midnight = mktime(0, 0, 0) + 86400;  // today next midnight in epoch
              $tomorrow_midnight =  $today_midnight + 2*86400; // today next midnight in epoch
                            
              for ($i = 0; $i<48; $i++)
                { //for hourly
                
                    //echo date('Y-m-d H:i:s', $weather_arr['hourly'][$i]['dt']) . " -- " . $weather_arr['hourly'][$i]['temp'] . "<br>";
                    //temp trend for next 8 hours, but only today (get extreme). Future only
                    // can be done base on $i, but time is more reliable
                    if (($weather_arr['hourly'][$i]['dt'] > $current_time) and ($weather_arr['hourly'][$i]['dt'] < $current_time + $hours_ahead * 3600 ))                         
                    { //relevant for temp trend  
                        //echo "trend index " . date('Y-m-d H:i:s', $weather_arr['hourly'][$i]['dt']) . " -> " . $weather_arr['hourly'][$i]['temp'] . "<br>";
                        if (abs($weather_arr['hourly'][$i]['temp'] - $temp) > abs($temp_trend))                                                      
                            // care for plus minus!
                            $temp_trend = $weather_arr['hourly'][$i]['temp'] - $temp;
                    } //relevant for temp trend
                    
                    if  ((($weather_arr['hourly'][$i]['dt'] > $current_time)) and ($weather_arr['hourly'][$i]['dt'] < $today_midnight))
                    { //today, future only
                                                     
                        //cycling index - only between sunrise and sunset    
                        if (($weather_arr['hourly'][$i]['dt'] >= $sunrise) and ($weather_arr['hourly'][$i]['dt'] <= $sunset))
                        { //sunrise-sunset for cycling index  
                                //echo "today index  " . date('Y-m-d H:i:s', $weather_arr['hourly'][$i]['dt'])  . "<br>";                              
                                $cycling_today = cycling_index_check ($weather_arr['hourly'][$i]['temp'],       '<', $low_temp, $super_low_temp, $cycling_today); //cold check
                                $cycling_today = cycling_index_check ($weather_arr['hourly'][$i]['temp'],       '>', $hi_temp,  $super_hi_temp, $cycling_today); //hot check
                                $cycling_today = cycling_index_check ($weather_arr['hourly'][$i]['wind_speed'], '>', $hi_wind,  $super_hi_wind, $cycling_today); //wind
                                $cycling_today = cycling_index_check ($weather_arr['hourly'][$i]['rain']['1h'], '>', $rain,  $super_rain, $cycling_today); //rain
                                $cycling_today = cycling_index_check ($weather_arr['hourly'][$i]['snow']['1h'], '>', $snow,  $super_snow, $cycling_today); //snow                                                                                                                                
                        }//sunrise-sunset for cycling index
                    } //today
                                        
                    if  (($weather_arr['hourly'][$i]['dt'] >= $today_midnight) and ($weather_arr['hourly'][$i]['dt'] < $tomorrow_midnight)) 
                    { //tomorrow
                                                
                        //cycling index - only between sunrise and sunset (same sun as today)    
                        if (($weather_arr['hourly'][$i]['dt'] >= $sunrise+86400) and ($weather_arr['hourly'][$i]['dt'] <= $sunset+86400))
                        { //sunrise-sunset for cycling index
                                //echo "tomorrow index " . date('Y-m-d H:i:s', $weather_arr['hourly'][$i]['dt'])  . "<br>";        
                                $cycling_tomorrow = cycling_index_check ($weather_arr['hourly'][$i]['temp'],       '<', $low_temp, $super_low_temp, $cycling_tomorrow); //cold check
                                $cycling_tomorrow = cycling_index_check ($weather_arr['hourly'][$i]['temp'],       '>', $hi_temp,  $super_hi_temp, $cycling_tomorrow); //hot check
                                $cycling_tomorrow = cycling_index_check ($weather_arr['hourly'][$i]['wind_speed'], '>', $hi_wind,  $super_hi_wind, $cycling_tomorrow); //wind
                                $cycling_tomorrow = cycling_index_check ($weather_arr['hourly'][$i]['rain']['1h'], '>', $rain,  $super_rain, $cycling_tomorrow); //rain
                                $cycling_tomorrow = cycling_index_check ($weather_arr['hourly'][$i]['snow']['1h'], '>', $snow,  $super_snow, $cycling_tomorrow); //snow                                                                                                                                      
                        }//sunrise-sunset for cycling index
                    } //tomorrow
    
                } //for hourly                            
            }
                             
   }//weather content
   
   //extra kontroly pro dnesek, jestli uz neni noc
   // air quality is just one (today only). No red cycling condition
   if ($aqi > 3)
      $cycling_today = max($cycling_today, 2);
      
   //too soon too dark       
   if  ($current_time+3600 > $sunset) //hodina do zapadu slunce
      $cycling_today = max($cycling_today, 2);
   if  ($current_time-3600 > $sunset) //hodina po zapadu slunce
      $cycling_today = max($cycling_today, 3);
   
   //build output json
   $air_weather_arr['weather']['sunrise'] = date('H:i', $sunrise);
   $air_weather_arr['weather']['sunset'] = date('H:i', $sunset);
   $air_weather_arr['weather']['temp'] = round($temp,1);
   $air_weather_arr['weather']['temp_feel'] = round($temp_feel,1);
   $air_weather_arr['weather']['wind_speed'] = round($wind_speed,1);
   $air_weather_arr['weather']['wind_dir'] = "NA";
   if (($wind_deg>=337.5) or ($wind_deg<22.5)) 
      $air_weather_arr['weather']['wind_dir'] = "N";
   elseif (($wind_deg>=22.5) and ($wind_deg<67.5)) 
      $air_weather_arr['weather']['wind_dir'] = "NE";
   elseif (($wind_deg>=67.5) and ($wind_deg<112.5)) 
      $air_weather_arr['weather']['wind_dir'] = "E";
   elseif (($wind_deg>=112.5) and ($wind_deg<157.5)) 
      $air_weather_arr['weather']['wind_dir'] = "SE";
   elseif (($wind_deg>=157.5) and ($wind_deg<202.5)) 
      $air_weather_arr['weather']['wind_dir'] = "S";
   elseif (($wind_deg>=202.5) and ($wind_deg<247.5)) 
      $air_weather_arr['weather']['wind_dir'] = "SW";
   elseif (($wind_deg>=247.5) and ($wind_deg<292.5)) 
      $air_weather_arr['weather']['wind_dir'] = "W";
   elseif (($wind_deg>=292.5) and ($wind_deg<337.5)) 
      $air_weather_arr['weather']['wind_dir'] = "NW";
   
   $air_weather_arr['weather']['w_desc'] = $w_desc;
   $air_weather_arr['weather']['w_icon'] = $w_icon;
      
         
   $air_weather_arr['weather']['temp_trend'] = round($temp_trend, 0);
            
   $air_weather_arr['weather']['clc_tdy'] = $cycling_today;
   $air_weather_arr['weather']['clc_tmr'] = $cycling_tomorrow;
   
 }
 catch (Exception $e) {
    echo   $e->getMessage();
}



 $all_arr = array_merge($hdo_arr_for_json, $air_weather_arr);
  
 $export_json = json_encode($all_arr);
  
 
 echo $export_json; 
 
 //echo "<p>konec";
 
   
?>