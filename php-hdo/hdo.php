<?php

// php code to update hdo info in my sql


require_once "includedb.php";
require_once __DIR__.'/SimpleXLS.php';
use Shuchkin\SimpleXLS;



ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);


try 
{   
    
    define("total_seconds_5_days", 432000);
    
    // todo!!
    $debug = 1;

    
    
    
    $current_time = time();
    
    //debug!! todo
    $current_time = 1675897000; // neco blizko konce hdo dat
    
    // max available hdo data in my sql 
    $sql_maxdata = "select max(dateEnd) as max_data from hdo_tbl"; 
    $max_hdo_data_q = $databaseConnection -> query($sql_maxdata);
    
    $row = $max_hdo_data_q->fetch_row();
    $max_hdo_data = strtotime($row[0]); //epoch max hdo data
    
    
    if  ($max_hdo_data - $current_time < total_seconds_5_days)
    {
        // worth try to read new hdo
        echo "new hdo! <br>";        
        $file1_path = 'https://www.predistribuce.cz/Files/potrebuji-zaridit/stav-hdo/aktualni-program-hdo-ke-stazeni/';
        $file1_local_path = $_SERVER['DOCUMENT_ROOT'] . '/meteo/data/aktualni.xls'; 
        $file2_path = 'https://www.predistribuce.cz/Files/potrebuji-zaridit/stav-hdo/nasledny-program-hdo-ke-stazeni/';
        $file2_local_path = $_SERVER['DOCUMENT_ROOT'] . '/meteo/data/nasledny.xls';
        
        if ($debug != 1)
        { 
        // delete local xls if exists (no check, it's built in unlink)
        unlink($file1_local_path);
        unlink($file2_local_path);
        
        // copy from PRE ... will find out if it fails later
        copy($file1_path, $file1_local_path);
        copy($file2_path, $file2_local_path);
        } //konec debug ifu
        
        // find time interval in both files
        
        if ($xlsx = SimpleXLS::parse($file1_local_path))             
            $aktualni_array = $xlsx->rows();
        if ($xlsx = SimpleXLS::parse($file2_local_path))             
            $nasledny_array = $xlsx->rows();
            
            
        $aktualni_start =  $aktualni_array[0][12];
        $aktualni_konec =  $aktualni_array[1][12];
        
        $nasledny_start =  $nasledny_array[0][12];
        $nasledny_konec =  $nasledny_array[1][12];
        
        echo $aktualni_start . "<br>";
        echo $aktualni_konec . "<p>";    
        
        echo $nasledny_start . "<br>";
        echo $nasledny_konec . "<p>";
        
            /*
        echo "<p>";
                echo "<pre>";
                print_r ($xlsx->rows());
                echo "</pre>";
            */
        

        
        // try cp 
    }
        
    echo $max_hdo_data . "<br>";
    echo "konec";    
    
 }  
catch (Exception $e)
 {
    echo "chyba" + $e;
 }    
    

?>
