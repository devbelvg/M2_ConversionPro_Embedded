define([
    'jquery',
    'jquery-ui-modules/slider',
    'priceUtils'
], function ($, undefined, priceUtils) {
    return function(cssSelector, options) {
        var self = this;
        this._cssSelector = {
            sliderElement: "#celebros-price-slider",
            sliderAmount: "#celebros-price-slider-amount",
            applyButton: "#celebros-price-slider-apply-button"
        };
        this._options = {
            minPrice: 10,
            maxPrice: 20,
            currentMinPrice: 10,
            currentMaxPrice: 20,
            amountHtml: "<input type='text' readonly value='{min_val}' class='amount-min'/><input type='text' readonly value='{max_val}' class='amount-max'/>"
        };
        this.cssSelector = $.extend({}, this._cssSelector, cssSelector);
        this.options = $.extend({}, this._options, options);
        this.format = function(string, args) {
            for (k in args) {
                string = string.replace("{" + k + "}", args[k])
            }
            return string;
        };
        this.applyPrice = function(el) {
            var url = $(el).data('url');
            var minPrice = $(self.cssSelector.sliderElement).slider("values", 0);
            var maxPrice = $(self.cssSelector.sliderElement).slider("values", 1);
            if (minPrice < maxPrice) {
                var price_value = '_P' + minPrice + '_' + maxPrice;
                window.location = url.replace("PRICE_RANGE", price_value);
            }
        };
        this.applyButtonState = function(state) {
            if (state) {
                $(self.cssSelector.applyButton).prop("disabled", false);
            }
        }
        this.init = function() {
            $( function() {
                $(self.cssSelector.sliderElement).slider({
                    range: true,
                    min: self.options.minPrice,
                    max: self.options.maxPrice,
                    values: [ self.options.currentMinPrice, self.options.currentMaxPrice ],
                    slide: function(event, ui) {
                        var min_val = priceUtils.formatPrice(ui.values[0]);
                        var max_val = priceUtils.formatPrice(ui.values[1]);
                        var html = self.format(self.options.amountHtml, {min_val, max_val});
                        $(self.cssSelector.sliderAmount).html(html);
                        self.applyButtonState(1);
                    }
                });
                var min_val = priceUtils.formatPrice($(self.cssSelector.sliderElement).slider("values", 0));
                var max_val = priceUtils.formatPrice($(self.cssSelector.sliderElement).slider("values", 1));
                $(self.cssSelector.sliderAmount).html(
                    self.format(self.options.amountHtml, {min_val, max_val})
                );
                $(self.cssSelector.applyButton).on('click', function(){
                    self.applyPrice(this);
                });
            });
        };
        this.init();
    };
});