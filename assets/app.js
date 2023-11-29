/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

import '@tabler/core';
import '@tabler/core/src/scss/tabler.scss';
import '@tabler/icons-webfont/tabler-icons.scss';

import * as echarts from 'echarts';
window.echarts=echarts;

import moment from 'moment';
global.moment = moment;
// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';
// import 'bootstrap';
