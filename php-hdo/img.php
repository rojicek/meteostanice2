<?php

// PHP version: 8.0.26


/*

header("Content-Type: image/png");

$im = @imagecreate(480, 320)
    or die("Cannot Initialize new GD image stream");
$background_color = imagecolorallocate($im, 255, 255, 255);



//place fixed elements
$w_icon_url = "img/weather_icon_demo.png";


$deg_color = imagecolorallocate($im, 0, 0, 0);

imagestring($im, 5, 5, 5,  "15°C", $deg_color);


$overlayImage = imagecreatefrompng($w_icon_url);
imagecopy($im, $overlayImage, 50, 50, 0, 0, imagesx($overlayImage), imagesy($overlayImage));

imagecopy($im, $overlayImage, 250, 50, 0, 0, imagesx($overlayImage), imagesy($overlayImage));

imagestring($im, 15, 55, 5,  "25°C", $deg_color);

imagepng($im);

imagedestroy($im);
*/
?>