define([
    'jquery'
], function ($) {
	return function(cssSelector, options) {
        var self = this;
        this._cssSelector = {
            mainSelector: '.items',
            itemSelector: 'li.item',
            moreClass: 'more-button',
            lessClass: 'less-button',
            hiddenClass: 'hidden'
        };
        this._options = {
            collapsedQty: 15
        };
        this.cssSelector = $.extend({}, this._cssSelector, cssSelector);
		this.options = $.extend({}, this._options, options);
        this.bindButtons = function(type) {
            $(self.cssSelector.mainSelector).find("[data-cel-collapse="+type+"]").each( function() {
                $(this).on('click', function() {
                    var elements = $(this).parent().find(self.cssSelector.itemSelector);
                    elements.each( function(i, e) {
                        if (type == 'more') {
                            if ($(e).hasClass(self.cssSelector.moreClass)) {
                                $(e).addClass(self.cssSelector.hiddenClass);
                            } else {
                                $(e).removeClass(self.cssSelector.hiddenClass);
                            };
                        } else if (type == 'less'){
                        if (i >= self.options.collapsedQty && !$(e).hasClass(self.cssSelector.moreClass)) {
                            $(e).addClass(self.cssSelector.hiddenClass);
                        } else {
                            $(e).removeClass(self.cssSelector.hiddenClass);
                        }
                        }
                    });
                });
            });
        };
        this.init = function() {
            this.bindButtons('more');
            this.bindButtons('less');
        };
        this.init();
    };
});