<html>
  <head>


<!--Load the AJAX API--> 
<meta charset="UTF-8">
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />

<script type="text/javascript"
src="https://www.google.com/jsapi"></script> <script type="text/javascript" 
src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js">
</script> 




 <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script type="text/javascript">
    
    // Load the Visualization API and the piechart package.
     google.load('visualization', '1', {'packages':['corechart']});
    
     
    function getExtremes(data, column_name) 
    {
    var t_min = 100;
    var t_max = -100;
    var temp_column = data.getColumnIndex(column_name);        
    
    for (var i=0 ; i<data.getNumberOfRows() ; i++) 
    {     
      t_min = Math.min(t_min, data.getValue(i,temp_column));
      t_max = Math.max(t_max, data.getValue(i,temp_column));                            
     }
    
    var band = Math.max(0.03*(t_max-t_min), 1.5);   
    return [t_min-band, t_max+band];
    } //getExtremes
     
    google.setOnLoadCallback(drawChartTVStatusInterval);
    function drawChartTVStatusInterval() 
    {
    
      var jsonWData = $.ajax({
          url: "get-daily.php",
          dataType:"json",
          async: false
          }).responseText;
      
      
      
     var hourly_data = new google.visualization.DataTable(jsonWData);
     
     var temp_min = 0;
     var temp_max = 0;
     
     [temp_min, temp_max] = getExtremes(hourly_data, 'teplota');
      
     var options = {
        isStacked: true,
        title : 'Předpoveď',
        
        bar: {groupWidth: "90%"},
        
        annotations: {
        alwaysOutside: true,
          textStyle: {
            fontSize: 14, 
            color:  '#000000'         
           },
           stem: {length:2, color:'transparent'}
          },
        
        vAxes: {
                0: {title: 'Teplota (°C)', viewWindow: {min: temp_min, max: temp_max}}, //osy , gridlines: {}, viewWindowMode:'pretty' 
                1: {title: 'Srážky  (mm/h)', gridlines: {color: 'transparent'}, viewWindow: {min: 0, max: 10}} //pevne meze aby obrazek byl vizualne podobny, max 10mm/h 
                },
                
        hAxis: {title: 'čas', gridlines: {color: '#FF0000'},  slantedText:true, slantedTextAngle:45},
        
        series: {
                 //jenom pro data series, anotace se nepocitaji
                 0: {type: 'bars', targetAxisIndex: 1}, //rain (color in style)                                 
                 1: {type: 'bars', targetAxisIndex: 1}, //snow (color in style)                                                                   
                 2: {type: 'line', targetAxisIndex: 0, color:'#228b22',  lineWidth: 5, curveType: 'function'}
                },
        legend:'none',
       
    };

  
      
        var chart = new google.visualization.ComboChart(document.getElementById('chart_div'));
        chart.draw(hourly_data, options);

    
    } 


   </script>

  </head>
  <body>
    <div id="chart_div" style="width: 900px; height: 500px;"></div>
  </body>
</html>
