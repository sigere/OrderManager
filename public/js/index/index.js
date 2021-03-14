var centerPopup = document.getElementById("center-popup");
var centerPopupContent = document.getElementById("center-popup-content");
var debug = document.getElementById("debug");
var deleteButton = document.getElementById("delete-button");
var currentOrderId = null;
var tableContainer = document
    .getElementsByClassName("mid-col")[0]
    .getElementsByClassName("table-container")[0];


class Controller {
    tableContainer;
    tableRows;
    form;
    detailsHeaderId;
    detailsContent;
    currentId;

    constructor(tableContainer, form) {
        this.tableContainer = tableContainer;
        this.form = form;
        this.detailsHeaderId = document.getElementById("details-header-id");
        this.detailsContent = document.getElementById("details-content");
        this.currentId = null;
        this.form.addEventListener("submit", e => this.execute(e), false);
        this.tableRows = this.tableContainer.getElementsByTagName("tr");
        this.addTableListeners();
    }

    addTableListeners() {
        for (let i = 1; i < this.tableRows.length; i++) {
            let row = this.tableRows[i];
            let orderId = row.getAttribute("order-id");
            let cells = row.getElementsByTagName("td");
            let lastCell = cells[cells.length - 1];
            let options = lastCell.getElementsByTagName("option");
            console.log(options[0]);
            for (let j = 0; j < options.length; j++) {
                options[j].addEventListener("click", this.updateState.bind(this, options[j], orderId), false);
            }
        }

        for (let i = 1; i < this.tableRows.length; i++) {
            let row = this.tableRows[i];
            let orderId = row.getAttribute("order-id");
            let cells = row.getElementsByTagName("td");
            for (let j = 0; j < cells.length-1; j++){
                cells[j].addEventListener("click", this.reloadDetails.bind(this, orderId), false);
            }
        }
    }

    execute(e) {
        e.preventDefault();
        let c = this;
        this.tableContainer.classList.toggle("hidden");
        let request = new XMLHttpRequest();
        request.open("POST", "index/api/filters", true);
        request.onload = function (oEvent) {
            if (request.status === 200) {
                c.reloadTable();
            } else {
                console.log(request.responseText);
                console.log(request.statusText);
            }
        };
        console.log(this.form);
        request.send(new FormData(this.form));
    }

    reloadTable() {
        console.log("wykonuje reloadTable");
        let c = this;
        let tableContainer = this.tableContainer;
        let request = new XMLHttpRequest();
        request.open("POST", "index/api/reloadTable", true);
        let stamp = Date.now();
        request.onload = setTimeout(
            function () {
                if (request.status === 200) {
                    tableContainer.innerHTML = request.responseText;
                    c.addTableListeners();
                } else {
                    console.log("not200response");
                    tableContainer.innerHTML =
                        '<div class="alert alert-danger" role="alert">Wystąpił błąd "' +
                        request.status +
                        " " +
                        request.statusText +
                        '" podczas ładowania tabeli</div>';
                    // NOT FOR DEV tableContainer.innerHTML += '<div class="alert alert-danger" role="alert">'+request.responseText+'</div>';
                }
                tableContainer.classList.toggle("hidden");
            },
            400 - (Date.now() - stamp) > 0 ? 400 - (Date.now() - stamp) : 0
        );
        request.send();
    }

    reloadDetails(id, e) {
        let detailsContent = this.detailsContent;
        let detailsHeaderId = this.detailsHeaderId;
        let c = this;
        this.detailsContent.classList.toggle("hidden");
        let request = new XMLHttpRequest();
        request.open("POST", "index/api/details/" + id, true);
        let stamp = Date.now();
        request.onload = setTimeout(
            function (oEvent) {
                if (request.status === 200) {
                    detailsContent.innerHTML = request.responseText;
                    detailsHeaderId.innerHTML = id;
                    c.currentOrderId = id;
                } else {
                    detailsContent.innerHTML =
                        '<div class="alert alert-danger" role="alert">Wystąpił błąd "' +
                        request.status +
                        " " +
                        request.statusText +
                        '" podczas ładowania szczegółów zlecenia ' +
                        id +
                        "</div>";
                    // NOT FOR DEV detailsContent.innerHTML += '<div class="alert alert-danger" role="alert">'+request.responseText+'</div>';
                }
                detailsContent.classList.toggle("hidden");
            },
            400 - (Date.now() - stamp) > 0 ? 400 - (Date.now() - stamp) : 0
        );
        request.send();
    }

