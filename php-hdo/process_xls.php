<?php

require_once '/var/www/rojicek.cz/web/db/includedb.php'; 
require_once 'MSXLS.php'; //MSCFB.php is 'required once' inside MSXLS.php

date_default_timezone_set('Europe/Prague');

function test_obsahu ($excel, $row, $col, $text)
{
  $obsah = "neni definovano";
  if(isset($excel->cells[$row][$col])) 
      {
        $obsah = $excel->cells[$row][$col];
        if (strcmp($obsah, $text) == 0)
            return true;
       }     
   
   // fails here
   mail("jiri@rojicek.cz", "Meteo: selhalo parsovani PRE xls", "kontrola radek:" . $row . " sloupec:" . $col . " ceka:" . $text . " nactu:" . $obsah);
   return false;
   
}

function cti_bunku ($excel, $row, $col)
{
    if(isset($excel->cells[$row][$col])) 
            return $excel->cells[$row][$col];
    
   mail("jiri@rojicek.cz", "Meteo: selhalo parsovani PRE xls", "chybi pozadovana hodnota bunky na radku:" . $row . " a sloupci:" . $col);
   die("nekonzistentni PRE xls"); //Terminate script execution, show error message.        
}


function get_one_day($excel, $row, $col1, $col2, $date_start, $date_end, $dow)
{
  $sql_array=array(); // prazdne pole
  for ($i = $col1; $i<=$col2; $i=$i+2)
  {
    if (isset($excel->cells[$row][$i]) and isset($excel->cells[$row][$i+1]))
    {      
        $peak_start = gmdate("H:i:s", round($excel->cells[$row][$i]*86400, 0));
        $peak_end   = gmdate("H:i:s", round($excel->cells[$row][$i+1]*86400, 0));
        
        $new_sql = "INSERT INTO `hdo_tbl` (`dateStart`, `dateEnd`, `DoW`, `peakStart`, `peakStop`) VALUES (";
        $new_sql = $new_sql . "'" . date_format($date_start, "Y-m-d") . "', '" . date_format($date_end, "Y-m-d") . "', ";
        $new_sql = $new_sql . "'" . $dow . "', ";
        $new_sql = $new_sql . "'" . $peak_start . "', '" . $peak_end . "'";
        $new_sql = $new_sql . ")";
        
        array_push($sql_array, $new_sql);
    }
  }
  return $sql_array;
}


function write_to_db($sql_array)
{
   global $databaseConnection;
   
   // will do nothing if empty array, no need to test
   foreach ($sql_array as $sql)
    {
     try
        {
           $r = $databaseConnection -> query($sql);
        }
    catch (Exception $e)
       {
            echo "chyba zapisu do hdo tabulky" + $e;
            mail("jiri@rojicek.cz", "Meteo: chyba zapisu HDO dat", "Nejdou zapsat HDO data " . $sql);
       } 
   }
}


function  process_xls ($file_path, $file_local_path, $max_hdo_data)
{
    // remove local file
    //todo: debug 
    unlink($file_local_path);
    copy($file_path, $file_local_path);
    
    
    $base_date = date_create("1899-12-30");
    
    $excel = new MSXLS($file_local_path, true, null, true);
    $excel->select_sheet("PROGRAM_HDO");
        
    $sheet = $excel->get_active_sheet();
    
    if($excel->error) 
    {
        mail('jiri@rojicek.cz', 'METEO: selhalo nacteni xls', 'soubor: '. $file_path . ' chyba: ' . $excel1->err_msg); 
        //die($excel->err_msg); //Terminate script execution, show error message.
        return; // do nothing
    }
    
    $excel->set_fill_xl_errors(true);
    $excel->read_everything(); //Read cells into $excel->cells
    
    // testy - umre to kdyz se neco zmeni
    $r1 = test_obsahu ($excel, 0, 7, "PLATNOST");
    $r2 = test_obsahu ($excel, 0, 11, "od:");
    $r3 = test_obsahu ($excel, 1, 11, "do:");
    $r4 = test_obsahu ($excel, 27, 0, "485"); // tohle je kod naseho elektromeru!
    
    
    if (!$r1 or !$r2 or !$r3 or !$r4)
    {
        return; //do nothing, integrity check failed
    }
    
    $date_start_nbr = cti_bunku($excel, 0, 12);
    $date_end_nbr = cti_bunku($excel, 1, 12);
    
    
    $date_start =  clone $base_date;
    date_add($date_start, new DateInterval("P" . $date_start_nbr . "D"));
    $date_end =  clone $base_date;
    date_add($date_end, new DateInterval("P" . $date_end_nbr . "D")); 
    
    
    if ($date_start->getTimestamp() > $max_hdo_data)
    {
    
       mail('jiri@rojicek.cz', 'Meteo: aktualizace HDO dat', 'pisu do tabulky, nasel jsem nova data');     
         
       // radek + 12 (6*2) start/end intervalu pro kazdy den
       // pondeli
       $sql_array = get_one_day($excel, 27, 5, 16, $date_start, $date_end, 'MON');
       write_to_db ($sql_array);
       
       //utery
       $sql_array = get_one_day($excel, 28, 5, 16, $date_start, $date_end, 'TUE');
       write_to_db ($sql_array);
        
       //streda
       $sql_array = get_one_day($excel, 29, 5, 16, $date_start, $date_end, 'WED');
       write_to_db ($sql_array);
       
       //ctvrtek
       $sql_array = get_one_day($excel, 30, 5, 16, $date_start, $date_end, 'THU');
       write_to_db ($sql_array);
       
       //patek
       $sql_array = get_one_day($excel, 31, 5, 16, $date_start, $date_end, 'FRI');
       write_to_db ($sql_array);
       
       //sobota
       $sql_array = get_one_day($excel, 32, 5, 16, $date_start, $date_end, 'SAT');
       write_to_db ($sql_array);
       
       //nedele
       $sql_array = get_one_day($excel, 33, 5, 16, $date_start, $date_end, 'SUN');
       write_to_db ($sql_array);
       
       //svatky
       $sql_array = get_one_day($excel, 34, 5, 16, $date_start, $date_end, 'HOL');
       write_to_db ($sql_array);
    
    }
        
}


function clean_up_old_hdo($current_time)
{
  
    try
        {
          global $databaseConnection;
         
          // delete older than 20000000sec ~ 230days
          $cut_date = date('Y-m-d', $current_time-20000000);
          $sqlDel = "delete from hdo_tbl where dateStart < '" . $cut_date ."'";
          $r = $databaseConnection -> query($sqlDel);
          mail("jiri@rojicek.cz", "Meteo: mazani HDO dat", "HDO smazano do: " . $cut_date);
        }
    catch (Exception $e)
       {
           mail("jiri@rojicek.cz", "Meteo: chyba mazani dat", "Nejdou smazat stara HDO data: " . $sqlDel);
           echo "chyba mazani starych dat" + $e;
       } 
    
}

?>