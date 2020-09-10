
// any CSS you import will output into a single css file (app.css in this case)
import '../css/app.scss';

import autocomplete from 'autocomplete.js';

autocomplete('#search-input', { hint: false }, [{
    source: function(query, cb) {
        fetch("/ac/search?q="+query)
            .then(response => response.json())
            .then(data => cb(data.products));
    }
}]);
