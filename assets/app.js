/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
import * as echarts from 'echarts';
window.echarts=echarts;

import { easepick } from '@easepick/core';
global.easepick = easepick;

import { AmpPlugin } from '@easepick/amp-plugin';
global.AmpPlugin = AmpPlugin;

import { RangePlugin } from '@easepick/range-plugin';
global.RangePlugin = RangePlugin;

import { PresetPlugin } from '@easepick/bundle';
global.PresetPlugin = PresetPlugin;
// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';
import 'bootstrap';
import bsCustomFileInput from 'bs-custom-file-input';
// start the Stimulus application
import './bootstrap';

bsCustomFileInput.init();





