<?php

<<<<<<< HEAD
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
 
=======

 $openweather_api = "916e26607461b7d5b5c3fad8075fa22e";
 $pwd="pa1e2";
    
    
 //used in main, but let's keep it here   
 $low_temp = 6;
 $super_low_temp = -3;
 $hi_temp = 28;
 $super_hi_temp = 33;
>>>>>>> e241a845e249a21d9f4ba3ced7382cf0e040be37
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
<<<<<<< HEAD
 "co"    => array(2000, 2500, 2501, 2502), //nepouzivam
=======
 "co"    => array(1000, 1200, 1201, 1202),
>>>>>>> e241a845e249a21d9f4ba3ced7382cf0e040be37
 "no2"   => array(50, 100, 200, 400),
 "o3"    => array(60, 120, 180, 240),
 "pm10"  => array(25, 50, 90, 180),
 "pm2_5" => array(15, 30, 55, 110),
<<<<<<< HEAD
 "so2"   => array(40, 80, 380, 800),
 "no"    => array(2000, 2500, 2501, 2502), //nepouzivam
 "nh3"   => array(1000, 1100, 1200, 1300) //nepouzivam
 );


 //$air_url = "https://private-anon-26bd2f9116-golemioapi.apiary-proxy.com/v2/airqualitystations/";  
$air_url = "https://api.golemio.cz/v2/airqualitystations/";
$openweather_url = 'https://api.openweathermap.org/data/3.0/onecall';

=======
 "no"    => array(2000, 2500, 2501, 2502),
 "nh3"   => array(1000, 1100, 1200, 1300) //asi vsechno nedostupne

 );

 
>>>>>>> e241a845e249a21d9f4ba3ced7382cf0e040be37


?>