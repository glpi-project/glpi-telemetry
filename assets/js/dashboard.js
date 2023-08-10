console.log('Hello');

global.filterCallbacks = [];
global.registerFilterCallback = function (callback) {global.filterCallbacks.push(callback)};
global.executeFilterCallbacks = function (filters) {
    global.filterCallbacks.forEach(callback => {
        callback(filters);
    });
};


window.addEventListener("DOMContentLoaded", (event) => {

    var today = moment().format();
    var defaultDate = moment().subtract(5, 'years').format();

    const picker = new easepick.create({
        element: document.getElementById('dateRange'),
        css: [
            "https://cdn.jsdelivr.net/npm/@easepick/bundle@1.2.1/dist/index.css",
        ],
        plugins: [AmpPlugin, RangePlugin, PresetPlugin],
        zIndex: 10,
        AmpPlugin: {
            dropdown: {
                minYear: 2000,
                months: true,
                years: true
            },
            darkMode: false
        },
        RangePlugin: {
            tooltip: true,
            startDate: defaultDate,
            endDate: today,
        }
    });

    picker.on('select', (e) => {
        var startDate = picker.getStartDate().toJSON();
        var endDate = picker.getEndDate().toJSON();
        global.executeFilterCallbacks({startDate: startDate, endDate: endDate});

    });
        var startDate = picker.getStartDate().toJSON();
        var endDate = picker.getEndDate().toJSON();
        global.executeFilterCallbacks({startDate: startDate, endDate: endDate});
});