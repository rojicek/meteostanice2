<?php

 // not in github
 require_once '/var/www/rojicek.cz/web/db/weather-secrets.php';     
 
 //used in main, but let's keep it here   
 $low_temp = 6;
 $super_low_temp = -3;
 $hi_temp = 28;
 $super_hi_temp = 33;
 $hi_wind = 10;
 $super_hi_wind = 15;
 $rain = 0.5;
 $super_rain = 1.5;
 $snow = 0.5;
 $super_snow = 1.5;
 
 $small_temp_diff = 2;
 $big_temp_diff = 6;
 
 $lat = "50.0973722";
 $lon = "14.4074581";


 $aqi_limits = array(
 "co"    => array(1000, 1200, 1201, 1202),
 "no2"   => array(50, 100, 200, 400),
 "o3"    => array(60, 120, 180, 240),
 "pm10"  => array(25, 50, 90, 180),
 "pm2_5" => array(15, 30, 55, 110),
 "no"    => array(2000, 2500, 2501, 2502),
 "nh3"   => array(1000, 1100, 1200, 1300) //asi vsechno nedostupne

 );

 


?>