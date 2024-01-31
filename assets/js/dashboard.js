import * as echarts from "echarts";

window.echarts = echarts;

global.filterCallbacks = [];
global.registerFilterCallback = function (callback) { global.filterCallbacks.push(callback); };
global.executeFilterCallbacks = function (filters) {
    global.filterCallbacks.forEach(callback => {
        callback(filters);
    });
};

window.addEventListener("DOMContentLoaded", () => {

    let select = document.getElementById('dataPeriod');
    let selectedOption = select.value;
    var params = {};
    setValue(selectedOption);

    select.onchange = function () { setValue(); };

    function setValue() {
        selectedOption = document.getElementById('dataPeriod').value;
        params = { filter: selectedOption };
        global.executeFilterCallbacks(params);
    }

    const modalButtons = document.querySelectorAll('.openModal');
    modalButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const chartContainer = button.closest('.card').querySelector('.card-body');
            const chart = echarts.getInstanceByDom(chartContainer);
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

    (async () => {
        const rawResponse = await fetch('http://[::1]:8000/test', {
            method: 'POST',
            mode: 'no-cors',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                "glpi": {
                    "uuid": "esCwsmrHsrc3lbtUgQrUaHcmkLw8yXUVeDVmsULa",
                    "version": "10.0.11-dev",
                    "plugins": [
                        {
                            "key": "fields",
                            "version": "1.21.6"
                        },
                        {
                            "key": "advancedforms",
                            "version": "1.1.2"
                        },
                        {
                            "key": "advanceddashboard",
                            "version": "1.5.1"
                        }
                    ],
                    "default_language": "en_GB",
                    "install_mode": "GIT",
                    "usage": {
                        "avg_entities": "0-500",
                        "avg_computers": "0-500",
                        "avg_networkequipments": "0-500",
                        "avg_tickets": "500-1000",
                        "avg_problems": "0-500",
                        "avg_changes": "0-500",
                        "avg_projects": "0-500",
                        "avg_users": "0-500",
                        "avg_groups": "0-500",
                        "ldap_enabled": true,
                        "mailcollector_enabled": false,
                        "notifications_modes": [],
                        "notifications": [
                            "mailing"
                        ]
                    }
                },
                "system": {
                    "db": {
                        "engine": "MySQL Community Server - GPL",
                        "version": "8.0.35",
                        "size": "117.0",
                        "log_size": "",
                        "sql_mode": "STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION"
                    },
                    "web_server": {
                        "engine": "",
                        "version": ""
                    },
                    "php": {
                        "version": "8.3.0",
                        "modules": [
                            "Core",
                            "date",
                            "libxml",
                            "openssl",
                            "pcre",
                            "sqlite3",
                            "zlib",
                            "ctype",
                            "curl",
                            "dom",
                            "fileinfo",
                            "filter",
                            "ftp",
                            "hash",
                            "iconv",
                            "json",
                            "mbstring",
                            "SPL",
                            "session",
                            "PDO",
                            "pdo_sqlite",
                            "bz2",
                            "posix",
                            "random",
                            "Reflection",
                            "standard",
                            "SimpleXML",
                            "tokenizer",
                            "xml",
                            "xmlreader",
                            "xmlwriter",
                            "mysqlnd",
                            "apache2handler",
                            "Phar",
                            "exif",
                            "gd",
                            "intl",
                            "ldap",
                            "memcached",
                            "mysqli",
                            "pcntl",
                            "redis",
                            "soap",
                            "sodium",
                            "xmlrpc",
                            "zip",
                            "Zend OPcache"
                        ],
                        "setup": {
                            "max_execution_time": "30",
                            "memory_limit": "128M",
                            "post_max_size": "8M",
                            "safe_mode": false,
                            "session": "files",
                            "upload_max_filesize": "2M"
                        }
                    },
                    "os": {
                        "family": "Linux",
                        "distribution": "",
                        "version": "5.15.0-89-generic"
                    }
                }
            })
        });
        console.log(rawResponse);
        const content = await rawResponse.text();

        console.log(content);
    })();
});
