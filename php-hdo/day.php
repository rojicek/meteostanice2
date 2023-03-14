<html>
  <head>


<!--Load the AJAX API--> 
<meta charset="UTF-8">
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />


   <style>
.overlay {    
    position: absolute;
    top: 60px;  /* chartArea top  */    
}
   </style>

<script type="text/javascript"
src="https://www.google.com/jsapi"></script> <script type="text/javascript" 
src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js">
</script> 




 <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script type="text/javascript">
    
    // Load the Visualization API and the piechart package.
     google.load('visualization', '1', {'packages':['corechart']});
     
     //global variable (across script)
     var hourly_data;


     function loadWIcons(){
        document.getElementById('w_icon_0').innerHTML = "<img src=img/weather/" + getIcon(0) + " height=80>";
        document.getElementById('w_icon_6').innerHTML = "<img src=img/weather/" + getIcon(6) + " height=80>";
        document.getElementById('w_icon_12').innerHTML = "<img src=img/weather/" + getIcon(12) + " height=80>";
        document.getElementById('w_icon_18').innerHTML = "<img src=img/weather/" + getIcon(18) + " height=80>";
        document.getElementById('w_icon_23').innerHTML = "<img src=img/weather/" + getIcon(23) + " height=80>";
     }
     
    function getExtremes(column_name) 
    {
    var t_min = 100;
    var t_max = -100;

    var temp_column = hourly_data.getColumnIndex(column_name);        
    
    for (var i=0 ; i<hourly_data.getNumberOfRows() ; i++) 
    {     
      t_min = Math.min(t_min, hourly_data.getValue(i,temp_column));
      t_max = Math.max(t_max, hourly_data.getValue(i,temp_column));                            
     }
    
    var band = Math.max(0.1*(t_max-t_min), 1.4); 
      
    return [Math.round(t_min-band), Math.round(t_max+band+0.5)];
    } //getExtremes
    
     
    google.setOnLoadCallback(drawChartTVStatusInterval);
    
    function drawChartTVStatusInterval() 
    {
    
      var jsonWData = $.ajax({
          url: "get-daily.php",
          dataType:"json",
          async: false
          }).responseText;
            
      
      
     //var hourly_data = new google.visualization.DataTable(jsonWData);
     hourly_data = new google.visualization.DataTable(jsonWData);
          
     var temp_min = 0;
     var temp_max = 0;
     
     [temp_min, temp_max] = getExtremes('teplota');
     
      
     var options = {
        isStacked: true,
        title : 'Předpověď na příštích 24h',
        
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
        
        //jakmile jsou data, tak nahrej ikony pocasi
        google.visualization.events.addListener(chart, 'ready', loadWIcons);
        
        chart.draw(hourly_data, options);
    
    } 
    
    function getIcon(hour_ahead)
    { //getIcon
    
      var icon_column = hourly_data.getColumnIndex('ikona');              
      return (hourly_data.getValue(hour_ahead,icon_column));
      
    } //getIcon


   </script>

  </head>
  <body>
    <div id="chart_div" style="width: 900px; height: 500px;"></div>
               
    <div class="overlay" style="left:175px;">  <pre id='w_icon_0'> </pre> </div>
    <div class="overlay" style="left:300px;">  <pre id='w_icon_6'> </pre> </div>
    <div class="overlay" style="left:425px;">  <pre id='w_icon_12'> </pre> </div>
    <div class="overlay" style="left:550px;">  <pre id='w_icon_18'> </pre> </div>
    <div class="overlay" style="left:675px;">  <pre id='w_icon_23'> </pre> </div>    
        
  </body>
</html>
