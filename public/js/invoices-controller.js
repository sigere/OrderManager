"use strict";

(function (window, $) {
    window.Controller = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.$ordersTableContainer = $wrapper.find(".js-orders-table-container");
        this.$clientsTableContainer = $wrapper.find(".js-clients-table-container");
        this.$buyerData = $wrapper.find(".js-buyer-data");

        this.popupManager = new PopupManager(
          $("body")
        );

        this._initListeners.bind(this)();
        let self = this;
        $(document).ajaxComplete(function(event, request, settings) {
            if (request.getResponseHeader("Set-Current-Subject") !== null) {
                self.reloadClient();
            }
        });
    };

    $.extend(window.Controller.prototype, {
        reloadClients: function (e) {
            let month = this.$wrapper.find(".js-form-month select[name='invoice_month_form[month]']").val();
            let year = this.$wrapper.find(".js-form-month select[name='invoice_month_form[year]']").val();

            this.$clientsTableContainer.addClass("hidden");
            let self = this;
            $.ajax({
                url: "/invoices/client",
                method: "GET",
                data: {
                    month: month,
                    year: year
                },
                success: function (data) {
                    executeAfter(function () {
                        self.$clientsTableContainer.html(data);
                        self.$clientsTableContainer.removeClass("hidden");
                    });
                },
                error: function (jqXHR) {
                    console.error(jqXHR.responseText);
                    self.popupManager.display(window.formatter.error(jqXHR.responseText));
                }
            });
        },

        selectClient: function (e) {
            let $row = $(e.currentTarget);
            this.currentClient = {
                type: "client",
                id: $row.data("subject-id")
            };
            this.reloadClient();
        },

        reloadClient: function () {
            let self = this;

            self.$ordersTableContainer.addClass("hidden");
            self.$buyerData.addClass("hidden");
            $.ajax({
                url: "/invoices/client/" + self.currentClient.id,
                method: "GET",
                success: function (data) {
                    executeAfter(function () {
                        self.$ordersTableContainer.html(data.orders);
                        self.$buyerData.html(data.client);

                        self.$ordersTableContainer.removeClass("hidden");
                        self.$buyerData.removeClass("hidden");
                        self.applyTableSorter();
                    });
                },
                error: function (jqXHR) {
                    console.error(jqXHR);
                    self.popupManager.display(jqXHR.responseText);
                }
            });
        },

        settle: function (invoice = false, e) {
            e.preventDefault();
            if (!this.currentClient) {
                this.popupManager.display(window.formatter.error("Nie wybrano klienta"));
                return;
            }

            let $buttons = this.$wrapper.find(".js-buttons");
            let buttonsHtml = $buttons.html();

            let orders = this._getOrders();
            let issueDate = this.$wrapper.find(".js-issue-date").val();
            let paymentDate = this.$wrapper.find(".js-payment-date").val();
            let url = invoice ? "/invoices/invoice" : "/invoices/settle";
            let method = invoice ? "POST" : "PUT";

            $buttons.html(window.reloadIcon);
            let self = this;
            console.log(orders);
            return;
            $.ajax({
                url: url,
                method: method,
                data: {
                    client: self.currentClient.id,
                    orders: orders,
                    issue_date: issueDate,
                    payment_date: paymentDate
                },
                success: function (data) {
                    self.popupManager.display(data);
                    executeAfter(function () {
                       $buttons.html(buttonsHtml);
                    });
                    self.reloadClient();
                },
                error: function (jqXHR) {
                    executeAfter(function () {
                        $buttons.html(buttonsHtml);
                    });
                    console.error(jqXHR);
                    self.popupManager.display(jqXHR.responseText);
                }
            });
        },

        updateNetto: function () {
            let $nettoSummaryCell = this.$wrapper.find(".js-orders-table .js-netto-summary");
            let $rows = this.$ordersTableContainer.find("tr[data-valid='1']");

            let sum = 0;
            for (let i=0; i<$rows.length; i++) {
                let $row = $($rows[i]);
                if ($row.find("input").is(":checked")) {
                    sum += $row.data("netto");
                }
            }
            $nettoSummaryCell.html(sum + "PLN");
        },

        onRowClicked: function (e) {
            let $row = $(e.target).closest("tr");
            let checkbox = $row.find("input[type='checkbox']");
            checkbox = checkbox.length === 1 ? checkbox[0] : null;

            this.updateNetto();
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
            }
        },

        applyTableSorter: function () {
            this.$ordersTableContainer.find(".js-orders-table").tablesorter({
                dateFormat: "ddmmyyyy"
            });
        },

        openOrderInNewTab: function (e) {
            let $row = $(e.currentTarget).closest("tr");
            let subject = {
                id: $row.data("subject-id"),
                type: $row.data("subject-type")
            };

            let self = this;
            this.popupManager.open();
            $.ajax({
                url: getUrlForSubject(subject),
                method: "PUT",
                success: function (data) {
                    executeAfter( function () {
                        let $handle = self.popupManager.display(data);
                        if (!$handle) {
                            return;
                        }
                        $handle.find("form").on(
                            "submit",
                            self.formSubmit.bind(controller)
                        );
                    });
                },
                error: function (jqXHR) {
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

        _getOrders: function () {
            let $rows = this.$ordersTableContainer.find("tr[data-valid='1']");
            let ids = [];
            for (let i = 0; i < $rows.length; i++) {
                let $row = $($rows[i]);
                if ($row.data("subject-type") === "order") {
                    ids.push($row.data("subject-id"));
                }
            }
            return ids;
        },

        _initListeners: function () {
            let self = this;

            this.$wrapper.on(
                "change",
                ".js-form-month select",
                this.reloadClients.bind(this)
            );

            this.$wrapper.on(
                "click",
                ".js-left-col tbody tr",
                this.selectClient.bind(this)
            );


            this.$wrapper.on(
                "click",
                ".js-orders-table input[type=\"checkbox\"]",
                this.updateNetto.bind(this)
            );

            this.$wrapper.on(
                "click",
                ".js-button-invoice",
                this.settle.bind(this, true)
            );

            this.$wrapper.on(
                "click",
                ".js-button-settle",
                this.settle.bind(this, false)
            );

            this.$wrapper.on(
                "click",
                ".js-edit-order-link",
                this.openOrderInNewTab.bind(this)
            );
        }
    });
})(window, jQuery);