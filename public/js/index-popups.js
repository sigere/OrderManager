var centerPopup = document.getElementById("center-popup");

function openPopup(callback) {
    document.getElementById("overlay").style.display = "block";

    // centerPopup.style.display = "block";
    // centerPopup.style.opacity = "0";

    if (!centerPopup.classList.contains("active")) {
        centerPopup.classList.add("active");
    }

    centerPopupContent.innerHTML =
        'Czy na pewno usunąć zlecenie? <button class="btn btn-danger" onclick="' +
        callback +
        ' closeAll();">Usuń</button>';
}

function closePopup() {
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
    document.getElementById("center-popup-content").innerHTML = '<svg style="margin-left: 25px;" xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-arrow-clockwise icon-loading" viewBox="0 0 16 16">' +
        '<path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>' +
        '<path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>' +
        "</svg>";
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