    updateState(option, id, e) {
        console.log(option);
        console.log(id);
        console.log(e);
        let c = this;
        let state = option.value;
        option.removeAttribute("selected");
        let cell = option.parentElement.parentElement;
        let select = option.parentElement;
        select.setAttribute("state", state);
        let tmp = cell.innerHTML;
        //TODO
        // cell.innerHTML =
        //     '<svg class="icon-loading" style="margin-left: 25px;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">' +
        //     '<path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>' +
        //     '<path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>' +
        //     "</svg>";

        let request = new XMLHttpRequest();
        request.open("POST", "index/api/updateState/" + id + "/" + state, true);
        let stamp = Date.now();

        request.onload = setTimeout(
            function (oEvent) {
                if (request.status === 200) {
                } else {
                }
                c.reloadDetails(id);
                // cell.innerHTML = tmp;
                cell.getElementsByClassName("form-select")[0].value = state;
            },
            400 - (Date.now() - stamp) > 0 ? 400 - (Date.now() - stamp) : 0
        );
        request.send();
    }

}

let c = new Controller(
    document
        .getElementsByClassName("mid-col")[0]
        .getElementsByClassName("table-container")[0],
    document
        .getElementById("filter")
);

function executeDeletion(id) {
    let stamp = Date.now();
    let request = new XMLHttpRequest();
    request.open("POST", "/index/api/deleteOrder/" + id, true);
    request.onload = setTimeout(
        function (oEvent) {
            if (request.status == 200) {
                alert("Usunieto zlecenie");
                tableContainer.classList.toggle("hidden");
                reloadDetails(id);
                reloadTable();
            } else {
                alert("Nie udało się usunąc zlecenia.");
            }
        },
        400 - (Date.now() - stamp) > 0 ? 400 - (Date.now() - stamp) : 0
    );
    request.send();
}

function addOrder() {
    openFormAddOrder();
    centerPopupContent.innerHTML =
        '<svg class="icon-loading" style="margin-left: 25px;" xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">' +
        '<path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>' +
        '<path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>' +
        "</svg>";
    let stamp = Date.now();
    let request = new XMLHttpRequest();
    request.open("POST", "/index/api/addOrder", true);
    request.onload = setTimeout(
        function (oEvent) {
            if (request.status == 200) {
                centerPopupContent.innerHTML = request.responseText;
                let addOrderForm = document.forms.namedItem("add_order_form");
                addOrderForm.addEventListener("submit", executeAddition, false);
            } else {
                //TODO
                centerPopupContent.innerHTML = request.responseText;
            }
        },
        400 - (Date.now() - stamp) > 0 ? 400 - (Date.now() - stamp) : 0
    );
    request.send();
}

function executeAddition(e) {
    e.preventDefault();
    let formData = new FormData(document.getElementById("add-order-form"));

    let request = new XMLHttpRequest();
    request.open("POST", "index/api/addOrder", true);
    request.onload = function (oEvent) {
        if (request.status == 201) {
            centerPopupContent.innerHTML = request.responseText;
        } else if (request.status == 200) {
            centerPopupContent.innerHTML = request.responseText;
            let addOrderForm = document.forms.namedItem("add_order_form");
            addOrderForm.addEventListener("submit", executeAddition, false);
        } else {
            centerPopupContent.innerHTML = request.responseText;
        }
    };
    request.send(formData);
}
