// import * as echarts from 'echarts';
// console.log(echarts);
// import $ from "jquery";
// window.$ = $;

// var chartDom = document.getElementById("chart");
// var myChart = echarts.init(chartDom);
// var option;

// var option = {
//     xAxis: {
//       data: ['A','B','C']
//     },
//     yAxis: {},
//     series: [{
//       type: 'bar',
//       data: [10,20,30]
//     }]
// };

// option && myChart.setOption(option);
// var chartDom = document.getElementById('main');
// var myChart = echarts.init(chartDom);

// $.get('data.json').done(function(data) {
//   myChart.setOption({
//     title: {
//       text: 'Asynchronous Loading Example'
//     },
//     tooltip: {},
//     legend: {},
//     xAxis: {
//       data: data.categories
//     },
//     yAxis: {},
//     series: [
//       {
//         name: 'Sales',
//         type: 'bar',
//         data: data.values
//       }
//     ]
//   });
// });