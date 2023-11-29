import * as echarts from "echarts";
import moment from "moment";

window.echarts = echarts;

console.log('Hello');

global.filterCallbacks = [];
global.results = [];
global.clearResults = function() {global.results.length = 0 };
global.registerResults = function (response) {global.results.push(response)};
global.registerFilterCallback = function (callback) {global.filterCallbacks.push(callback)};
global.executeFilterCallbacks = function (filters) {
    global.filterCallbacks.forEach(callback => {
        callback(filters);
    });
};

window.addEventListener("DOMContentLoaded", (event) => {

    var select = document.getElementById('dataPeriod');
    var selectedOption = select.value;
    var today = moment().format('Y-MM-DD hh:mm:ss');
    var defaultStartDate = moment().subtract(1, 'year').format('Y-MM-DD hh:mm:ss');
    var params = {};
    setValue(selectedOption);

    select.onchange = function() {setValue()};

    function setValue() {
        selectedOption = document.getElementById('dataPeriod').value;
        switch (selectedOption) {
            case 'lastYear':
                console.log("lastYearOption");
                params = {startDate: defaultStartDate, endDate: today, filter: selectedOption};
                break;
            case 'fiveYear':
                console.log("fiveYearOption");
                defaultStartDate = moment().subtract(5, 'years').format('Y-MM-DD hh:mm:ss');
                params = {startDate: defaultStartDate, endDate: today, filter: selectedOption};
                break;
            case 'always':
                console.log("alwaysOption");
                defaultStartDate = moment().subtract(10, 'years').format('Y-MM-DD hh:mm:ss');
                params = {startDate: defaultStartDate, endDate: today, filter: selectedOption};
                break;
        }
        // params = JSON.stringify(params);
        console.log(params);
        global.executeFilterCallbacks(params);
    }
});