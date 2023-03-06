<?php

 // not in github
 require_once '/var/www/rojicek.cz/web/db/weather-secrets.php';     
 

 
 $temp_limits = array(
  //mesic -> [extreme_low, low, high, extreme_high], tj intervaly cervena - oranzova - zelena - oranzova - cervena
   1 => [-4,  0, 28, 33],
   2 => [-3,  2, 28, 33],
   3 => [-1,  4, 28, 33],
   4 => [ 2,  6, 28, 33],
   5 => [ 8, 12, 28, 33],
   6 => [10, 15, 28, 33],
   7 => [10, 15, 28, 33],
   8 => [10, 15, 28, 33],
   9 => [ 8, 12, 28, 33],
  10 => [ 2,  6, 28, 33],
  11 => [-1,  4, 28, 33],
  12 => [-3,  2, 28, 33]
 );
 
 $chmu_stanice = 'ALIBA'; // Praha 4 - Libus
 
 $hi_wind = 10;
 $super_hi_wind = 15;
 $rain = 0.5;
 $super_rain = 1.5;
 $snow = 0.5;
 $super_snow = 2;
 
 //pro trend
 $small_temp_diff = 2;
 $big_temp_diff = 6;
 
 $lat = "50.0973722";
 $lon = "14.4074581";


 //todo: nejaky vyzkum, tohle je trochu kreativni
 $aqi_limits = array(
 "co"    => array(2000, 2500, 2501, 2502, 2503), //nepouzivam
 "no2"   => array(50, 100, 200, 3000, 400),
 "o3"    => array(60, 85, 105, 130, 250),
 "pm10"  => array(30, 60, 90, 180, 250),
 "pm2_5" => array(20, 35, 60, 110, 250),
 "so2"   => array(40, 80, 250, 380, 800),
 "no"    => array(2000, 2500, 2501, 2502, 2503), //nepouzivam
 "nh3"   => array(1000, 1100, 1200, 1300, 1400) //nepouzivam
 );


 //$air_url = "https://private-anon-26bd2f9116-golemioapi.apiary-proxy.com/v2/airqualitystations/";  
$air_url = "https://api.golemio.cz/v2/airqualitystations/";
$openweather_url = 'https://api.openweathermap.org/data/3.0/onecall';

//nejak nefunguje - je to zadratovane
//$na = "NA";

?>