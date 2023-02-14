<?php

$myArr = array(
  'cas' => array('1h', '2h','3h', '4h'),
  'dest' => array(1,1,1,1),
  'snih' => array(2,2,2,2),
  'teplota' => array(10,11,11,12)
);

$myJSON = json_encode($myArr);

echo $myJSON;


?>