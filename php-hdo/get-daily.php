<?php

    require_once 'constants.php';
    require_once 'utils1.php';
     
    $table = array();
    $hours_ahead = 24;
        

    //staticke sloupce 
    $table['cols'] = array(
    array('type' => 'string', 'label' => 'čas'),
    array('type' => 'number', 'label' => 'déšť (mm/h)', 'role' => 'data'),     
    array('type' => 'number', 'label' => 'sníh (mm/h)', 'role' => 'data'),
    array('type' => 'number', 'label' => 'teplota', 'role' => 'data')      
    );


    //postupne radky
    
    $weather_url = $openweather_url . "?lat=".$lat."&lon=".$lon."&exclude=minutely,daily,alerts&appid=".$openweather_api."&units=metric";
   //echo $weather_url . "<p>"; // debug only
   $weather_content = file_get_contents($weather_url);
   if ($weather_content)
   {//weather content
    
    $weather_arr = json_decode($weather_content, true);
    
    for ($i = 0; $i<$hours_ahead; $i++)
        { //for hourly    
           // echo date('Y-m-d H:i:s', $weather_arr['hourly'][$i]['dt']) . " -- " . $weather_arr['hourly'][$i]['temp'] . "<br>";
           
           //jen kazdy druhy cas
            //$d = "";
           //if ($i % 2 == 0)
            $d = date('G', $weather_arr['hourly'][$i]['dt']) . 'h';
        
             
           $rain = 0;  
           $snow = 0;
           //teploty jsou proste vzdycky 
           if (array_key_exists('rain', $weather_arr['hourly'][$i]))  
              $rain = $weather_arr['hourly'][$i]['rain']['1h'];
           
           if (array_key_exists('snow', $weather_arr['hourly'][$i]))  
              $snow = $weather_arr['hourly'][$i]['snow']['1h'];
           
          
            $temp = array(
                            array('v' => $d ),
                            array('v' => $rain), //dest                                                        
                            array('v' => $snow), //snih
                            array('v' => $weather_arr['hourly'][$i]['temp'] ) //teplota                            
                            );
            $rows[] = array('c' => $temp);  
        }//for hourly
 }

   
    
    $table['rows'] = $rows;


    $jsonTable = json_encode($table);
  
    echo $jsonTable;


?>