"use strict";

(function (window, $) {
    window.FiltersController = function ($wrapper, controller) {
        this.$wrapper = $wrapper;
        this.controller = controller;
        console.log($wrapper);

        this.$wrapper.on(
            "submit",
            "form",
            this.submit.bind(this)
        );
    };

    $.extend(window.FiltersController.prototype, {
        submit: function (e) {
            e.preventDefault();
            let self = this;
            let data = new FormData(e.currentTarget);
            let url = $(e.currentTarget).data("url");
            let method = $(e.currentTarget).data("method");
            if (method !== "POST") {
                data.append("_method", method);
            }

            $.ajax({
                url: url,
                method: "POST",
                data: data,
                processData: false,
                contentType: false,
                success: function (data) {
                    self.controller.reloadTable();
                },
                error: function (jqXHR) {
                    console.log(jqXHR);
                    popupManager.display(jqXHR.responseText);
                }
            });
        }
    });
})(window, jQuery);