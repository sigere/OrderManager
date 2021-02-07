var centerPopup = document.getElementById("center-popup");
function openPopup(callback) {
  document.getElementById("overlay").style.display = "block";

  // centerPopup.style.display = "block";
  // centerPopup.style.opacity = "0";

  if (!centerPopup.classList.contains("active")) {
    centerPopup.classList.add("active");
  }

  centerPopup.innerHTML =
    'Czy na pewno usunąć zlecenie? <button class="btn btn-danger" onclick="' +
    callback +
    ' closeAll();">Usuń</button>';
}

function closePopup() {
  var centerPopup = document.getElementById("center-popup");
  if (centerPopup.classList.contains("active")) {
    centerPopup.classList.remove("active");
  }
}

function openFormAddOrder() {
  document.getElementById("overlay").style.display = "block";
  if (!centerPopup.classList.contains("active")) {
    centerPopup.classList.add("active");
  }
}

function closeAll() {
  document.getElementById("overlay").style.display = "none";
  closePopup();
}

// function togglePopup(popup) {
//   document.getElementById("overlay").classList.toggle("active");
//   document.getElementById(popup).classList.toggle("active");
// }

// function toggleInfo() {
//   $("#popup-ok").fadeIn(1);
//   // document.getElementById('popup-ok').classList.toggle("active");
//   setTimeout(function () {
//     $("#popup-ok").fadeOut(1200);
//   }, 2000);
// }
