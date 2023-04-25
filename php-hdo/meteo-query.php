<?php
// php ktere vrati kompletni info pro meteostanici

require_once '/var/www/rojicek.cz/web/db/includedb.php'; 

require_once 'constants.php';
require_once 'utils1.php';


if ($_GET["pwd"] != $pwd)
{
    echo "{\"log\": \"no way jose\"}";
    exit();
}


$datetime_now = time();

//HDO

$date_today = date('Y-m-d', $datetime_now);
$month_today =  intval(date('n', $datetime_now));


$date_tomorrow = date('Y-m-d', $datetime_now + 86400);
$month_tomorrow = intval(date('n', $datetime_now + 86400));


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
   
$selectZITRA = "select DoW, peakStart, peakStop from hdo_tbl where date(now() + interval 1 day)>=dateStart AND date(now()+ interval 1 day)<=dateEnd AND " . $DoWzitra . "  = DoW order by timeid"; 
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
  
 $max_time_gap = 7200;
 $current_time = time();
 
 //$openweather_api = 'xxxxx'; //just for testing
 try
 {
  //air quality from golemio
    
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
     
     $site_properties = get_aq_data($chmu_stanice, $air_quality_arr['features']);
     $measurements = $site_properties['measurement'];

     $aqi = $measurements['AQ_hourly_index'];
           
             
   } //air ok
   $air_weather_arr['weather']['aqi'] = $aqi;
   // konec air quality 
   
   //current weather
   $weather_url = $openweather_url . "?lat=".$lat."&lon=".$lon."&exclude=minutely,daily,alerts&appid=".$openweather_api."&units=metric";
   //echo $weather_url . "<p>"; // debug only
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
             
             $w_rain = 0;
             $w_snow = 0;
             
             if (array_key_exists("rain", $weather_arr["current"]))             
               if (array_key_exists("1h", $weather_arr["current"]["rain"]))
                    $w_rain = $weather_arr['current']['rain']['1h'];
                    
             if (array_key_exists("snow", $weather_arr["current"]))             
               if (array_key_exists("1h", $weather_arr["current"]["snow"]))
                    $w_snow = $weather_arr['current']['snow']['1h'];   
                         
          } //json ok and up to date
          
    // if (array_key_exists("hourly", $weather_arr)) - test jak to prezije bez kontroly
        if ($up_to_date == 1)
            {
              // no longer needed
              //$today_midnight = mktime(0, 0, 0) + 86400;  // today next midnight in epoch
              //$tomorrow_midnight =  $today_midnight + 2*86400; // today next midnight in epoch
                            
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
                    
                    // don't care for time before 8am (even if sun rises earlier), start at 8am
                    $cycling_time_start_today = max($current_time, strtotime('midnight')+8*3600);
                    // end: 1hr after sunset or 20:001
                    $cycling_time_end_today = min($sunset+3600, strtotime('midnight')+20*3600);
                                                                                                    
                    if  ((($weather_arr['hourly'][$i]['dt'] > $cycling_time_start_today)) and ($weather_arr['hourly'][$i]['dt'] < $cycling_time_end_today))
                    { //today, future only until sunset + 1h                                               
                        //echo "today index  " . date('Y-m-d H:i:s', $weather_arr['hourly'][$i]['dt'])  . "<br>";                              
                        $cycling_today = cycling_index_check ($weather_arr['hourly'][$i]['temp'],       '<', $temp_limits[$month_today][1], $temp_limits[$month_today][0], $cycling_today); //cold check
                        $cycling_today = cycling_index_check ($weather_arr['hourly'][$i]['temp'],       '>', $temp_limits[$month_today][2],  $temp_limits[$month_today][3], $cycling_today); //hot check
                        $cycling_today = cycling_index_check ($weather_arr['hourly'][$i]['wind_speed'], '>', $hi_wind,  $super_hi_wind, $cycling_today); //wind
                        $cycling_today = cycling_index_check ($weather_arr['hourly'][$i]['rain']['1h'], '>', $rain,  $super_rain, $cycling_today); //rain
                        $cycling_today = cycling_index_check ($weather_arr['hourly'][$i]['snow']['1h'], '>', $snow,  $super_snow, $cycling_today); //snow  
                        

                                                                                                                             
                    } //today
                    
                    // don't care for time before 8am (even if sun rises earlier), start at 8am
                    $cycling_time_start_tomorrow = max($sunrise + 86400, strtotime('midnight')+ 8*3600 + 86400);
                    // end: 1hr after sunset or 20:001
                    $cycling_time_end_tomorrow = min($sunset + 86400 + 3600, strtotime('midnight')+ 20*3600 + 86400);
                                                            
                                
                    //cycling index - only between sunrise and sunset (same sun as today)    
                    if (($weather_arr['hourly'][$i]['dt'] >= $cycling_time_start_tomorrow) and ($weather_arr['hourly'][$i]['dt'] <= $cycling_time_end_tomorrow))
                    { //sunrise-sunset for cycling index tomorrow
                        //echo "tomorrow index " . date('Y-m-d H:i:s', $weather_arr['hourly'][$i]['dt'])  .  " - ".$weather_arr['hourly'][$i]['temp']. " (". $temp_limits[$month_tomorrow][2]. ")<br>";        
                        $cycling_tomorrow = cycling_index_check ($weather_arr['hourly'][$i]['temp'],       '<', $temp_limits[$month_tomorrow][1], $temp_limits[$month_tomorrow][0], $cycling_tomorrow); //cold check
                        $cycling_tomorrow = cycling_index_check ($weather_arr['hourly'][$i]['temp'],       '>', $temp_limits[$month_tomorrow][2],  $temp_limits[$month_tomorrow][3], $cycling_tomorrow); //hot check
                        $cycling_tomorrow = cycling_index_check ($weather_arr['hourly'][$i]['wind_speed'], '>', $hi_wind,  $super_hi_wind, $cycling_tomorrow); //wind
                        $cycling_tomorrow = cycling_index_check ($weather_arr['hourly'][$i]['rain']['1h'], '>', $rain,  $super_rain, $cycling_tomorrow); //rain
                        $cycling_tomorrow = cycling_index_check ($weather_arr['hourly'][$i]['snow']['1h'], '>', $snow,  $super_snow, $cycling_tomorrow); //snow                                                                                                                                      
                    } //tomorrow
    
                } //for hourly                            
            }
                             
   }//weather content
   
   //extra kontroly pro dnesek, jestli uz neni noc
   // air quality is just one (today only). (options 1A/1B ... 3A/3B)   
   if ($aqi[0] == 2)
      $cycling_today = max($cycling_today, 2);
   if ($aqi[0] == 3)
      $cycling_today = max($cycling_today, 3);
   
   //too early today
   if  ($current_time < $sunrise-3600) //hodina pred vychodem
      $cycling_today = max($cycling_today, 3);
   if  ($current_time < $sunrise) // pred vychodem
      $cycling_today = max($cycling_today, 2);   
   //too soon too dark       
   if  ($current_time > $sunset-3600) //hodina pred zapadem slunce
      $cycling_today = max($cycling_today, 2);
   if  ($current_time > $sunset+3600) //hodina po zapadu slunce
      $cycling_today = max($cycling_today, 3);
   
   //build output json
   $air_weather_arr['weather']['sunrise'] = $sunrise; //date('H:i', $sunrise);
   $air_weather_arr['weather']['sunset'] = $sunset; //date('H:i', $sunset);
   
   $air_weather_arr['weather']['rain'] = $w_rain;
   $air_weather_arr['weather']['snow'] = $w_snow;  
      
   $air_weather_arr['weather']['temp'] = round($temp,0);
   $air_weather_arr['weather']['temp_feel'] = round($temp_feel,0);
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
   
   $temp_trend_icon = "?"; //default - should be overwritten
   if (abs($temp_trend) <= $small_temp_diff)
    $temp_trend_icon = "trend_steady";
   elseif (($temp_trend > $small_temp_diff) and ($temp_trend <= $big_temp_diff))
    $temp_trend_icon = "trend_small_up";
   elseif ($temp_trend > $big_temp_diff)
    $temp_trend_icon = "trend_up";
   elseif (($temp_trend < -$small_temp_diff) and ($temp_trend >= -$big_temp_diff))
    $temp_trend_icon = "trend_small_down";
   elseif ($temp_trend < -$big_temp_diff)
     $temp_trend_icon = "trend_down";
        
   $air_weather_arr['weather']['temp_trend_icon'] = $temp_trend_icon;
   
            
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
