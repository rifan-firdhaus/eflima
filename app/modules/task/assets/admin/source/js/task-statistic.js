(function($){
  window.taskProgressCart = function($chart, taskProgressStatistic){
    var columnColors = [];

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
        position: "top",
        style: {
          fontSize: "9px"
        },
        formatter: function(label){
          return parseFloat(label) + "%";
        }
      },
      stroke: {
        curve: "straight",
        width: 3
      },
      series: [
        {
          name: "Progress",
          data: taskProgressStatistic.series
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
            show: true
          }
        }
      },
      xaxis: {
        type: "datetime",
        tooltip: {
          enabled: false
        },
        labels: {
          show: true
        }
      },
      yaxis: {
        min: 0,
        max: 100,
        range: 10,
        tickAmount: 4,
        labels: {
          show: true
        }
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
        custom: function(options){
          var date = taskProgressStatistic.series[options.dataPointIndex][0];
          var formattedDate = options.ctx.formatters.xLabelFormat(function(val){
            return val;
          }, date);

          return "<div class=\"p-2 font-size-md\">" +
            "<span><i class='icons8-size icon pr-1 icons8-calendar'></i>" + formattedDate + ": <span class=\"font-weight-bold\">" + options.series[options.seriesIndex][options.dataPointIndex] + "%</span></span>" +
            "</div>";
        },
        followCursor: false,
        y: {
          formatter: function(label){
            return parseFloat(label) + "%";
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
