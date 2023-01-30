<?php
// php ktere vrati kompletni info pro meteostanici

include '/var/www/rojicek.cz/web/db/includedb.php'; 


if ($_GET["pwd"] != "pa1e2")
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
        array_push($hdo_arr, array($zacatek, $konec));
     }        
     elseif (strtotime($konec) >= $datetime_now)
     { 
        array_push($hdo_arr, array(date('Y-m-d H:i:s', $datetime_now), $konec));
     }
  }
 
  // no need to cf act time - it's tomorrow
  while($row = $hdoResultsZITRA->fetch_assoc())
  {
     $zacatek = $date_tomorrow . " " . $row['peakStart'];
     $konec = $date_tomorrow . " " . $row['peakStop'];
     array_push($hdo_arr, array($zacatek, $konec));    
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
 
 $max_time_gap = 7200;
 $current_time = time();
 
 //$openweather_api = 'xxxxx';
 try
 {
  $air_url = "https://api.openweathermap.org/data/2.5/air_pollution?lat=".$lat."&lon=".$lon."&appid=".$openweather_api;
  //echo $air_url . "<pre>";
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
   $current_url = "https://api.openweathermap.org/data/3.0/onecall?lat=".$lat."&lon=".$lon."&exclude=minutely,hourly,daily,alerts&appid=".$openweather_api."&units=metric";
   //echo $current_url . "<p>";
   $current_content = file_get_contents($current_url);
   if ($current_content)
   {//current weather
    
    $current_weather_arr = json_decode($current_content, true);
    
    if (array_key_exists("current", $current_weather_arr))
        if ($current_time - $current_weather_arr['current']['dt'] < $max_time_gap)
          { //json ok and up to date
             $sunrise = $current_weather_arr['current']['sunrise'];
             $sunset  = $current_weather_arr['current']['sunset'];
             $temp = $current_weather_arr['current']['temp'];
             $temp_feel = $current_weather_arr['current']['feels_like'];
             $wind_speed = $current_weather_arr['current']['wind_speed'];
             $wind_deg = $current_weather_arr['current']['wind_deg'];
             $w_desc = $current_weather_arr['current']['weather'][0]['main'];
          } //json ok and up to date
   
   }//current weather
   
   $air_weather_arr['weather']['sunrise'] = date('H:i', $sunrise);
   $air_weather_arr['weather']['sunset'] = date('H:i', $sunset);
   $air_weather_arr['weather']['temp'] = round($temp,1);
   $air_weather_arr['weather']['temp_feel'] = round($temp_feel,1);
   $air_weather_arr['weather']['wind_speed'] = round($wind_speed,1);
   $air_weather_arr['weather']['wind_dir'] = "NA";
   if (($wind_deg>=337.5) and ($wind_deg<22.5)) 
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
   
   

/*
{"lat":50.0974,"lon":14.4075,"timezone":"Europe/Prague","timezone_offset":3600,"current":{"dt":1675024601,"sunrise":1674974461,
"sunset":1675007377,"temp":269.97,"feels_like":263.05,"pressure":1021,"humidity":83,"dew_point":267.78,
"uvi":0,"clouds":0,"visibility":10000,"wind_speed":7.2,"wind_deg":240,"weather":[{"id":800,"main":"Clear","description":"clear sky","icon":"01n"}]}}*
*/
  
   

   
 }
 catch (Exception $e) {
    echo   $e->getMessage();
}



 $all_arr = array_merge($hdo_arr_for_json, $air_weather_arr);
  
 $export_json = json_encode($all_arr);
  
 
 echo $export_json; 
 
 //echo "<p>konec";
 
   
?>