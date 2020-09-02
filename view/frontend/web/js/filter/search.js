define([
    'jquery'
], function ($) {
	return function(cssSelector, options) {
        var self = this;
        this._cssSelector = {
            filtersList: "#narrow-by-list .filter-options-content",
            li: "li",
            ignoredClasses: ['.swatch-attribute', '.celebros-price-layered'],
            hideClass: "filtered"
        };
        this._options = {
            minSearchFilterQty: 4,
            searchBoxHtmlTemplate: '<input type="text" id="attr-filter-{filter_id}" placeholder="Search {filter_name}..." /></li>'
        };
        this.cssSelector = $.extend({}, this._cssSelector, cssSelector);
		this.options = $.extend({}, this._options, options);
        this.format = function(string, args) {
            for (k in args) {
                string = string.replace("{" + k + "}", args[k])
            }
            return string;
        };
        this.prepareId = function(string) {
            var id = string.toLowerCase();
            id = id.replace(" ", "-");
            return id = id.replace("/", "-");
        };
        this.checkIgnoredClasses = function(element) {
            var result = true;
            self.cssSelector.ignoredClasses.forEach( function(cl) {
                if (element.find(cl).length) {
                    result = false;
                }
            });
            return result;
        };
        this.init = function() {
            $(self.cssSelector.filtersList).each( function(){
                var ansQty = $(this).find(self.cssSelector.li).length;
                if (self.checkIgnoredClasses($(this))
                && self.options.minSearchFilterQty <= ansQty
                ) {
                    var filter_name = $(this).prev().text();
                    var filter_id = self.prepareId($(this).prev().text());
                    $(this).prepend(self.format(self.options.searchBoxHtmlTemplate, {filter_id, filter_name}));
                    $(this).on('keyup', function() {
                        var filter = $(this).find('#attr-filter-'+filter_id).val().toUpperCase();
                        var lis = $(this).find(self.cssSelector.li);
                        for (var i = 0; i < lis.length; i++) {
                            var name = $.trim($(lis[i]).find('a').text());
                            if (name.toUpperCase().indexOf(filter) != -1) { 
                                $(lis[i]).removeClass(self.cssSelector.hideClass);
                            } else {
                                $(lis[i]).addClass(self.cssSelector.hideClass);
                            }
                        }
                    });
                }
            });
        };
        this.init();
    };
});