function openPopup() {
  document.getElementById("overlay").style.display = "block";

  var centerPopup = document.getElementById("center-popup");

  // centerPopup.style.display = "block";
  // centerPopup.style.opacity = "0";

  if (!centerPopup.classList.contains("active")) {
    centerPopup.classList.add("active");
  }
}

function closePopup() {
  var centerPopup = document.getElementById("center-popup");
  if (centerPopup.classList.contains("active")) {
    centerPopup.classList.remove("active");
  }
}

function openFormAddOrder() {
  openForm();
  // document.getElementById("right-form").innerHTML =
  //   '<i style="font-size: 30px; color: red;">Dodaj zlecenie</i>';
}

function openForm() {
  document.getElementById("overlay").style.display = "block";
  var rightForm = document.getElementById("right-form");
  if (!rightForm.classList.contains("active")) {
    rightForm.classList.add("active");
  }
}

function closeForm() {
  var rightForm = document.getElementById("right-form");
  if (rightForm.classList.contains("active")) {
    rightForm.classList.remove("active");
  }
}

function closeAll() {
  document.getElementById("overlay").style.display = "none";
  closeForm();
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
