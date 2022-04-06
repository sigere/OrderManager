"use strict";

(function (window, $) {
    window.Controller = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.currentSubject = null;

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
        let self = this;
        $(document).ajaxComplete(function(event, request, settings ) {
            let header = request.getResponseHeader("Set-Current-Entity");
            if (header !== null) {
                header = header.split("/");
                self.currentSubject = {
                    type: header[0],
                    id: header[1]
                };
                self.reloadTable();
                return;
            }

            header = request.getResponseHeader("Reload-Table");
            if (header !== null) {
                self.reloadTable();
            }
        });
    };

    $.extend(window.Controller.prototype, {
        reloadDetails: function (subject) {
            this.detailsController.reload(subject);
        },

        reloadTable: function () {
            this.contentTableController.reload();
        },

        addTask: function () {
            this.popupManager.open();

            let self = this;
            $.ajax({
                url: window.getUrlForSubject({type: "task"}),
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
                    });
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
                url: "/tasks/" + currentSubject.type + "/" + currentSubject.id,
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
                    });
                },
                error: function (jqXHR) {
                    console.error(jqXHR);
                    self.popupManager.display(jqXHR.responseText);
                }
            });
        },

        editTask: function () {
            let subject = {
                id: this.$wrapper.find(".js-task-id").data("task"),
                type: "task"
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
                ".js-add-task-link",
                this.addTask.bind(this)
            );

            this.$wrapper.on(
                "click",
                ".js-burger .js-edit-link",
                function () {
                    this.detailsController.edit(this.currentSubject);
                }.bind(this)
            );

            this.$wrapper.on(
                "click",
                ".js-burger .js-delete-link",
                this.delete.bind(this)
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