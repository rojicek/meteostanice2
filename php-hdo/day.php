<html>
  <head>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
    
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawVisualization);
      
function drawVisualization() {
    // Some raw data (not necessarily accurate)
    var data = google.visualization.arrayToDataTable([
        ['cas', 'snih', 'dest',  'teplota'],//, {type: 'number', role: 'annotation'}],
        ['1h', 1, 1, 5],
        ['2h', 2, 2, 4],
        ['3h', 3, 1, 3],
        ['4h', 4, 2, 2],
        ['5h', 5, 1, 1]
    ]);

    var options = {
        isStacked: true,
        title : 'Monthly Coffee Production by Country',
        //vAxis: [0: {format: '#'}, 1: {format: '#'}],
        //vAxis: [0: {title: 'prvni'}, 1: {title: 'druha'}],
        hAxis: {title: 'cas'},
        //seriesType: 'bars',
        series: {0: {type: 'bars'},
                 1: {type: 'bars'}},
        //series {0: { type: 'bars' }}
        //series: {2: {type: 'line', targetAxisIndex: 1}},
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
