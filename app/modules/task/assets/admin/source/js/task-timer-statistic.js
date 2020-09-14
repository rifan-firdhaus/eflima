(function($){
  window.taskTimerCart = function($chart, chartSeries){
    var options = {
      chart: {
        height: 85,
        type: "area",
        toolbar: {
          show: false
        },
        sparkline: {
          enabled: true
        },
        zoom: {
          enabled: false
        }
      },
      colors: ["#468bef"],
      dataLabels: {
        enabled: false,
        style: {
          fontSize: "9px"
        },
        formatter: function(label){
          return parseFloat((parseFloat(label) / 3600).toFixed(2));
        }
      },
      stroke: {
        curve: "straight",
        width: 3
      },
      series: [
        {
          name: "Progress",
          data: chartSeries
        }
      ],
      grid: {
        show: true,
        borderColor: "rgba(0,0,0,0.04)",
        xaxis: {
          lines: {
            show: true
          }
        },
        yaxis: {
          lines: {
            show: false
          }
        }
      },
      xaxis: {
        type: "datetime",
        tooltip: {
          enabled: false
        },
        labels: {
          show: false
        },
      },
      yaxis: {
        min: 0,
        labels: {
          show: false
        },
      },
      legend: {
        show: false
      },
      markers: {
        size: 5,
        hover: {
          size: 7
        }
      },
      tooltip: {
        theme: "dark",
        marker: {
          show: false
        },
        followCursor: false,
        custom: function(options){
          var date = chartSeries[options.dataPointIndex][0];
          var formattedDate = options.ctx.formatters.xLabelFormat(function(val){
            return val;
          }, date);
          var value = options.series[options.seriesIndex][options.dataPointIndex];

          value = (parseFloat(value) / 3600).toFixed(2);

          return "<div class=\"p-2 font-size-md\">" +
            "<span><i class='icons8-size icon pr-1 icons8-calendar'></i>" + formattedDate + ": <span class=\"font-weight-bold\">" + value + " hours</span></span>" +
            "</div>";
        },
        y: {
          formatter: function(label){
            return (parseFloat(label) / 3600).toFixed(2) + " hours";
          }
        }
      }
    };

    var chart = new ApexCharts(
      $chart.get(0),
      options
    );

    chart.render();
  };
})(jQuery);