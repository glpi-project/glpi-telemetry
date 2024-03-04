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


window.addEventListener("DOMContentLoaded", () => {
    const data = global.referenceData;

    const translator = new Intl.DisplayNames(['en'], {type: 'region'});
    const namesMap = new Map();
    const grid = new gridjs.Grid({
        columns: [
            {
                name: "Name",
                formatter: (cell, row) => {
                    const url = row.cells[6].data;
                    const name = cell;
                    try {
                        new URL(url);
                        return gridjs.h(
                            'a',
                            {
                                href: url,
                                target: '_blank',
                            },
                            name
                        );
                    } catch (e) {
                        return name;
                    }
                }
            },
            {
                name: "Country",
                formatter: (cell) => {
                    if (cell === null || cell === '') {
                        return '';
                    }

                    if (!namesMap.has(cell)) {
                        namesMap.set(cell, translator.of(cell.toUpperCase()));
                    }
                    return gridjs.html(`<span class="fi fi-${cell}" title="${namesMap.get(cell)}"></span>`);
                },
                sort: {
                    compare: (a, b) => {
                        if (!namesMap.has(a)) {
                            namesMap.set(a, translator.of(a.toUpperCase()));
                        }
                        if (!namesMap.has(b)) {
                            namesMap.set(b, translator.of(b.toUpperCase()));
                        }
                        return namesMap.get(a).localeCompare(namesMap.get(b));
                    }
                },
            },
            "Nb of assets",
            "Nb of helpdesk",
            "Registration date",
            "Comment",
            {
                name: "URL",
                hidden: true,
            },
        ],
        pagination: {
            limit: 20
        },
        search: true,
        sort: true,
        data: data,
    });

    grid.render(document.getElementById('references-table-container'));
});
