import * as gridjs from "gridjs";

window.gridjs = gridjs;

window.addEventListener('DOMContentLoaded', () => {
    const chartDom = document.getElementById('map_graph');
    const myChart = global.echarts.init(chartDom);

    myChart.showLoading();

    fetch('reference/map/countries', {
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
                    roam: false,
                    map: 'countriesMap',
                    emphasis: {
                        label: {
                            show: false
                        }
                    },
                    select: {
                        disabled: true,
                    },
                    data: []
                }
            ]
        });

        fetch('reference/map/data', {
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

            // Open modal and display the map in full screen
            const cardBody = document.querySelector('.card-body');
            const button = document.getElementById('modalBtn');
            if (cardBody) {
                const options = myChart.getOption();

                button.addEventListener('click', () => {

                    const modal = document.createElement('div');
                    modal.setAttribute('class', 'modal modal-blur fade');
                    modal.setAttribute('role', 'dialog');
                    modal.innerHTML = `
                        <div class="modal-dialog modal-fullscreen">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="chart-container" style="width: 100%; height: 80vh; max-height: 700px;"></div>
                                </div>
                            </div>
                        </div>
                    `;

                    modal.addEventListener('shown.bs.modal', () => {
                        options.title = { show: false };

                        const modalChartContainer = modal.querySelector('.chart-container');
                        const modalChart = global.echarts.init(modalChartContainer);
                        modalChart.setOption(options);
                    });
                    modal.addEventListener('hidden.bs.modal', () => {
                        modal.remove();
                    });

                    document.body.appendChild(modal);
                    const bootstrapModal = new window.bootstrap.Modal(modal);
                    bootstrapModal.show();
                });
            }

        }).catch(error => {
            console.error('an error occured: ', error);
        });
    }).catch(error => {
        console.error('an error occured: ', error);
    });

    // Fix size on window resize
    window.addEventListener('resize', () => {
        myChart.resize();
    });
});

window.addEventListener("DOMContentLoaded", () => {
    const data = global.referenceData;

    const translator = new Intl.DisplayNames(['en'], { type: 'region' });
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
