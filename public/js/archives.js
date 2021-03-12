var centerPopupContent = document.getElementById("center-popup-content");
var form = document.forms.namedItem("archives_filters_form");
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
    url: "archives/api/filters",
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
  request.open("POST", "archives/api/reloadTable", true);
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
  request.open("POST", "archives/api/details/" + id, true);
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



