class InvoicesController {
    clientsTableContainer;
    ordersTableContainer;
    selectedRow;
    summaryContent;
    buyerDataContent;
    currentId;
    ordersForm;
    overlay;
    centerPopup;
    centerPopupContent;
    invoiceButton;
    settledButton;

    constructor() {
        this.clientsTableContainer = document
            .getElementsByClassName("left-col")[0]
            .getElementsByClassName("table-container")[0];
        this.ordersTableContainer = document
            .getElementsByClassName("mid-col")[0]
            .getElementsByClassName("table-container")[0];
        this.currentId = null;
        this.selectedRow = null;
        this.ordersForm = null;
        this.overlay = document.getElementById("overlay");
        this.centerPopup = document.getElementById("center-popup");
        this.centerPopupContent = document.getElementById("center-popup-content");
        this.buyerDataContent = document.getElementById("buyer-data");
        this.summaryContent = document.getElementById("summary-content");
        this.invoiceButton = document.getElementById("button-execute-invoice");
        this.settledButton = document.getElementById("button-execute-settled");

        this.invoiceButton.addEventListener("click", this.executeInvoice.bind(this), false);
        this.addTableListeners();
    }

    addTableListeners() {
        let tableRows = this.clientsTableContainer.getElementsByTagName("tr");
        for (let i = 1; i < tableRows.length; i++) {
            let row = tableRows[i];
            let clientId = row.getAttribute("client-id");
            let cells = row.getElementsByTagName("td");
            for (let j = 0; j < cells.length; j++) {
                cells[j].addEventListener("click", this.reloadOrders.bind(this, clientId), false);
            }
        }
        $(function () {
            $("#main-table").tablesorter();
        });
    }

    updateSelected() {
        let tableRows = this.clientsTableContainer.getElementsByTagName("tr");
        if (!this.currentId)
            return;
        if (this.selectedRow)
            this.selectedRow.classList.toggle("active-row");
        for (let i = 1; i < tableRows.length; i++) {
            let row = tableRows[i];
            let clientId = row.getAttribute("client-id");
            if (clientId === this.currentId) {
                this.selectedRow = row;
                break;
            }
        }
        this.selectedRow.classList.toggle("active-row");
    }

    reloadOrders(clientId) {
        let c = this;

        if (!clientId) {
            c.ordersTableContainer.classList.toggle("hidden");
            setTimeout(function () {
                c.ordersTableContainer.innerHTML = '';
                c.ordersTableContainer.classList.toggle("hidden");
            }, 400);
            return;
        }

        let ordersTableContainer = this.ordersTableContainer;
        let buyerDataContent = this.buyerDataContent;
        let ordersOk = false;
        let clientOk = false;
        let stamp = Date.now();

        let ordersRequest = new XMLHttpRequest();
        ordersRequest.open("POST", "invoices/api/reloadOrders/" + clientId, true);

        let clientRequest = new XMLHttpRequest();
        clientRequest.open("POST", "invoices/api/reloadClient/" + clientId, true);

        function check() {
            if (ordersOk && clientOk) {
                setTimeout(function () {
                    c.ordersTableContainer.innerHTML = ordersRequest.responseText;
                    c.buyerDataContent.innerHTML = clientRequest.responseText;
                    c.currentId = clientId;
                    c.updateSelected();
                    c.ordersForm = document.getElementById("orders-form");
                    c.ordersTableContainer.classList.toggle("hidden");
                    c.summaryContent.classList.toggle("hidden");
                }, 400 - (Date.now() - stamp) > 0 ? 400 - (Date.now() - stamp) : 0)
            }
        }

        ordersRequest.onload = function () {
            if (ordersRequest.status === 200) {
                ordersOk = true;
                check();
            } else {
                ordersTableContainer.innerHTML =
                    '<div class="alert alert-danger" role="alert">Wystąpił błąd "' +
                    ordersRequest.status +
                    " " +
                    ordersRequest.statusText +
                    '" podczas ładowania danych</div>';
            }
        }

        clientRequest.onload = function () {
            if (clientRequest.status === 200) {
                clientOk = true;
                check();
            } else {
                buyerDataContent.innerHTML =
                    '<div class="alert alert-danger" role="alert">Wystąpił błąd "' +
                    clientRequest.status +
                    " " +
                    clientRequest.statusText +
                    '" podczas ładowania danych</div>';
            }
        }

        this.ordersTableContainer.classList.toggle("hidden");
        this.summaryContent.classList.toggle("hidden");
        stamp = Date.now();
        ordersRequest.send();
        clientRequest.send();
    }

    executeInvoice(e) { //TODO
        e.preventDefault();
        if (!this.currentId || !this.ordersForm)
            return;

        let c = this;
        let popup = this.centerPopup;
        let popupContent = this.centerPopupContent;
        let post = new FormData(document.getElementsByClassName("summary-form")[0]);

        let orders = new FormData(this.ordersForm);
        let tmp = [];
        for (let pair of orders.entries()) {
            post.append("orders[]", pair[0]);
        }
        post.append("client", this.currentId);

        let request = new XMLHttpRequest();
        request.open("POST", "invoices/api/execute", true);
        request.onload = function () {
            c.overlay.style.display = "block";
            popup.classList.toggle("active");
            if (request.status === 200) {
                popupContent.innerHTML = request.responseText;
                c.buyerDataContent.innerHTML = '<div class="alert alert-info">Wybierz klienta, by wystawić fakturę</div>';
                c.reloadClients();
            } else {
                popupContent.innerHTML = request.responseText;
            }
        };
        request.send(post);
    }

    reloadClients() {
        this.currentId = null;
        this.selectedRow = null;
        let c = this;
        let request = new XMLHttpRequest();
        request.open("POST", "invoices/api/reloadClients", true);
        request.onload = function () {
            c.clientsTableContainer.innerHTML = request.responseText;
            c.clientsTableContainer.classList.toggle("hidden");
            c.addTableListeners();
            c.reloadOrders(null);
        };
        c.clientsTableContainer.classList.toggle("hidden");
        request.send();
    }

}

let c = new InvoicesController();



