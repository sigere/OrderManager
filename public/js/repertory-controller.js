"use strict";

(function (window, $) {
    window.Controller = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.currentSubject = null;

        this.filtersController = new FiltersController(
            $(".js-left-col"),
            this
        );

        this.contentTableController = new ContentTableController(
            $(".js-mid-col"),
            this
        );

        this.detailsController = new DetailsController(
            $(".js-right-col"),
            this
        );

        this.popupManager = new PopupManager(
            $("body")
        );

        this._initListeners.bind(this)();
        window.onpopstate = window._onPopState.bind(this);
    };

    $.extend(window.Controller.prototype, {
        reloadDetails: function (subject) {
            this.detailsController.reload(subject);
        },

        reloadTable: function () {
            this.contentTableController.reload();
        },

        editOrder: function () {
            let subject = {
                id: this.$wrapper.find(".js-order-id").data("order"),
                type: "order"
            };

            this.detailsController.edit(subject);
        },

        formSubmit: function (e) {
            e.preventDefault();
            let self = this;
            let data = new FormData(e.currentTarget);
            let url = $(e.currentTarget).data("url");
            let method = $(e.currentTarget).data("method");
            if (method !== "POST") {
                data.append("_method", method);
            }

            this.popupManager.default();
            $.ajax({
                url: url,
                method: "POST",
                data: data,
                processData: false,
                contentType: false,
                success: function (data) {
                    self.reloadTable();
                    executeAfter(function () {
                        let $handle = self.popupManager.display(data);
                        if (!$handle) {
                            return;
                        }
                        $handle.find("form").on(
                            "submit",
                            self.formSubmit.bind(self)
                        );
                    });
                },
                error: function (jqXHR) {
                    console.error(jqXHR);
                    self.popupManager.display(jqXHR.responseText);
                }
            });
        },

        _initListeners: function () {
            this.$wrapper.on(
                "click",
                ".js-burger .js-edit-link",
                function () {
                    this.detailsController.edit(this.currentSubject);
                }.bind(this)
            );

            this.$wrapper.on(
                "click",
                ".js-burger .js-edit-order-link",
                this.editOrder.bind(this)
            );

            this.$wrapper.on(
                "click",
                ".js-burger .js-share-link",
                function (e) {
                    this.detailsController.share(e);
                }.bind(this)
            );
        }
    });
})(window, jQuery);