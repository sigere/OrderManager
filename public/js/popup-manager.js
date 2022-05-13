"use strict";

(function (window, $) {
    window.PopupManager = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.$overlay = $wrapper.find(".js-overlay");
        this.$popup = $wrapper.find(".js-popup");
        this.defaultContent = this.$popup.html();
        this.active = false;

        this.$overlay.on(
            "click",
            this.close.bind(this)
        );
    };

    $.extend(window.PopupManager.prototype, {
        open: function () {
            this.$overlay.addClass("active");
            this.$popup.addClass("active");
            this.active = true;
        },

        close: function () {
            this.$overlay.removeClass("active");
            this.$popup.removeClass("active");
            executeAfter(function () {
                this.$popup.html(this.defaultContent);
            }.bind(this), Date.now() + 250);
            this.active = false;
        },

        default: function () {
          this.$popup.html(this.defaultContent);
          this.active = true;
        },

        display: function (data) {
            if (!this.active) {
                this.open();
            }

            this.$popup.html(data);
            return this.$popup;
        }
    });
})(window, jQuery);