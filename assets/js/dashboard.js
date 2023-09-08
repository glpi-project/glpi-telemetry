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
                params = {startDate: defaultStartDate, endDate: today};
                console.log(params);
                break;
            case 'fiveYear':
                console.log("fiveYearOption");
                defaultStartDate = moment().subtract(5, 'years').format('Y-MM-DD hh:mm:ss');
                params = {startDate: defaultStartDate, endDate: today};
                console.log(params);
                break;
            case 'always':
                console.log("alwaysOption");
                defaultStartDate = moment().subtract(10, 'years').format('Y-MM-DD hh:mm:ss');
                params = {startDate: defaultStartDate, endDate: today};
                console.log(params);
                break;
        }
        params = JSON.stringify(params);
        global.executeFilterCallbacks(params);
    }

    // const picker = new easepick.create({
    //     element: document.getElementById('dateRange'),
    //     css: [
    //         "https://cdn.jsdelivr.net/npm/@easepick/bundle@1.2.1/dist/index.css",
    //     ],
    //     plugins: [AmpPlugin, RangePlugin, PresetPlugin],
    //     zIndex: 10,
    //     AmpPlugin: {
    //         dropdown: {
    //             minYear: 2000,
    //             months: true,
    //             years: true
    //         },
    //         darkMode: false
    //     },
    //     RangePlugin: {
    //         tooltip: true,
    //         startDate: defaultDate,
    //         endDate: today,
    //     }
    // });

    // picker.on('select', (e) => {
    //     var startDate = picker.getStartDate().toJSON();
    //     var endDate = picker.getEndDate().toJSON();
    //     global.executeFilterCallbacks({startDate: startDate, endDate: endDate});

    // });
    //     var startDate = picker.getStartDate().toJSON();
    //     var endDate = picker.getEndDate().toJSON();
    //     global.executeFilterCallbacks({startDate: startDate, endDate: endDate});
});