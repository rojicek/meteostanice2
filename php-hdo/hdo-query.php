<?php
// php ktere vrati info kdy je HO pro zpracovani v ESP
// navratove hodnoty json nebo plain text - podle toho co se bude snaze parsovat v ESP

require_once "includedb.php";


$DoWdnes = " ucase(SUBSTRING(DAYNAME(now()),1,3)) ";  #mozna nahradim HOL, takze takhle
$DoWzitra = " ucase(SUBSTRING(DAYNAME(DATE_ADD(now(), INTERVAL 1 DAY) ),1,3)) ";  #mozna nahradim HOL, takze takh

//todo: kontrola jestli nejaky den neni svatek !!!

$selectDNESKA = "select DoW, peakStart, peakStop from hdo_tbl where date(now())>=dateStart AND date(now())<=dateEnd AND " . $DoWdnes . "  = DoW order by timeid";  
$hdoResultsDNESKA  = $databaseConnection->query ($selectDNESKA) ;
   
$selectZITRA = "select DoW, peakStart, peakStop from hdo_tbl where date(now())>=dateStart AND date(now())<=dateEnd AND " . $DoWzitra . "  = DoW order by timeid";  
$hdoResultsZITRA  = $databaseConnection->query ($selectZITRA) ;

 
$date_today = date('Y-m-d', time());
$date_tomorrow = date('Y-m-d', time()+86400);

$datetime_now = time();
$datetime_str_now = date('Y-m-d H:i:s', $datetime_now);

  
$intervals_obj = new stdClass();
$arr_data = array();
 
  while($row = $hdoResultsDNESKA->fetch_assoc())
  {
     $zacatek = $date_today . " " . $row['peakStart'];
     $konec = $date_today . " " . $row['peakStop'];

     if (strtotime($zacatek) >= $datetime_now)
     {  
        array_push($arr_data, array($zacatek, $konec));
     }        
     elseif (strtotime($konec) >= $datetime_now)
     { 
        array_push($arr_data, array($datetime_str_now, $konec));
     }
  }
 
  // no need to cf act time - it's tomorrow
  while($row = $hdoResultsZITRA->fetch_assoc())
  {
     $zacatek = $date_tomorrow . " " . $row['peakStart'];
     $konec = $date_tomorrow . " " . $row['peakStop'];
     array_push($arr_data, array($zacatek, $konec));    
  }
 
 //play safe :)
 asort($arr_data);
 $intervals_obj->intervals = $arr_data;
 $export_json = json_encode($intervals_obj);
 
 echo $export_json; 
 
   
?>