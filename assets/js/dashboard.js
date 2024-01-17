import * as echarts from "echarts";
import moment from "moment";

window.echarts = echarts;

global.filterCallbacks = [];
global.results = [];
global.clearResults = function() {global.results.length = 0; };
global.registerResults = function (response) {global.results.push(response);};
global.registerFilterCallback = function (callback) {global.filterCallbacks.push(callback);};
global.executeFilterCallbacks = function (filters) {
    global.filterCallbacks.forEach(callback => {
        callback(filters);
    });
};

window.addEventListener("DOMContentLoaded", () => {

    var select = document.getElementById('dataPeriod');
    var selectedOption = select.value;
    var today = moment().format('Y-MM-DD hh:mm:ss');
    var defaultStartDate = moment().subtract(1, 'year').format('Y-MM-DD hh:mm:ss');
    var params = {};
    setValue(selectedOption);

    select.onchange = function() {setValue();};

    function setValue() {
        selectedOption = document.getElementById('dataPeriod').value;
        switch (selectedOption) {
            case 'lastYear':
                params = {startDate: defaultStartDate, endDate: today, filter: selectedOption};
                break;
            case 'fiveYear':
                defaultStartDate = moment().subtract(5, 'years').format('Y-MM-DD hh:mm:ss');
                params = {startDate: defaultStartDate, endDate: today, filter: selectedOption};
                break;
            case 'always':
                defaultStartDate = moment().subtract(10, 'years').format('Y-MM-DD hh:mm:ss');
                params = {startDate: defaultStartDate, endDate: today, filter: selectedOption};
                break;
        }
        global.executeFilterCallbacks(params);
    }

    const modalButtons = document.querySelectorAll('.openModal');
    modalButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const chartContainer = button.closest('.card').querySelector('.card-body');
            const chart = echarts.getInstanceByDom(chartContainer);
            const options = chart.getOption();
            const title = typeof(options.title) !== 'undefined' && typeof(options.title[0]) !== 'undefined' && typeof(options.title[0].text) !== 'undefined'
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
                options.title = {show:false};

                const modalChartContainer = modal.querySelector('.chart-container');
                const modalChart = echarts.init(modalChartContainer);
                modalChart.setOption(options);
            });
            modal.addEventListener('hidden.bs.modal', () => {
                modal.remove();
            });

            const bootstrapModal = new window.bootstrap.Modal(modal);
            bootstrapModal.show();
        });
    });
});
