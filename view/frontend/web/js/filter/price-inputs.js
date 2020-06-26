define([
    'jquery'
], function ($) {
	return function(cssSelector, options) {
        var self = this;
        this._cssSelector = {
            applyButton: "#price-apply-button",
            inputPriceMin: "#input-price-min",
            inputPriceMax: "#input-price-max"
        };
        this._options = {
            
            
        };
        this.cssSelector = $.extend({}, this._cssSelector, cssSelector);
		this.options = $.extend({}, this._options, options);
        this.applyPrice = function(el) {
            var prices = self.minMaxCheck();
            var url = $(el).data('url');
            if (prices[0] < prices[1]) {
                price_value = '_P' + prices[0] + '_' + prices[1];
                window.location = url.replace("PRICE_RANGE", price_value);
            }
        };
        this.minMaxCheck = function() {
            var min = $(self.cssSelector.inputPriceMin).val() ? parseInt($(self.cssSelector.inputPriceMin).val()) : parseInt($(self.cssSelector.inputPriceMin).data('min-price'));
            min = isNaN(min) ? 0 : min;
            var max = $(self.cssSelector.inputPriceMax).val() ? parseInt($(self.cssSelector.inputPriceMax).val()) : parseInt($(self.cssSelector.inputPriceMax).data('max-price'));
            max = isNaN(max) ? 0 : max;
            if (max > $(self.cssSelector.inputPriceMax).data('max-price')) {
                max = $(self.cssSelector.inputPriceMax).data('max-price');
                $(self.cssSelector.inputPriceMax).val(max);
            }
            if (min < $(self.cssSelector.inputPriceMin).data('min-price')) {
                min = $(self.cssSelector.inputPriceMin).data('min-price');
                $(self.cssSelector.inputPriceMin).val(min);
            }
            
            this.applyButtonState(1);
            return [min, max];
        };
        this.applyButtonState = function(state) {
            if (state) {
                $(self.cssSelector.applyButton).prop("disabled", false);
            }
        }
        this.init = function() {
            $(self.cssSelector.applyButton).on('click', function(){
                self.applyPrice(this);
            });
            $(self.cssSelector.inputPriceMin+','+self.cssSelector.inputPriceMax).on('change', function(){
                self.minMaxCheck(this);
            });
        };
        this.init();
    };
});