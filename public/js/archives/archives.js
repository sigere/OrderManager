class ArchivesController {
  tableContainer;
  selectedRow;
  form;
  detailsHeaderId;
  detailsContent;
  currentId;
  restoreButton;
  overlay;
  centerPopup;
  centerPopupContent;

  constructor() {
    this.tableContainer = document
        .getElementsByClassName("mid-col")[0]
        .getElementsByClassName("table-container")[0];
    this.currentId = null;
    this.selectedRow = null;
    this.overlay = document.getElementById("overlay");
    this.centerPopup = document.getElementById("center-popup");
    this.centerPopupContent = document.getElementById("center-popup-content");
    this.form = document.forms.namedItem("archives_filters_form");
    this.detailsHeaderId = document.getElementById("details-header-id");
    this.detailsContent = document.getElementById("details-content");
    this.restoreButton = document.getElementById("restore-button");

    this.form.addEventListener("submit", e => this.executeFilters(e), false);
    this.restoreButton.addEventListener("click", this.restoreOrder.bind(this), false);
    this.addTableListeners();
  }

  addTableListeners() {
    let tableRows = this.tableContainer.getElementsByTagName("tr");
    for (let i = 1; i < tableRows.length; i++) {
      let row = tableRows[i];
      let orderId = row.getAttribute("order-id");
      let cells = row.getElementsByTagName("td");
      for (let j = 0; j < cells.length - 1; j++) {
        cells[j].addEventListener("click", this.reloadDetails.bind(this, orderId), false);
      }
    }
    console.log(tableRows.length);
    if (tableRows.length - 1 >= 15) {
      this.overlay.style.display = "block";
      this.centerPopup.classList.add("active");
      this.centerPopupContent.innerHTML = '' +
          '<div class="alert alert-danger" style="text-align: center">' +
          'Liczba zwróconych przez serwer rekordów przekracza 15.<br/>' +
          'Część danych nie może zostać wyświetlona.<br/>' +
          'Użyj filtrów, by ograniczyć wynik' +
          '</div>';
    }
  }

  updateSelected() {
    let tableRows = this.tableContainer.getElementsByTagName("tr");
    if(!this.currentId)
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
    this.selectedRow.classList.toggle("active-row");
  }

  executeFilters(e) {
    e.preventDefault();
    let c = this;
    let request = new XMLHttpRequest();
    request.open("POST", "archives/api/filters", true);
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
    request.open("POST", "archives/api/reloadTable", true);
    let stamp = Date.now();
    request.onload = setTimeout(
        function () {
          if (request.status === 200) {
            tableContainer.innerHTML = request.responseText;
            c.addTableListeners();
            c.updateSelected();
          } else {
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
    request.open("POST", "archives/api/details/" + id, true);
    let stamp = Date.now();
    request.onload = setTimeout(
        function (oEvent) {
          if (request.status === 200) {
            detailsContent.innerHTML = request.responseText;
            detailsHeaderId.innerHTML = id;
            c.currentId = id;
            c.updateSelected();
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

  restoreOrder() {
    if (!this.currentId) {
      alert("Nie wybrano żadnego zlecenia");
      return;
    }

    this.overlay.style.display = "block";


    if (!this.centerPopup.classList.contains("active")) {
      this.centerPopup.classList.add("active");
    }

    this.centerPopupContent.innerHTML =
        'Czy na pewno przywrócić zlecenie? <button id="confirmRestoreButton" class="btn btn-danger">Przywróć</button>';
    let button = document.getElementById("confirmRestoreButton");

    let c = this;
    let id = this.currentId;
    let popup = this.centerPopupContent;
    button.addEventListener("click", function () {
      popup.innerHTML =
          '<svg style="margin-left: 25px;" xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-arrow-clockwise icon-loading" viewBox="0 0 16 16">' +
          '<path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>' +
          '<path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>' +
          "</svg>";

      let stamp = Date.now();
      let request = new XMLHttpRequest();
      request.open("POST", "/archives/api/restoreOrder/" + id, true);
      request.onload = setTimeout(
          function (oEvent) {
            if (request.status === 200) {
              popup.innerHTML = '<div class="alert alert-success" role="alert">Przywrócono zlecenie</div>';
              c.reloadDetails(null);
              c.reloadTable();
            }
            else {
              popup.innerHTML = request.responseText;
            }
          },
          400 - (Date.now() - stamp) > 0 ? 400 - (Date.now() - stamp) : 0
      );
      request.send();
    }, false);
  }

}

let c = new ArchivesController();



