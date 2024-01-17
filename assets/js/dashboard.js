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
});


