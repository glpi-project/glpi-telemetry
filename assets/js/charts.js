import * as echarts from 'echarts';
import $ from "jquery";
window.$ = $;
window.echarts=echarts;

var chartDom = document.getElementById('main');
var myChart = echarts.init(chartDom);

$.get('data.json').done(function(data) {
  myChart.setOption({
    title: {
      text: 'Asynchronous Loading Example'
    },
    tooltip: {},
    legend: {},
    xAxis: {
      data: data.categories
    },
    yAxis: {},
    series: [
      {
        name: 'Sales',
        type: 'bar',
        data: data.values
      }
    ]
  });
});