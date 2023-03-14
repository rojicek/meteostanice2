<?php

    require_once 'constants.php';
    require_once 'utils1.php';
     
    $table = array();
    $hours_ahead = 24;
        

    //staticke sloupce 
    $table['cols'] = array(
    array('type' => 'string', 'label' => 'čas'),
    array('type' => 'number', 'label' => 'déšť (mm/h)', 'role' => 'data'),
    array('type' => 'string', 'role' => 'style'),         
    array('type' => 'number', 'label' => 'sníh (mm/h)', 'role' => 'data'),
    array('type' => 'string', 'role' => 'style'),    
    array('type' => 'number', 'role' => 'annotation'),    
    array('type' => 'number', 'label' => 'teplota', 'role' => 'data'),
    array('type' => 'string', 'label'=> 'ikona', 'role' => 'none')      
    );



    
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
           $rain_opa = 0;
           $rain_stroke_width = 0;
           $snow_opa = 0;
           $snow_stroke_width = 0;
           
           
           //teploty jsou proste vzdycky 
           if (array_key_exists('rain', $weather_arr['hourly'][$i]))
           {                           
              $rain = $weather_arr['hourly'][$i]['rain']['1h'];
              $rain_opa = $weather_arr['hourly'][$i]['pop'];
              $rain_stroke_width = 1;
            }
           
           if (array_key_exists('snow', $weather_arr['hourly'][$i]))   
           {
              $snow = $weather_arr['hourly'][$i]['snow']['1h'];
              $snow_opa = $weather_arr['hourly'][$i]['pop'];
              $snow_stroke_width = 1;
            }
           
            //debug
            //$rain = 2.5;
            //$snow = 1.6;
            
            $total_precipitation = null;
            if ($rain + $snow > 0)
                $total_precipitation = round($rain + $snow, 0);
            
            $temp = array(
                            array('v' => $d ),
                            array('v' => $rain), //dest
                            array('v' => "fill-color: #3C5369; stroke-width: ".$rain_stroke_width."; stroke-color:#3C5369 fill-opacity:".$rain_opa),                                                                                    
                            array('v' => $snow), //snih
                            array('v' => "fill-color: #C5E2F7; stroke-width: ".$snow_stroke_width."; stroke-color:#000000 ; fill-opacity:".$snow_opa),
                            array('v' => $total_precipitation), //celkem anotace                            
                            array('v' => $weather_arr['hourly'][$i]['temp']), //teplota                            
                            array('v' => $weather_arr['hourly'][$i]['weather'][0]['icon'] . '.svg') //ikona
                            );
            $rows[] = array('c' => $temp);  
        }//for hourly
 }

   
    
    $table['rows'] = $rows;


    $jsonTable = json_encode($table);
  
    echo $jsonTable;


?>