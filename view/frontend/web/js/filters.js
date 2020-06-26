define([
    'jquery'
], function ($) {
    return function (options) {
        var self = this;
        this.filters = options.filters;
        Object.keys(this.filters).forEach( function(filter) { 
            var data = self.filters[filter];
            if (data.status) {
                require([
                    'conversionpro/filter/'+filter
                ], function(f) {
                    var filterVar = new f(data.selectors, data.options);
                });
            }
        });
    };
});