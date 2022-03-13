"use strict";

(function (window, $) {
    window.Controller = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.$popup = $wrapper.find(".js-popup");
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

        this.$wrapper.on(
            "click",
            "header .js-add-order-link",
            this.addOrder.bind(this)
        );

        this.$wrapper.on(
            "click",
            ".js-burger .js-edit-link",
            this.edit.bind(this)
        );

        this.$wrapper.on(
            "click",
            ".js-burger .js-repertory-entry-link",
            this.addRepertoryEntry.bind(this)
        );

        this.$wrapper.on(
            "click",
            ".js-burger .js-delete-link",
            this.delete.bind(this)
        );

        this.$wrapper.on(
            "click",
            ".js-burger .js-restore-link",
            this.restore.bind(this)
        );
    };

    $.extend(window.Controller.prototype, {
        addOrder: function () {
            this.popupManager.open();

            let self = this;
            $.ajax({
                url: "/order",
                method: "POST",
                success: function (data) {
                    executeAfter( function () {
                        let $handle = self.popupManager.display(data);
                        if (!$handle) {
                            return;
                        }
                        $handle.find("form").on(
                            "submit",
                            self.formSubmit.bind(self)
                        );
                    }, Date.now() + 400);
                },
                error: function (jqXHR) {
                    self.popupManager.display(jqXHR.responseText);
                }
            });
        },

        edit: function () {
            this.popupManager.open();
            let currentSubject = this.currentSubject;

            let self = this;
            $.ajax({
                url: "/" + currentSubject.type + "/" + currentSubject.id,
                method: "PUT",
                success: function (data) {
                    executeAfter( function () {
                        let $handle = self.popupManager.display(data);
                        if (!$handle) {
                            return;
                        }
                        $handle.find("form").on(
                            "submit",
                            self.formSubmit.bind(self)
                        );
                    }, Date.now() + 400);
                },
                error: function (jqXHR) {
                    self.popupManager.display(jqXHR.responseText);
                }
            });
        },

        addRepertoryEntry: function () {
            this.popupManager.open();
            let currentSubject = this.currentSubject;
            let data = new FormData();
            data.append("order", currentSubject.id);

            let self = this;
            $.ajax({
                url: "/repertory/entry",
                method: "POST",
                processData: false,
                contentType: false,
                data: data,
                success: function (data) {
                    executeAfter( function () {
                        let $handle = self.popupManager.display(data);
                        if (!$handle) {
                            return;
                        }
                        $handle.find("form").on(
                            "submit",
                            self.formSubmit.bind(self)
                        );
                    }, Date.now() + 400);
                },
                error: function (jqXHR) {
                    self.popupManager.display(jqXHR.responseText);
                }
            });
        },

        delete: function () {
            this.popupManager.open();
            let currentSubject = this.currentSubject;

            let self = this;
            $.ajax({
                url: "/" + currentSubject.type + "/" + currentSubject.id,
                method: "DELETE",
                success: function (data) {
                    executeAfter( function () {
                        let $handle = self.popupManager.display(data);
                        if (!$handle) {
                            return;
                        }
                        $handle.find("form").on(
                            "submit",
                            self.formSubmit.bind(self)
                        );
                    }, Date.now() + 400);
                },
                error: function (jqXHR) {
                    console.log(jqXHR);
                    self.popupManager.display(jqXHR.responseText);
                }
            });
        },

        restore: function () {
            this.popupManager.open();
            let currentSubject = this.currentSubject;

            let self = this;
            $.ajax({
                url: "/" + currentSubject.type + "/" + currentSubject.id + "/restore",
                method: "POST",
                success: function (data) {
                    executeAfter( function () {
                        let $handle = self.popupManager.display(data);
                        if (!$handle) {
                            return;
                        }
                        $handle.find("form").on(
                            "submit",
                            self.formSubmit.bind(self)
                        );
                    }, Date.now() + 400);
                },
                error: function (jqXHR) {
                    console.log(jqXHR);
                    self.popupManager.display(jqXHR.responseText);
                }
            });
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
                    }, Date.now() + 400);
                },
                error: function (jqXHR) {
                    console.log(jqXHR);
                    self.popupManager.display(jqXHR.responseText);
                }
            });
        },

        reloadDetails: function (subject) {
            this.detailsController.reload(subject);
        },

        reloadTable: function () {
            this.contentTableController.reload();
        }
    });
})(window, jQuery);