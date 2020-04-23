/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import '../css/app.scss';

var $ = require('jquery');
window.$ = $;
window.jQuery = $;

import autocomplete from 'autocomplete.js';

autocomplete('#search-input', { hint: false }, [{
    source: function(query, cb) {
        $.ajax({
            url: '/ac/search?q='+query
        }).then(function(data) {
            cb(data.products);
        });
    }
}]);
console.log('Hello Webpack Encore! Edit me in assets/js/app.js');
