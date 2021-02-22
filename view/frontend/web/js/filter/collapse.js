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
        this.status = 'less';
        this.cssSelector = $.extend({}, this._cssSelector, cssSelector);
        this.options = $.extend({}, this._options, options);
        this.bindButtons = function(type) {
            $(self.cssSelector.mainSelector).find("[data-cel-collapse="+type+"]").each( function() {
                var button = this;
                if (type == 'more') {
                    $(this).parent().parent().on('celFilterApplied', function() {
                        self.showElements(this, type);
                    }); 

                    $(this).on('click', function() {
                        self.status = type;
                        self.showElements(this, type);
                    });
                }

                if (type == 'less') {
                    $(this).parent().parent().on('celFilterEmpty', function() {
                        self.showElements(this, type);
                    });

                    $(this).on('click', function() {
                        self.status = type;
                        self.showElements(this, type);
                    });
                }
            });
        };
        this.init = function() {
            this.bindButtons('more');
            this.bindButtons('less');
        };
        this.showElements = function(element, type) {
            var elements = $(element).parent().find(self.cssSelector.itemSelector);
            if (type == 'less') {
                this.showLessElements(elements);
            } else {
                this.showMoreElements(elements);
            }
        };
        this.showLessElements = function(elements) {
            elements.each( function(i, e) {
                if (i >= self.options.collapsedQty && !$(e).hasClass(self.cssSelector.moreClass)) {
                    $(e).addClass(self.cssSelector.hiddenClass);
                } else {
                    $(e).removeClass(self.cssSelector.hiddenClass);
                }
            });  
        };
        this.showMoreElements = function(elements) {
            elements.each( function(i, e) {
                if ($(e).hasClass(self.cssSelector.moreClass)) {
                    $(e).addClass(self.cssSelector.hiddenClass);
                } else {
                    $(e).removeClass(self.cssSelector.hiddenClass);
                };
            }); 
        };
        this.init();
    };
});