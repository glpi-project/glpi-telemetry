/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
import * as echarts from 'echarts';
window.echarts = echarts;
console.log(echarts);
import $ from "jquery";
window.$ = $;
import DataTable from 'datatables.net-dt';
window.DataTable = DataTable;
// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';
import 'bootstrap';
import bsCustomFileInput from 'bs-custom-file-input';
// start the Stimulus application
import './bootstrap';

bsCustomFileInput.init();





