<?php

// php code to update hdo info in my sql


require_once "includedb.php";
require_once "process_xls.php";
require_once 'MSXLS.php'; //MSCFB.php is 'required once' inside MSXLS.php
 

ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

date_default_timezone_set('Europe/Prague');


try 
{   
    
    define("total_seconds_5_days", 432000);
      

    $current_time = time();
    
    // max available hdo data in my sql 
    $sql_maxdata = "select max(dateEnd) as max_data from hdo_tbl"; 
    $max_hdo_data_q = $databaseConnection -> query($sql_maxdata);
    
    $row = $max_hdo_data_q->fetch_row();
    $max_hdo_data = strtotime($row[0]); //epoch max hdo data
    
    
    if  ($max_hdo_data - $current_time < total_seconds_5_days)
    {
        // worth try to read new hdo
        mail('jiri@rojicek.cz', 'Aktualizace HDO dat', 'pokousim se aktualizovat HDO data');     
        
        // repeat the same for 2 files            
        $file1_path = 'https://www.predistribuce.cz/Files/potrebuji-zaridit/stav-hdo/aktualni-program-hdo-ke-stazeni/';
        $file1_local_path = $_SERVER['DOCUMENT_ROOT'] . '/meteo/data/aktualni.xls'; 
        $file2_path = 'https://www.predistribuce.cz/Files/potrebuji-zaridit/stav-hdo/nasledny-program-hdo-ke-stazeni/';
        $file2_local_path = $_SERVER['DOCUMENT_ROOT'] . '/meteo/data/nasledny.xls';
        
        process_xls ($file1_path, $file1_local_path, $max_hdo_data);
        process_xls ($file2_path, $file2_local_path, $max_hdo_data);
        
    }
          
    
 }  
catch (Exception $e)
 {
    echo "chyba" + $e;
 }    
    

?>
