"use strict";

(function (window, $) {
    window.Controller = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.$ordersTableContainer = $wrapper.find(".js-orders-table-container");

        this.popupManager = new PopupManager(
          $("body")
        );

        this._initListeners.bind(this)();
    };

    $.extend(window.Controller.prototype, {
        selectClient: function (e) {
            let $row = $(e.currentTarget);
            this.currentClient = {
                type: "client",
                id: $row.data("subject-id")
            };
            this.reloadOrders();
        },

        reloadOrders: function () {
            let self = this;

            self.$ordersTableContainer.addClass("hidden");
            $.ajax({
                url: "/invoices/client/" + self.currentClient.id,
                method: "GET",
                success: function (data) {
                    executeAfter(function () {
                        console.log("ajax data:", data);

                        self.$ordersTableContainer.html(data.orders);
                        self.$ordersTableContainer.removeClass("hidden");
                        self.applyTableSorter();
                    });
                },
                error: function (jqXHR) {
                    self.popupManager.display(jqXHR.responseText);
                }
            });
        },

        applyTableSorter: function () {
            this.$ordersTableContainer.find(".js-orders-table").tablesorter({
                dateFormat: "ddmmyyyy"
            });
        },

        _initListeners: function () {
            this.$wrapper.on(
                "click",
                ".js-left-col tbody tr",
                this.selectClient.bind(this)
            );
        }
    });
})(window, jQuery);