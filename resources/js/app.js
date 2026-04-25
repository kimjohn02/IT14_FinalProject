import './bootstrap';

import $ from 'jquery';
window.$ = window.jQuery = $;

// 2. Import Select2 JavaScript
import 'select2';

// 3. Import Select2 CSS
import 'select2/dist/css/select2.min.css';

// Now you can initialize Select2 in your scripts
$(document).ready(function() {
    $('.product-select').select2();
});