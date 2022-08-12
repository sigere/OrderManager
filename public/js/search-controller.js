"use strict";

(function (window, $) {
    window.SearchController = function ($wrapper, controller, endpointUrl) {
        this.$wrapper = $wrapper;
        this.controller = controller;
        this.endpointUrl = endpointUrl;

        this.$wrapper.on(
            "click",
            // no need to specify selector, $wrapper is the target (.js-search-link)
            this.search.bind(this)
        );

        $(document).on(
            "click",
            ".search-form-container .entity-table tbody tr",
            this.updateCurrentSubject.bind(this)
        );
    };

    $.extend(window.SearchController.prototype, {
        search: function (e) {
            this.controller.popupManager.open();
            let self = this;
            $.ajax({
                url: self.endpointUrl,
                method: "POST",
                success: function (data) {
                    executeAfter( function () {
                        let $handle = self.controller.popupManager.display(data);
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
                    console.error(jqXHR.responseText);
                    self.controller.popupManager.display(jqXHR.responseText);
                }
            });
        },

        updateCurrentSubject: function(e) {
            let $row = $(e.currentTarget);
            let subject = new Subject(
                $row.data("subjectId"),
                $row.data("subjectType"),
                null,
                null
            );
            this.controller.reloadDetails(subject);
            this.controller.contentTableController._setCurrentSubject(subject);
            this.controller.popupManager.close();
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

            this.controller.popupManager.default();
            $.ajax({
                url: url,
                method: "POST",
                data: data,
                processData: false,
                contentType: false,
                success: function (data) {
                    executeAfter(function () {
                        let $handle = self.controller.popupManager.display(data);
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
                    self.controller.popupManager.display(jqXHR.responseText);
                }
            });
        },
    });
})(window, jQuery);