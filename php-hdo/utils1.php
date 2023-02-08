<?php

function next_hdo($intervals)
{
  $hdo = array();
  
  //smycka pres hodiny + pro kazdy interval zjistit kolik procent lezi v hdo intervalech
  // $intervals = arrays
  $current_time = time();
  $doba_programu = 2.5 * 3600; //2.5h
  $max_pct_1 = 0.05; //max cas ve vysokem proudu - oranzova
  $max_pct_2 = 0.02; //max cas ve vysokem proudu - zelena
  $max_starts = 6; //max starts
  $max_hours_ahead = 24;
  
  
  for ($plushour = 0; $plushour <= $max_hours_ahead; $plushour++)
  { //smycka pres hodiny
   $start = $current_time + $plushour * 3600;
   $end = $start + $doba_programu;
   $overlap = 0;
   
   $in_hdo = 0; //counter kolik min jsem uvnitr
 
   for ($i=0; $i<count($intervals); $i++)
   { //smycka pres intervaly
       $overlap = $overlap + overlap($intervals[$i][0], $intervals[$i][1], $start, $end);
   } //smycka pres intervaly
   
   //echo "for " . $plushour . "->" .  $overlap . "<-<br>";
   if ($overlap <  $max_pct_1*$doba_programu)
   {
       if ($overlap < $max_pct_2 * $doba_programu)
         $color = "#355E3B"; //super ok - zelena
       else
         $color = "#A3911F";  //oranzova
         
       if ($plushour == 0)
            array_push($hdo, ["now", $color]);
       else     
            array_push($hdo, ["+" . $plushour . "h", $color]); //beru 
            
       if (count($hdo) >= $max_starts)
            break;
              
   } 
   
  } //smycka pres hodiny

  return $hdo;
}

function overlap ($s1, $k1, $s2, $k2)
{
 $o = 0;
 
 if ($s1 > $s2)
  { //prohod intervaly at zacina s1
    $s_temp = $s1;
    $s1 = $s2;
    $s2 = $s_temp;
    
    $k_temp = $k1;
    $k1 = $k2;
    $k2 = $k_temp;
  } //prohod intervaly
  
  // s1 urcite zacina drive!
  if ($s1 == $s2)
   {
    //echo "R";
    $o = min($k1, $k2) - $s1;
   }
   else
   { //urcite s1<s2
     if ($s2<$k1)
     {
       $o = min($k1, $k2) - $s2;
       // echo $s1 . "-" . $k1 . "-". $s2 . "-" .$k2 . "==" . $o .  "<br>";
       }
     //jinak se vubec neprekryvaji  
   }
   
 return $o;
}
?>