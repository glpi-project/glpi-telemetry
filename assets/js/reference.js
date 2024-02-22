import * as gridjs from "gridjs";

window.gridjs = gridjs;

window.addEventListener('DOMContentLoaded', () => {
    const chartDom = document.getElementById('map_graph');
    const myChart = global.echarts.init(chartDom);

    myChart.showLoading();

    fetch('map/countries', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        },
    }).then(response => {
        return response.json();
    }).then(geoJson => {
        myChart.hideLoading();
        global.echarts.registerMap('countriesMap', geoJson);

        myChart.setOption({
            title: {
                text: 'GLPI references by country',
                left: 'center',
                top: 10,
                textStyle: {
                    fontSize: 15
                }
            },
            tooltip: {
                trigger: 'item',
                showDelay: 0,
                transitionDuration: 0.2,
                formatter: '{b} : {c}'
            },
            visualMap: {
                show: false,
                min: 1,
                max: 1000,
                inRange: {
                    color: [
                        '#e6f7ff',
                        '#bae7ff',
                        '#91d5ff',
                        '#69c0ff',
                        '#40a9ff',
                        '#1890ff',
                        '#096dd9',
                        '#0050b3',
                        '#003a8c',
                        '#002766',
                        '#00134d'
                    ]
                },
                calculable: true
            },
            series: [
                {
                    name: 'References by country',
                    type: 'map',
                    roam: true,
                    map: 'countriesMap',
                    emphasis: {
                        label: {
                            show: false
                        }
                    },
                    data: []
                }
            ]
        });

        fetch('map/graph', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            },
        }).then(response => {
            return response.json();
        }).then(json => {
            myChart.setOption({
                series: [
                    {
                        data: json
                    }
                ]
            });
        }).catch(error => {
            console.error('an error occured: ', error);
        });
    }).catch(error => {
        console.error('an error occured: ', error);
    });
});
