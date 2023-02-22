<?php

/*
$myArr = array(
  'cas' => array('1h', '2h','3h', '4h'),
  'dest' => array(1,1,1,1),
  'snih' => array(2,2,2,2),
  'teplota' => array(10,11,11,12)
);

$myJSON = json_encode($myArr);

echo $myJSON;
*/

     
    $table = array();
        

     
    $table['cols'] = array(
    array('type' => 'string', 'label' => 'cas'),
    array('type' => 'number', 'label' => 'dest'), 
    array('type' => 'number', 'label' => 'snih'),
    array('type' => 'number', 'label' => 'teplota')      
    );

    $temp1 = array(
    array('v' => '2002'),
    array('v' => 1),
    array('v' => 2),
    array('v' => 3)  
    );
    
    $temp2 = array(
    array('v' => '2003'),
    array('v' => 4),
    array('v' => 5),
    array('v' => 6)  
    );

    $rows[] = array('c' => $temp1);   
    $rows[] = array('c' => $temp2);                                              
                                         
    
    $table['rows'] = $rows;


     $jsonTable = json_encode($table);
  
    echo $jsonTable;


?>