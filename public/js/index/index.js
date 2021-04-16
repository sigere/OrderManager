class Controller {
    tableContainer;
    selectedRow;
    form;
    detailsHeaderId;
    detailsContent;
    currentId;
    overlay;
    centerPopup;
    centerPopupContent;
    deleteButton;
    addButton;
    rep;
    editButton;

    constructor(tableContainer, form) {
        this.tableContainer = tableContainer;
        this.selectedRow = null;
        this.form = form;
        this.detailsHeaderId = document.getElementById("details-header-id");
        this.detailsContent = document.getElementById("details-content");
        this.currentId = null;
        this.overlay = document.getElementById("overlay");
        this.centerPopup = document.getElementById("center-popup");
        this.centerPopupContent = document.getElementById("center-popup-content");
        this.deleteButton = document.getElementById("delete-button");
        this.addButton = document.getElementById("add-button");
        this.editButton = document.getElementById("edit-button");
        this.rep = document.getElementById("rep");

        this.form.addEventListener("submit", e => this.executeFilters(e), false);
        this.deleteButton.addEventListener("click", this.deleteOrder.bind(this), false);
        this.addButton.addEventListener("click", this.addOrder.bind(this), false);
        this.editButton.addEventListener("click", this.updateOrder.bind(this), false);
        this.rep.addEventListener("click", this.setRep.bind(this), false);
        this.addTableListeners();
    }

    addTableListeners() {
        let tableRows = this.tableContainer.getElementsByTagName("tr");
        for (let i = 1; i < tableRows.length; i++) {
            let row = tableRows[i];
            let orderId = row.getAttribute("order-id");
            let cells = row.getElementsByTagName("td");
            let lastCell = cells[cells.length - 1];
            let select = lastCell.getElementsByTagName("select")[0];
            select.addEventListener("change", this.updateState.bind(this, select, orderId), false)

        }

        for (let i = 1; i < tableRows.length; i++) {
            let row = tableRows[i];
            let orderId = row.getAttribute("order-id");
            let cells = row.getElementsByTagName("td");
            for (let j = 0; j < cells.length - 1; j++) {
                cells[j].addEventListener("click", this.reloadDetails.bind(this, orderId), false);
            }
        }
        if (tableRows.length - 1 >= 100) {
            this.overlay.style.display = "block";
            this.centerPopup.classList.add("active");
            this.centerPopupContent.innerHTML = '' +
                '<div class="alert alert-warning" style="text-align: center">' +
                'Liczba zwróconych przez serwer rekordów przekracza 100.<br/>' +
                'Część danych nie może zostać wyświetlona.<br/>' +
                'Użyj filtrów, by ograniczyć wynik' +
                '</div>';
        }
        $(function () {
            $("#main-table").tablesorter({
                dateFormat: "ddmmyyyy",
            });
        });
    }

    updateSelected() {
        let tableRows = this.tableContainer.getElementsByTagName("tr");
        if (!this.currentId)
            return;
        if (this.selectedRow)
            this.selectedRow.classList.toggle("active-row");
        for (let i = 1; i < tableRows.length; i++) {
            let row = tableRows[i];
            let orderId = row.getAttribute("order-id");
            if (orderId === this.currentId) {
                this.selectedRow = row;
                break;
            }
        }
        if (this.selectedRow)
            this.selectedRow.classList.toggle("active-row");
    }

    executeFilters(e) {
        e.preventDefault();
        let c = this;
        let request = new XMLHttpRequest();
        request.open("POST", "index/api/filters", true);
        request.onload = function () {
            if (request.status === 200) {
                c.reloadTable();
            } else {
                alert(request.responseText);
                alert(request.statusText);
            }
        };
        request.send(new FormData(this.form));
    }

    reloadTable() {
        let c = this;
        let tableContainer = this.tableContainer;
        let request = new XMLHttpRequest();
        tableContainer.classList.toggle("hidden");
        request.open("POST", "index/api/reloadTable", true);
        let stamp = Date.now();
        request.onload =
            function () {
                if (request.status === 200) {
                    setTimeout(function () {
                        tableContainer.innerHTML = request.responseText;
                        c.addTableListeners();
                        c.updateSelected();
                    }, 400 - (Date.now() - stamp) > 0 ? 400 - (Date.now() - stamp) : 0);
                } else {
                    tableContainer.innerHTML =
                        '<div class="alert alert-danger" role="alert">Wystąpił błąd "' +
                        request.status +
                        " " +
                        request.statusText +
                        '" podczas ładowania tabeli</div>';
                }
                tableContainer.classList.toggle("hidden");
            };
        request.send();
    }

    reloadDetails(id, e) {
        if (!id) {
            this.detailsHeaderId.innerHTML = "";
            this.detailsContent.innerHTML = '<div class="alert alert-primary">Wybierz zlecenie, aby wyświetlić jego szczegóły</div>';
            return;
        }

        let detailsContent = this.detailsContent;
        let detailsHeaderId = this.detailsHeaderId;
        let c = this;
        this.detailsContent.classList.toggle("hidden");
        let request = new XMLHttpRequest();
        request.open("POST", "index/api/details/" + id, true);
        let stamp = Date.now();
        request.onload =
            function (oEvent) {
                if (request.status === 200) {
                    setTimeout(function () {
                        detailsContent.innerHTML = request.responseText;
                        detailsHeaderId.innerHTML = id;
                        c.currentId = id;
                        c.updateSelected();
                        detailsContent.classList.toggle("hidden");
                        console.log(400 - (Date.now() - stamp));
                    }, 400 - (Date.now() - stamp) > 0 ? 400 - (Date.now() - stamp) : 0);
                } else {
                    console.log(request.status);
                    console.log(request);
                    detailsContent.innerHTML =
                        '<div class="alert alert-danger" role="alert">Wystąpił błąd "' +
                        request.status +
                        " " +
                        request.statusText +
                        '" podczas ładowania szczegółów zlecenia ' +
                        id +
                        "</div>";
                    detailsContent.classList.toggle("hidden");
                }

            };
        request.send();
    }

    updateState(select, id, e) {
        let c = this;
        let state = select.value;
        // let cell = option.parentElement.parentElement;
        // let select = option.parentElement;
        // select.setAttribute("state", state);
        // let tmp = cell.innerHTML;
        //TODO
        // cell.innerHTML =
        //     '<svg class="icon-loading" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">' +
        //     '<path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>' +
        //     '<path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>' +
        //     "</svg>";

        let request = new XMLHttpRequest();
        request.open("POST", "index/api/updateState/" + id + "/" + state, true);
        let stamp = Date.now();

        request.onload =
            function (oEvent) {
                setTimeout(function () {
                    c.reloadDetails(id);
                    c.reloadTable();
                }, 400 - (Date.now() - stamp) > 0 ? 400 - (Date.now() - stamp) : 0);
            };
        request.send();
    }

    deleteOrder() {
        if (!this.currentId) {
            alert("Nie wybrano żadnego zlecenia");
            return;
        }
        this.overlay.style.display = "block";


        if (!this.centerPopup.classList.contains("active")) {
            this.centerPopup.classList.add("active");
        }

        this.centerPopupContent.innerHTML =
            'Czy na pewno usunąć zlecenie? <button id="confirmDeletionButton" class="btn btn-danger">Usuń</button>';
        let button = document.getElementById("confirmDeletionButton");

        let c = this;
        let id = this.currentId;
        let popup = this.centerPopupContent;
        button.addEventListener("click", function () {
            popup.innerHTML =
                '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-arrow-clockwise icon-loading" viewBox="0 0 16 16">' +
                '<path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>' +
                '<path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>' +
                "</svg>";

            let stamp = Date.now();
            let request = new XMLHttpRequest();
            request.open("POST", "/index/api/deleteOrder/" + id, true);
            request.onload =
                function (oEvent) {
                    if (request.status === 200) {
                        setTimeout(function () {
                            popup.innerHTML = '<div class="alert alert-success" role="alert">Usunięto zlecenie</div>';
                            c.reloadDetails(null);
                            c.reloadTable();
                        });
                    } else {
                        popup.innerHTML = '<div class="alert alert-danger" role="alert">Nie udało się usunąć zlecenia</div>';
                    }
                };
            request.send();
        }, false);
    }

    addOrder() {
        this.overlay.style.display = "block";

        if (!this.centerPopup.classList.contains("active")) {
            this.centerPopup.classList.add("active");
        }

        let c = this;
        let popup = this.centerPopupContent;
        let stamp = Date.now();
        let request = new XMLHttpRequest();
        let responseText;
        let status;
        let newId;
        let addOrderForm;
        let formData;
        let request2;
        request.open("POST", "/index/api/addOrder", true);
        request.onload = function (oEvent) {
            responseText = request.responseText;

            setTimeout(function () {
                document.getElementById("add_order_form_deadline_date").value = new Date().toISOString().slice(0, 10);
                document.getElementById("add_order_form_adoption").value = new Date().toISOString().slice(0, 10);
                document.getElementById("add_order_form_deadline_time").value = "23:59";
            }, 10);
            status = 0;

            function refresh() {
                if (status === 201) {
                    popup.innerHTML = '<div class="alert alert-success" role="alert">Dodano zlecenie.</div>';
                    if (newId) c.reloadDetails(newId);
                    c.reloadTable();
                    return;
                }

                popup.innerHTML = responseText;
                addOrderForm = document.forms.namedItem("add_order_form");
                addOrderForm.addEventListener("submit", function (e) {
                    e.preventDefault();
                    formData = new FormData(addOrderForm);
                    request2 = new XMLHttpRequest();
                    request2.open("POST", "/index/api/addOrder", true);
                    request2.onload = function (oEvent) {
                        responseText = request2.responseText;
                        document.getElementById("add_order_form_deadline_date").value = new Date().toISOString().slice(0, 10);
                        status = request2.status;
                        newId = request2.getResponseHeader("orderId");
                        refresh();
                    };
                    request2.send(formData);
                }, false);
            }

            refresh();

        };
        request.send();
    }

    updateOrder() {
        if (!this.currentId) {
            alert("Nie wybrano żadnego zlecenia");
            return;
        }
        this.overlay.style.display = "block";

        if (!this.centerPopup.classList.contains("active")) {
            this.centerPopup.classList.add("active");
        }

        let c = this;
        let popup = this.centerPopupContent;
        let stamp = Date.now();
        let request = new XMLHttpRequest();
        request.open("POST", "/index/api/updateOrder/" + this.currentId, true);
        request.onload =
            function (oEvent) {
                let responseText = request.responseText;
                let status = 0;

                function refresh() {
                    if (status === 202) {
                        setTimeout(function () {
                            popup.innerHTML = '<div class="alert alert-success" role="alert">Zaktualizowano zlecenie.</div>';
                            c.reloadDetails(c.currentId);
                            c.reloadTable();
                        }, 400 - (Date.now() - stamp) > 0 ? 400 - (Date.now() - stamp) : 0);

                        return;
                    }
                    popup.innerHTML = responseText;
                    let addOrderForm = document.forms.namedItem("add_order_form");
                    addOrderForm.addEventListener("submit", function (e) {
                        e.preventDefault();
                        let formData = new FormData(addOrderForm);
                        let request = new XMLHttpRequest();
                        request.open("POST", "/index/api/updateOrder/" + c.currentId, true);
                        request.onload = function (oEvent) {
                            setTimeout(function () {
                                responseText = request.responseText;
                                status = request.status;
                                refresh();
                            }, 400 - (Date.now() - stamp) > 0 ? 400 - (Date.now() - stamp) : 0);
                        };
                        request.send(formData);
                    }, false);
                }

                refresh();

            };
        request.send();
    }

    setRep() {
        let popup = this.centerPopupContent;
        let rep = this.rep;
        this.overlay.style.display = "block";
        if (!this.centerPopup.classList.contains("active"))
            this.centerPopup.classList.add("active");
        console.log(rep);
        popup.innerHTML =
            '<h5>Wprowadź nowy numer:</h5>' +
            '<input id="rep-text" type="text" value="' + rep.getAttribute("value") + '"/>' +
            '<button class="btn btn-primary" id="rep-button">OK</button>';

        let button = document.getElementById("rep-button");
        let repText = document.getElementById("rep-text");
        button.addEventListener("click", function () {
            popup.innerHTML =
                '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-arrow-clockwise icon-loading" viewBox="0 0 16 16">' +
                '<path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>' +
                '<path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>' +
                "</svg>";
            let stamp = Date.now();
            let request = new XMLHttpRequest();
            request.open("POST", "/index/api/setRep/" + repText.value, true);
            request.onload =
                function (oEvent) {
                    popup.innerHTML = request.responseText;
                    if (request.status === 200)
                        setTimeout(function () {
                            rep.innerHTML = repText.value;
                        }, 400 - (Date.now() - stamp) > 0 ? 400 - (Date.now() - stamp) : 0);
                };
            request.send();
        }, false)
    }
}

let c = new Controller(
    document
        .getElementsByClassName("mid-col")[0]
        .getElementsByClassName("table-container")[0],
    document
        .getElementById("filter")
);

