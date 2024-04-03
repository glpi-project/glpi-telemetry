
import merge from 'deepmerge';

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
            // Define generic base options
            options = merge(
                {
                    title: {
                        text: '',
                        left: 'center',
                    }
                },
                options
            );
            switch (type) {
                case 'bar':
                    options = merge(
                        {
                            tooltip: {
                                trigger: 'axis',
                                axisPointer: {
                                    type: 'shadow',
                                },
                            },
                            legend: {
                                orient: 'vertical',
                                top: 30,
                                right: 0,
                            },
                            grid: {
                                top: 40,
                                left: 0,
                                right: 75, // only versions are displayed, no need too much space
                                bottom: 0,
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
                        },
                        options
                    );
                    break;
                case 'pie':
                    options = merge(
                        {
                            tooltip: {
                                formatter: '{b}: {d}% ({c})'
                            },
                            series: []
                        },
                        options
                    );
                    break;
                case 'nightingale-rose':
                    options = merge(
                        {
                            tooltip: {
                                formatter: '{b}: {c}'
                            },
                            series: []
                        },
                        options
                    );
                    break;
            }

            // Compute options related to series data
            switch (type) {
                case 'bar':
                    // Extract monthly totals
                    // eslint-disable-next-line no-case-declarations
                    let totalByMonth = [];
                    for (const key of options.xAxis.data.keys()) {
                        totalByMonth[key] = 0;
                    }
                    for (let i = 0; i < options.series.length; i++) {
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
                        for (let k = 0; k < options.series[i].data.length; k++) {
                            totalByMonth[k] += options.series[i].data[k];
                        }
                    }

                    // Replace series absolute data by percentages
                    // Store absolute values to be able to display them in the tooltip
                    // eslint-disable-next-line no-case-declarations
                    let absoluteValues = [];
                    for (var i = 0; i < options.series.length; i++) {
                        absoluteValues[i] = [];
                        for (let k = 0; k < options.series[i].data.length; k++) {
                            const percentage = totalByMonth[k] > 0
                                ? options.series[i].data[k] / totalByMonth[k] * 100
                                : 0;
                            absoluteValues[i][k] = options.series[i].data[k];
                            options.series[i].data[k] = percentage;
                        }
                    }

                    // Defines the tooltip formatter that uses absolute values
                    options = merge(
                        {
                            tooltip: {
                                formatter: (params) => {
                                    let name = null;
                                    let rows = [];
                                    for (const item of params) {
                                        if (name === null) {
                                            name = item.name;
                                        }
                                        const marker        = item.marker;
                                        const label         = item.seriesName;
                                        const percentage    = item.value;
                                        const absoluteValue = absoluteValues[item.componentIndex][item.dataIndex];

                                        if (absoluteValue === 0) {
                                            // Do not display 0 values
                                            continue;
                                        }

                                        rows.push(`
                                            <tr>
                                                <td>${marker} ${label}</td>
                                                <td class="text-end">${percentage.toFixed(2)}%</td>
                                                <td class="text-end">(${absoluteValue.toLocaleString('en')})</td>
                                            </tr>
                                        `);
                                    }
                                    return `
                                        <table class="table table-sm table-borderless">
                                            <tr><th colspan="3">${name}</th></tr>
                                            ${rows.join('')}
                                        </table>
                                    `;
                                }
                            }
                        },
                        options
                    );
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

            chartInstance.setOption(options, true);
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
    card.setAttribute('class', 'card dashboard-card');
    card.innerHTML = `
        <button type="button" class="btn p-1 ms-auto mt-1 me-1 mb-n4 d-none d-lg-inline-block" style="z-index: 1">
            <i class="ti ti-arrows-maximize"></i>
        </button>
        <div class="card-body">
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

// Fix charts size on widow resize
window.addEventListener('resize', () => {
    document.querySelectorAll('[data-chart-serie]').forEach((chart) => {
        let chartInstance = global.echarts.getInstanceByDom(chart.querySelector('.card-body'));
        if (typeof(chartInstance) !== 'undefined') {
            chartInstance.resize();
        }
    });
});
