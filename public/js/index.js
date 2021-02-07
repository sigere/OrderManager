var centerPopup = document.getElementById("center-popup");
var form = document.forms.namedItem("index_filters_form");
var debug = document.getElementById("debug");
var deleteButton = document.getElementById("delete-button");
var currentOrderId = null;
var tableContainer = document
  .getElementsByClassName("mid-col")[0]
  .getElementsByClassName("table-container")[0];
form.addEventListener("submit", executeFilters, false);

function executeFilters(e) {
  e.preventDefault();

  tableContainer.classList.toggle("hidden");
  $.ajax({
    url: "index/api/filters",
    method: "POST",
    data: $("#filter").serialize(),
    success: function (data) {
      reloadTable();
    },
  });
}

function reloadTable() {
  console.log("wykonuje reloadTable");

  var request = new XMLHttpRequest();
  request.open("POST", "index/api/reloadTable", true);
  var stamp = Date.now();
  request.onload = setTimeout(
    function (oEvent) {
      if (request.status == 200) {
        tableContainer.innerHTML = request.responseText;
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

function reloadDetails(id) {
  var detailsHeaderId = document.getElementById("details-header-id");
  var detailsContent = document.getElementById("details-content");

  detailsContent.classList.toggle("hidden");

  var request = new XMLHttpRequest();
  request.open("POST", "index/api/details/" + id, true);
  var stamp = Date.now();
  request.onload = setTimeout(
    function (oEvent) {
      if (request.status == 200) {
        detailsContent.innerHTML = request.responseText;
        detailsHeaderId.innerHTML = id;
        currentOrderId = id;
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

function updateState(option, id, state) {
  option.parentElement.parentElement.innerHTML =
    '<svg class="icon-loading" style="margin-left: 25px;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">' +
    '<path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>' +
    '<path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>' +
    "</svg>";

  var request = new XMLHttpRequest();
  request.open("POST", "index/api/updateState/" + id + "/" + state, true);
  var stamp = Date.now();

  request.onload = setTimeout(
    function (oEvent) {
      if (request.status == 200) {
      } else {
      }
      tableContainer.classList.toggle("hidden");
      reloadDetails(id);
    },
    400 - (Date.now() - stamp) > 0 ? 400 - (Date.now() - stamp) : 0
  );
  request.send();
}

function deleteOrder() {
  if (!currentOrderId) {
    alert("Nie wybrano żadnego zlecenia");
    return;
  }
  openPopup("executeDeletion(" + currentOrderId + ");");
}

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
  centerPopup.innerHTML =
    '<svg class="icon-loading" style="margin-left: 25px;" xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">' +
    '<path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>' +
    '<path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>' +
    "</svg>";
  let stamp = Date.now();
  let request = new XMLHttpRequest();
  request.open("POST", "/index/api/addOrder", true);
  request.onload = setTimeout(
    function (oEvent) {
      centerPopup.innerHTML = request.responseText;
      let addOrderForm = document.forms.namedItem("add_order_form");
      console.log("ustawiam listenera");
      addOrderForm.addEventListener("submit", executeAddition, false);
    },
    400 - (Date.now() - stamp) > 0 ? 400 - (Date.now() - stamp) : 0
  );
  request.send();
}

function executeAddition(e){
  e.preventDefault();
//TODO
  $.ajax({
    url: "index/api/addOrder",
    method: "POST",
    data: $("#add-order-form").serialize(),
    success: function (data) {
      centerPopup.innerHTML = data;
      console.log("sucess");
    },
  });
}
