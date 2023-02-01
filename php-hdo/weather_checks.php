<?php

 //used in main, but let's keep it here   
 $low_temp = 6;
 $super_low_temp = -3;
 $hi_temp = 28;
 $super_hi_temp = 33;
 $hi_wind = 10;
 $super_hi_wind = 15;
 $rain = 0.5;
 $super_rain = 1.5;
 $snow = 0.5;
 $super_snow = 1.5;

function cycling_index_check ($value, $direction, $limit, $hi_limit, $cycle_index)
{

    // the value does not exists, return cycle index as it is
    if ($value != '')
       {//value exists
            
      if ($direction == '>')
      {
          if ($value >= $limit)
              $cycle_index = max ($cycle_index, 2);            
          if ($value >= $hi_limit)
              $cycle_index = max ($cycle_index, 3);
      } //if vetsi
              
      if ($direction == '<')    
      {
          if ($value <= $limit)
              $cycle_index = max ($cycle_index, 2);           
          if ($value <= $hi_limit)
              $cycle_index = max ($cycle_index, 3);
      } //if vetsi
         
     } //value exists
     
     return $cycle_index;
}

?>