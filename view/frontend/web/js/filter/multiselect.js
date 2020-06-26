define([
    'jquery'
], function ($) {
	return function(cssSelector, options) {
        var self = this;
        this._cssSelector = {
            checkboxSelector: ".filter-options-content .item input[type='checkbox']",
            hlinkSelector: ".filter-options-content .item a"
        };
        this._options = {
        };
        this.cssSelector = $.extend({}, this._cssSelector, cssSelector);
		this.options = $.extend({}, this._options, options);
        this.init = function() {
            $(self.cssSelector.checkboxSelector).on('click', function(){
                self.clickCheckbox(this);
            });
            $(self.cssSelector.hlinkSelector).on('click', function(){
                self.clickHLink(this);
            });
        };
        this.clickCheckbox = function(el) {
            href = $(el).data('href');
            window.location.href = href;
        };
        this.clickHLink = function(el) {
            checkbox = $(el).prev();
            if (checkbox.prop("checked")) {
                checkbox.prop("checked", false);
            } else {
                checkbox.prop("checked", true);
            }
        };
        this.init();
    };
});