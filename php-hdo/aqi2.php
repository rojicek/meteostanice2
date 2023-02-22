<!DOCTYPE html>
<html> 

<head>
<title>Meteostanice - kvalita vzduchu</title>

<meta charset="UTF-8">
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />

<link rel="stylesheet" type="text/css" href="meteo.css">
<link rel=icon href=favicon.png>

</head>

<body>

<?php
// php ktere vrati air quality
 
require_once 'constants.php';
require_once 'utils1.php';


 
 //////////////////////////////
 // air quality
 

$air_url = "https://private-anon-26bd2f9116-golemioapi.apiary-proxy.com/v2/airqualitystations/";  

$opts = array (
    'http' => array (
        'method' => 'GET',
        'header'=> "Content-type: application/json; charset=utf-8\r\n"
                 . "x-access-token: " . $golemio_api . "\r\n",
        )
    );

$context  = stream_context_create($opts);
$air_content= file_get_contents($air_url, false, $context);

$air_quality_arr = json_decode($air_content, true);


/*
echo "<pre>";
var_dump($air_content);
echo "</pre>";
*/

$site_properties = get_aq_data($chmu_stanice, $air_quality_arr['features']);
echo $site_properties['updated_at'] . " at " .  $site_properties['name'] . "<br>";

$measurements = $site_properties['measurement'];

$aq_index = $measurements['AQ_hourly_index'];
$components = $measurements['components'];



/////////////////////////
echo $aq_index. "<br>";
for ($i=0; $i<count($components); $i++)
 {
  $type  = $components[$i]['type'];
  $value = $components[$i]['averaged_time']['value'];
  
  echo $type . " : " . $value . "<br>";
 }


echo "<p>--------------";


echo "<pre>";
var_dump($measurements);
echo "</pre>";

 

echo "<p>konec";
?>
</body>
</html>