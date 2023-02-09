<!DOCTYPE html>
<html> 

<title>Meteostanice</title>

<meta charset="UTF-8">
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />

<link rel="stylesheet" type="text/css" href="meteo.css">
<link rel=icon href=favicon.png>

</head>

<?php

//prep data by query
require_once '/var/www/rojicek.cz/web/db/vars.php';
require_once 'utils1.php';

$meteo_url = "https://www.rojicek.cz/meteo/meteo-query.php?pwd=".$pwd;

$meteo_content = file_get_contents($meteo_url);
$meteo_content_arr = json_decode($meteo_content, true);

$hdo_starts = next_hdo($meteo_content_arr["hdo_intervals"]);
$hdo_intervals = next_hdo_intervals($meteo_content_arr["hdo_intervals"]);
/*
$hdo_intervals =  [
    0 => [200,"#FF1010", "10:00"],
    1 => [500,"#42FF10", "11:00"],
    2 => [150,"#FF1010", "13:00"]   
];
*/

$total_hdo = 0;
for ($i=0; $i<count($hdo_intervals); $i++)
{
  $total_hdo = $total_hdo + $hdo_intervals[$i][0];
}

#echo "total hdo: " . $total_hdo . "<br>";
?>

<body>

<table>

<tr>
<td rowspan=3>
<?php echo "<img src=\"img/weather/". $meteo_content_arr["weather"]["w_icon"] . ".svg\"  style=\"vertical-align:middle\" width=200>"; ?>
</td>

<td class="maly" style="text-align:right;" colspan=3>
<?php
 $current_time = time();
 echo date('j M, H:i', $current_time);
?>
</td>
</tr>


<tr>


<td colspan=3 class="maly" style="text-align:right;vertical-align: middle;" height=70>
<?php 
echo date('H:i', $meteo_content_arr["weather"]["sunrise"]);
echo "&nbsp;&nbsp;";

if (($current_time < $meteo_content_arr["weather"]["sunrise"]) or ($current_time > $meteo_content_arr["weather"]["sunset"])) 
    echo "<img src=\"img/sun/sunrise.svg\" style=\"vertical-align:middle\" width=80>";
else
    echo "<img src=\"img/sun/sunset.svg\" style=\"vertical-align:middle\" width=80>";

echo "&nbsp;&nbsp;"; 
echo date('H:i',$meteo_content_arr["weather"]["sunset"]);
?>
</td>

</tr>

<tr>

<td>
<font class="mensi">
<?php echo "NOW&nbsp;&nbsp;<img src=\"img/cycle/cycle_". $meteo_content_arr["weather"]["clc_tdy"] . ".png\"  style=\"vertical-align:middle\" width=70>"; ?>
</font>
</td>

<td>
<font class="mensi">
<?php echo strtoupper(date("D", strtotime("+1 day"))) . "&nbsp;&nbsp;<img src=\"img/cycle/cycle_". $meteo_content_arr["weather"]["clc_tmr"] . ".png\"  style=\"vertical-align:middle\" width=70>"; ?>
</font>
</td>

<td>
<img src="img/air_quality/air<?php echo $meteo_content_arr["weather"]["aqi"]; ?>.png"  width=70>
</td>

</tr>


<tr>
<td>
<b><?php echo $meteo_content_arr["weather"]["temp"] . "&degC";  ?></b>
<font class="mensi">
<?php echo $meteo_content_arr["weather"]["temp_feel"] . "&degC";  ?>
</font>
</td>
<td colspan=3 rowspan=3>

<?php
$second_line = 0;

for ($i=0; $i<count($hdo_starts); $i++)
 {
  
   if (($i >= count($hdo_starts)/2) and ($second_line == 0))
   {
        echo "<br>";
        $second_line = 1;
    }
     echo "<font color = \"".$hdo_starts[$i][1]."\">" . $hdo_starts[$i][0] . "</font> ";
 }
?>
 
</td>
</tr>


<tr>
<td>
<?php
// trend prep 
$temp_trend = $meteo_content_arr["weather"]["temp_trend"];
if ($temp_trend>0)
  $temp_trend = "+".$temp_trend;
?>
<img src="img/temp_trend/<?php echo $meteo_content_arr["weather"]["temp_trend_icon"]; ?>.png"  style="vertical-align:middle" width=40> 
<font class="mensi"><?php echo $temp_trend . "&degC";  ?></font>
</td>

</tr>

<tr>
<td>
<img src="img/direction/compass-<?php echo strtolower ($meteo_content_arr["weather"]["wind_dir"]); ?>.png"  style="vertical-align:middle" width=30 height=30> 
<font style="font-size: smaller;"><?php echo $meteo_content_arr["weather"]["wind_speed"] . "m/s";?></font>
</td>

</tr>

<tr>
<td colspan=4  height=5px>
</td>
</tr>

<tr>
<td  colspan=4 >

<table style="table-layout: fixed;" width="100%">
<tr height=25px>
<?php
for ($i=0; $i<count($hdo_intervals); $i++)
{
  $procenta = round(100*$hdo_intervals[$i][0]/$total_hdo, 0);
  echo "<td bgcolor=\"".$hdo_intervals[$i][1]."\" style=\"width:".$procenta."%;font-size:10pt; \"></td>";
}
?>
</tr>
</table>


</td>
</tr>

</table>


</body>
