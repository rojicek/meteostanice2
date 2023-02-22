<html>
  <head>


<!--Load the AJAX API--> 
<script type="text/javascript"
src="https://www.google.com/jsapi"></script> <script type="text/javascript" 
src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js">
</script> 




 <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script type="text/javascript">
    
    // Load the Visualization API and the piechart package.
     google.load('visualization', '1', {'packages':['corechart']});
    
     
     
     
    google.setOnLoadCallback(drawChartTVStatusInterval);
    function drawChartTVStatusInterval() 
    {
    
      var jsonDataTVStatusInterval = $.ajax({
          url: "get-daily.php",
          dataType:"json",
          async: false
          }).responseText;
      
      
      
       var hourly_data = new google.visualization.DataTable(jsonDataTVStatusInterval);
    
      
     var options = {
        isStacked: true,
        title : 'Predpoved',

        vAxes: {
                0: {title: 'Teplota'},
               // 0: {title: 'Profit', textStyle: {color: 'DodgerBlue', bold: true}},
                1: {title: 'Srazky', gridlines: {color: 'transparent'}}
                
                },
                
        hAxis: {title: 'cas', gridlines: {color: '#FF0000'}},
        
        series: {
                 0: {type: 'bars', targetAxisIndex: 1, color:'#53789E'},
                 1: {type: 'bars', targetAxisIndex: 1, color:'#C5E2F7'},
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
