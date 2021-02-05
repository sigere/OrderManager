var form = document.forms.namedItem("index_filters_form");
var debug = document.getElementById("debug");
form.addEventListener("submit", executeFilters, false);

function executeFilters(e) {
  e.preventDefault();

  // $.post( "index/api/filters", function( data ) {
  //   $( ".result" ).html( data );
  // });

  $.ajax({
    url: "index/api/filters",
    method: "POST",
    data: $('#filter').serialize(),
    success: function(data){
      debug.innerHTML = data;
      // alert(data);
    }
  });

  // var data = new FormData(form);
  // var queryString = new URLSearchParams(data).toString();
  // console.log(queryString);

  // var request = new XMLHttpRequest();
  // request.open("POST", "index/api/filters", true);

  // request.onload = function (oEvent) {
  //   if (request.status == 200) {
  //       document.getElementById("debug").innerHTML = request.responseText;
  //       // reloadTable(request.responseText);
  //   }
  //   else {
  //       console.log("not200response");
  //       // todo
  //   //   oOutput.innerHTML =
  //   //     "Error " +
  //   //     request.status +
  //   //     " occurred when trying to upload your file.<br />";
  //   }
  // };
  // request.send();
}

function reloadTable(){
  var tableContainer = document.getElementsByClassName('mid-col')[0].getElementsByClassName('table-container')[0];
  console.log(tableContainer);
}