
/**
 * Fetch and display chart data.
 */
const fetchAndDisplayChartsData = function () {
    document.querySelectorAll('[data-chart-serie]').forEach((chart) => {
        let chartInstance = global.echarts.getInstanceByDom(chart.querySelector('.card-body'));
        if (typeof(chartInstance) === 'undefined') {
            chartInstance = global.echarts.init(chart.querySelector('.card-body'));
        }

        const serie = chart.getAttribute('data-chart-serie');
        const type = chart.getAttribute('data-chart-type');
        const periodFilter = document.getElementById('dataPeriod').value;

        fetch(`telemetry/chart/${serie}/${type}/${periodFilter}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        }).then(response => {
            return response.json();
        }).then(options => {
            // Apply base options
            let baseOptions = {
                title: {
                    text: '',
                    left: 'center',
                }
            };
            switch (type) {
                case 'bar':
                    baseOptions = Object.assign(
                        baseOptions,
                        {
                            tooltip: {
                                trigger: 'axis',
                                axisPointer: {
                                    type: 'shadow',
                                },
                                valueFormatter: function (value) {
                                    return value.toFixed(2) + ' %';
                                },
                            },
                            legend: {
                                orient: 'horizontal',
                                top: 25,
                                textStyle: {
                                    fontSize: 12
                                }
                            },
                            grid: {
                                left: '3%',
                                right: '3%',
                                bottom: '2%',
                                containLabel: true
                            },
                            yAxis: {
                                type: 'value',
                                axisLabel: {
                                    formatter: '{value} %'
                                },
                                min: 0,
                                max: 100,
                            },
                            xAxis: {
                                type: 'category',
                                axisTick: {
                                    alignWithLabel: true
                                },
                                axisLabel: {
                                    rotate: 30
                                },
                                data: []
                            },
                            series: []
                        }
                    );
                    break;
                case 'pie':
                    chartInstance.setOption({
                        tooltip: {
                            formatter: '{b}: {d}% ({c})'
                        },
                        series: []
                    });
                    break;
                case 'nightingale-rose':
                    chartInstance.setOption({
                        tooltip: {
                            formatter: '{b}: {c}'
                        },
                        series: [{
                            name: 'Nightingale Chart',
                        }]
                    });
                    break;
            }
            chartInstance.setOption(baseOptions);

            // Apply series options
            switch (type) {
                case 'bar':
                    for (var i = 0; i < options.series.length; i++) {
                        options.series[i] = Object.assign(
                            {
                                type: 'bar',
                                stack: 'total',
                                label: {
                                    show: false,
                                },
                                emphasis: {
                                    focus: 'series',
                                },
                                data: [],
                            },
                            options.series[i]
                        );
                    }
                    break;
                case 'pie':
                    options.series[0] = Object.assign(
                        {
                            type: 'pie',
                            radius: ['15%', '50%'],
                            itemStyle: {
                                borderRadius: 5
                            },
                            data: []
                        },
                        options.series[0]
                    );
                    break;
                case 'nightingale-rose':
                    options.series[0] = Object.assign(
                        {
                            type: 'pie',
                            top: 30,
                            radius: [20, 70],
                            avoidLabelOverlap: false,
                            center: ['50%', '50%'],
                            roseType: 'area',
                            itemStyle: {
                                borderRadius: 3
                            },
                            data: []
                        },
                        options.series[0]
                    );
                    break;
            }
            chartInstance.setOption(options);
        });
    });
};

/**
 * Display a chart in a modal.
 *
 * @param chart ECharts instance
 */
const displayChartInModal = function (chart) {
    const options = chart.getOption();
    const title = typeof (options.title) !== 'undefined' && typeof (options.title[0]) !== 'undefined' && typeof (options.title[0].text) !== 'undefined'
        ? options.title[0].text
        : '';

    const modal = document.createElement('div');
    modal.setAttribute('class', 'modal modal-blur fade');
    modal.setAttribute('role', 'dialog');
    modal.innerHTML = `
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">${title}</h5>
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

    const bootstrapModal = new window.bootstrap.Modal(modal);
    bootstrapModal.show();
};

// Initialize charts DOM
document.querySelectorAll('[data-chart-serie]').forEach((chart) => {
    const card = document.createElement('div');
    card.setAttribute('class', 'card');
    card.innerHTML = `
        <button type="button" class="btn p-1 ms-auto mt-1 me-1 mb-n4" style="z-index: 1">
            <i class="ti ti-arrows-maximize"></i>
        </button>
        <div class="card-body dashboard-card-size">
        </div>
    `;
    chart.appendChild(card);

    card.querySelector('button').addEventListener('click', (event) => {
        const chartContainer = event.target.closest('.card').querySelector('.card-body');
        const chart = global.echarts.getInstanceByDom(chartContainer);
        displayChartInModal(chart);
    });
});

// Fetch charts data now and whenever period filter change
fetchAndDisplayChartsData();
document.getElementById('dataPeriod').onchange = fetchAndDisplayChartsData;
