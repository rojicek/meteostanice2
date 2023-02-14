<html>
  <head>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
    
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawVisualization);
      
function drawVisualization() {
    // Some raw data (not necessarily accurate)
    var data = google.visualization.arrayToDataTable([
        ['cas', 'dest', 'snih',  'teplota'],//, {type: 'number', role: 'annotation'}],
        ['1h', 1, 1.5, 18],
        ['2h', 2, 1, 20],
        ['3h', 1, 3.5, 18],
        ['4h', 1, 1, 18],
        ['5h', 2, 1.5, 20],
        ['6h', 1, 3, 18],
        ['7h', 1, 1, 18],
        ['8h', 2, 1, 20],
        ['9h', 0, 0, 18],
        ['10h', 0, 0, 18],
        ['11h', 1, 1, 18],
        ['12h', 2, 1, 20],
        ['13h', 1, 0, 18],
        ['14h', 1, 0, 18],
        ['15h', 2, 1, 20],
        ['16h', 1, 0, 17],
        ['17h', 0, 1, 16],
        ['18h', 0, 1, 15],
        ['19h', 0, 3.5, 14],
        ['20h', 1, 4, 10],
        ['21h', 1, 5.5, 9],
        ['22h', 2, 5, 9],
        ['23h', 1, 3, 10],
       
    ]);

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
    chart.draw(data, options);
}
      
      
    </script>
  </head>
  <body>
    <div id="chart_div" style="width: 900px; height: 500px;"></div>
  </body>
</html>
