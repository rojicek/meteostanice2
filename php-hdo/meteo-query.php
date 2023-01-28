<?php
// php ktere vrati kompletni info pro meteostanici

 include '/var/www/rojicek.cz/web/db/includedb.php'; 



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
 
 //$openweather_api = 'xxxxx';
 
 $air_url = "https://api.openweathermap.org/data/2.5/air_pollution?lat=".$lat."&lon=".$lon."&appid=".$openweather_api;
 $air_quality_arr = json_decode(file_get_contents($air_url), true);
 
 // rename list key to air quality
 
 //try
// {
  if (array_key_exists("list", $air_quality_arr))
  {
     $air_quality_arr['air_quality'] = $air_quality_arr['list'];
     unset($air_quality_arr['list']);
   }
  else
   {
      $ert=0;
   //  $air_quality_arr['air_quality'] = 0;
   }
   
   //$rr=0;
 //}
 //catch (Exception $e) {
  //  echo   $e->getMessage();
//}



 $all_arr = array_merge($hdo_arr_for_json, $air_quality_arr);
  
 $export_json = json_encode($all_arr);
  

 echo $export_json; 
 
 //echo "<p>konec";
 
   
?>