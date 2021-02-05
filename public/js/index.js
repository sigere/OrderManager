var form = document.forms.namedItem("index_filters_form");
var debug = document.getElementById("debug");
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
      // debug.innerHTML = data;
      reloadTable();
    },
  });
}

function reloadTable() {
  console.log("wykonuje reloadTable");

  var request = new XMLHttpRequest();
  request.open("POST", "index/api/reloadTable", true);

  request.onload = function (oEvent) {
    if (request.status == 200) {
      tableContainer.innerHTML = request.responseText;
      tableContainer.classList.toggle("hidden");
    } else {
      console.log("not200response");
      // todo
      //request.status
    }
  };
  request.send();
}
