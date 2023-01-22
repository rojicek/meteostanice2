<?php
// php ktere vrati info kdy je HO pro zpracovani v ESP
// navratove hodnoty json nebo plain text - podle toho co se bude snaze parsovat v ESP

require_once "includedb.php";


$intervals_obj = new stdClass();


$intervals_obj->intervals = [["00:30", "01:30"], ["02:30", "33:33"]];


$export_json = json_encode($intervals_obj);

echo $export_json;
echo "<p>";

//var_dump(json_decode($export_json));
//echo "<p>";

//$a = $export_json->{'intervals'};
//echo $a;

// demo parsing json

?>