"use strict";

(function (window, $) {
    window.ContentTableController = function ($wrapper, controller) {
        this.$wrapper = $wrapper;
        this.controller = controller;
        this.$tableContainer = $wrapper.find(".js-table-container");
        this.$rowsCount = $wrapper.find(".js-rows-count-container");
        this.$table = $wrapper.find(".js-main-table");

        let subjectType = null;
        let subjectId = null;
        window.subjectTypes.forEach(function (value) {
            let id = $.query.get(value);
            if (id !== "") {
                subjectType = value;
                subjectId = id;
            }
        });

        if (subjectType && subjectId) {
            let row = this.$table.find(
                "[data-subject-id=\"" + subjectId + "\"][data-subject-type=\"" + subjectType + "\"]"
            );
            row = row.length === 1 ? row : null;

            this._setCurrentSubject({
                id: subjectId,
                type: subjectType,
                row: row
            });
        }

        this.applyTableSorter();

        this.$wrapper.on(
            "click",
            "tbody tr",
            this.updateCurrentRow.bind(this)
        );

        this.$wrapper.on(
            "change",
            "tbody .js-update-state-cell select",
            this.updateState.bind(this)
        );

        this.$wrapper.on(
            "click",
            "tbody .js-update-state-cell select",
            function (e) {e.stopPropagation();}
        );
    };

    $.extend(window.ContentTableController.prototype, {
        _setCurrentSubject: function (subject) {
            this.$table.find(".active-row").removeClass("active-row");

            if(subject.row === null) {
                let $row = this.$table.find(
                    "[data-subject-id=\"" + subject.id + "\"][data-subject-type=\"" + subject.type + "\"]"
                );
                if ($row.length === 1) {
                    subject.row = $row;
                }
            }

            if (subject.row) {
                subject.row.addClass("active-row");
            }

            this.controller.currentSubject = subject;
        },

        applyTableSorter: function () {
            this.$tableContainer.find(".js-main-table").tablesorter({
                dateFormat: "ddmmyyyy"
            });
        },

        updateCurrentRow: function (e) {
            let $row = $(e.currentTarget);
            let subject = new Subject(
                $row.data("subjectId"),
                $row.data("subjectType"),
                $row,
                null
            );
            this.controller.reloadDetails(subject);
            this._setCurrentSubject(subject);
        },

        updateState: function (e) {
            e.stopPropagation();
            let $select = $(e.currentTarget);
            let self = this;
            let $row = $(e.currentTarget).closest("tr");
            let id = $row.data("subject-id");
            let type = $row.data("subject-type");
            let currentSubject = this.controller.currentSubject;
            let $cell = $select.parent();
            let $placeholder = $cell.find(".js-reload-placeholder");

            $select.css({display: "none"});
            $placeholder.css({display: "block"});
            $.ajax({
                url: "/api/" + type + "/" + id + "/state",
                method: "PUT",
                data: {state: $select.val()},
                success: function (data) {
                    executeAfter(function () {
                        $select.css({display: "block"});
                        $placeholder.css({display: "none"});
                        $select.attr("data-state", $select.val());
                        if (type === currentSubject.type &&
                            id === currentSubject.id) {
                            self.controller.detailsController.reload(currentSubject);
                        }
                    });
                },
                error: function (jqXHR) {
                    $placeholder.html("Wystąpił błąd.");
                    executeAfter(function () {
                        $placeholder.html(window.reloadIcon);
                        $placeholder.css({display: "none"});
                        $select.css({display: "block"});
                    }, Date.now() + 5000);
                    self.controller.popupManager.display(jqXHR.responseText);
                }
            });
        },

        reload: function () {
            let self = this;
            let url = this.$table.data("source-url");
            $.ajax({
                url: url,
                method: "GET",
                success: function (data) {
                    self.$tableContainer.addClass("hidden");
                    executeAfter(function () {
                        if (typeof data === "object") {
                            let rowsCount = data.rowsCount;
                            data = data.table;
                            self.$rowsCount.html(rowsCount);
                        }

                        self.$tableContainer.html(data);
                        self.$table = self.$wrapper.find(".js-main-table");
                        self.setAndHighlightCurrent();
                        self.$tableContainer.removeClass("hidden");
                        self.applyTableSorter();
                    });
                },
                error: function (jqXHR) {
                    self.controller.popupManager.display(jqXHR.responseText);
                }
            });
            this.controller.detailsController.reload(this.controller.currentSubject);
        },

        setAndHighlightCurrent: function (subject) {
            if (subject === null) {
                this.controller.currentSubject = subject;
                this.$table.find(".active-row").removeClass("active-row");
                return;
            }
            if (subject === undefined) {
                subject = this.controller.currentSubject;
            }

            if (subject === undefined || subject === null) {
                this.$table.find(".active-row").removeClass("active-row");
                return;
            }

            this.$table.find(".active-row").removeClass("active-row");
            let $rows = this.$table.find("tr");
            let $found = null;
            for (let i = 1; i < $rows.length; i++) {
                let $row = $($rows[i]);
                if ($row.data("subject-type") === subject.type &&
                    $row.data("subject-id") === subject.id) {
                    $found = $row;
                    break;
                }
            }

            if ($found) {
                $found.addClass("active-row");
            }
        },
    });
})(window, jQuery);