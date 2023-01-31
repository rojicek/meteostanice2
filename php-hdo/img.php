<?php
header("Content-Type: image/png");

$im = @imagecreate(480, 320)
    or die("Cannot Initialize new GD image stream");
$background_color = imagecolorallocate($im, 255, 255, 255);


//place fixed elements
$w_icon_url = "https://openweathermap.org/img/wn/13d@2x.png";
$overlayImage = imagecreatefrompng($w_icon_url);
imagecopy($im, $overlayImage, 10, 10, 0, 0, imagesx($overlayImage), imagesy($overlayImage));



//$text_color = imagecolorallocate($im, 233, 14, 91);

//imagestring($im, 1, 5, 5,  "A Simple Text String", $text_color);
imagepng($im);
imagedestroy($im);
?>