<html>
  <head>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
    
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawVisualization);
      
function drawVisualization() {
    // Some raw data (not necessarily accurate)
    var data = google.visualization.arrayToDataTable([
        ['cas', 'snih', 'dest',  'Total', {type: 'number', role: 'annotation'}],
        ['1h', 5, 1,  2, 17],
        ['2h', 6, 1, 2, 20],
        ['3h', 2, 1, 0, 9],
        ['4h', 5, 1, 1, 18],
        ['5h', 3, 1, 0, 13]
    ]);

    var options = {
        isStacked: true,
        title : 'Monthly Coffee Production by Country',
        vAxis: {title: 'y osa'},
        hAxis: {title: 'cas'},
        seriesType: 'bars',
        series: {2: {type: 'line'}},
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
