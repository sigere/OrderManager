// class ClientsController {
//     tableContainer;
//     selectedRow;
//     detailsHeaderId;
//     detailsContent;
//     currentId;
//     overlay;
//     centerPopup;
//     centerPopupContent;
//     addButton;
//     editButton;
//
//     constructor() {
//         this.tableContainer = document
//             .getElementsByClassName("mid-col")[0]
//             .getElementsByClassName("table-container")[0];
//         this.selectedRow = null;
//         this.detailsHeaderId = document.getElementById("details-header-id");
//         this.detailsContent = document.getElementById("details-content");
//         this.currentId = null;
//         this.overlay = document.getElementById("overlay");
//         this.centerPopup = document.getElementById("center-popup");
//         this.centerPopupContent = document.getElementById("center-popup-content");
//         this.addButton = document.getElementById("add-button");
//         this.editButton = document.getElementById("edit-button");
//
//         this.addButton.addEventListener("click", this.addClient.bind(this), false);
//         this.editButton.addEventListener("click", this.updateClient.bind(this), false);
//         this.addTableListeners();
//     }
//
//     addTableListeners() {
//         let tableRows = this.tableContainer.getElementsByTagName("tr");
//
//         for (let i = 1; i < tableRows.length; i++) {
//             let row = tableRows[i];
//             let clientId = row.getAttribute("client-id");
//             row.addEventListener("click", this.reloadDetails.bind(this, clientId), false);
//         }
//         $(function () {
//             $("#main-table").tablesorter();
//         });
//     }
//
//     updateSelected() {
//         let tableRows = this.tableContainer.getElementsByTagName("tr");
//         if (!this.currentId)
//             return;
//         if (this.selectedRow)
//             this.selectedRow.classList.toggle("active-row");
//         for (let i = 1; i < tableRows.length; i++) {
//             let row = tableRows[i];
//             let clientId = row.getAttribute("client-id");
//             if (clientId === this.currentId) {
//                 this.selectedRow = row;
//                 break;
//             }
//         }
//         this.selectedRow.classList.toggle("active-row");
//     }
//
//     reloadTable() {
//         let c = this;
//         let tableContainer = this.tableContainer;
//         let request = new XMLHttpRequest();
//         tableContainer.classList.toggle("hidden");
//         request.open("POST", "/clients/api/reloadTable", true);
//         let stamp = Date.now();
//         request.onload = setTimeout(
//             function () {
//                 if (request.status === 200) {
//                     tableContainer.innerHTML = request.responseText;
//                     c.addTableListeners();
//                     c.updateSelected();
//                 } else {
//                     tableContainer.innerHTML =
//                         '<div class="alert alert-danger" role="alert">Wystąpił błąd "' +
//                         request.status +
//                         " " +
//                         request.statusText +
//                         '" podczas ładowania tabeli</div>';
//                 }
//                 tableContainer.classList.toggle("hidden");
//             },
//             400 - (Date.now() - stamp) > 0 ? 400 - (Date.now() - stamp) : 0
//         );
//         request.send();
//     }
//
//     reloadDetails(id, e) {
//         if (!id) {
//             this.detailsHeaderId.innerHTML = "";
//             this.detailsContent.innerHTML = '<div class="alert alert-primary">Wybierz klienta, aby wyświetlić jego szczegóły</div>';
//             return;
//         }
//
//         let detailsContent = this.detailsContent;
//         let detailsHeaderId = this.detailsHeaderId;
//         let c = this;
//         this.detailsContent.classList.toggle("hidden");
//         let request = new XMLHttpRequest();
//         request.open("POST", "/clients/api/details/" + id, true);
//         let stamp = Date.now();
//         request.onload = setTimeout(
//             function (oEvent) {
//                 if (request.status === 200) {
//                     detailsContent.innerHTML = request.responseText;
//                     detailsHeaderId.innerHTML = id;
//                     c.currentId = id;
//                     c.updateSelected();
//                 } else {
//                     detailsContent.innerHTML =
//                         '<div class="alert alert-danger" role="alert">Wystąpił błąd "' +
//                         request.status +
//                         " " +
//                         request.statusText +
//                         '" podczas ładowania szczegółów klienta ' +
//                         id +
//                         "</div>";
//                 }
//                 detailsContent.classList.toggle("hidden");
//             },
//             400 - (Date.now() - stamp) > 0 ? 400 - (Date.now() - stamp) : 0
//         );
//         request.send();
//     }
//
//
//     addClient() {
//         this.overlay.style.display = "block";
//
//         if (!this.centerPopup.classList.contains("active")) {
//             this.centerPopup.classList.add("active");
//         }
//
//         let c = this;
//         let popup = this.centerPopupContent;
//         let stamp = Date.now();
//         let request = new XMLHttpRequest();
//         request.open("POST", "/clients/api/addClient", true);
//         request.onload = function (oEvent) {
//
//             let responseText = request.responseText;
//             let status = 0;
//             let newId;
//
//             function refresh() {
//                 if (status === 201) {
//                     popup.innerHTML = '<div class="alert alert-success" role="alert">Dodano klienta.</div>';
//                     if (newId)
//                         c.reloadDetails(newId);
//                     c.reloadTable();
//                     return;
//                 }
//
//                 popup.innerHTML = responseText;
//                 let addClientForm = document.forms.namedItem("add_client_form");
//                 addClientForm.addEventListener("submit", function (e) {
//                     e.preventDefault();
//                     let formData = new FormData(addClientForm);
//                     let request = new XMLHttpRequest();
//                     request.open("POST", "/clients/api/addClient", true);
//                     request.onload = function (oEvent) {
//                         responseText = request.responseText;
//                         status = request.status;
//                         newId = request.getResponseHeader("ClientId");
//                         refresh();
//                     };
//                     request.send(formData);
//                 }, false);
//             }
//
//             refresh();
//         };
//         request.send();
//     }
//
//     updateClient() {
//         if (!this.currentId) {
//             alert("Nie wybrano żadnego klienta");
//             return;
//         }
//         this.overlay.style.display = "block";
//
//         if (!this.centerPopup.classList.contains("active")) {
//             this.centerPopup.classList.add("active");
//         }
//
//         let c = this;
//         let popup = this.centerPopupContent;
//         let stamp = Date.now();
//         let request = new XMLHttpRequest();
//         request.open("POST", "/clients/api/updateClient/" + this.currentId, true);
//         request.onload = setTimeout(
//             function (oEvent) {
//
//                 let responseText = request.responseText;
//                 let status = 0;
//
//                 function refresh() {
//                     if (status === 202) {
//                         popup.innerHTML = '<div class="alert alert-success" role="alert">Zaktualizowano klienta.</div>';
//                         c.reloadDetails(c.currentId);
//                         c.reloadTable();
//                         return;
//                     }
//
//                     popup.innerHTML = responseText;
//                     let addClientForm = document.forms.namedItem("add_client_form");
//                     addClientForm.addEventListener("submit", function (e) {
//                         e.preventDefault();
//                         let formData = new FormData(addClientForm);
//                         let request = new XMLHttpRequest();
//                         request.open("POST", "/clients/api/updateClient/" + c.currentId, true);
//                         request.onload = function (oEvent) {
//                             responseText = request.responseText;
//                             status = request.status;
//                             refresh();
//                         };
//                         request.send(formData);
//                     }, false);
//                 }
//
//                 refresh();
//
//             },
//             400 - (Date.now() - stamp) > 0 ? 400 - (Date.now() - stamp) : 0
//         );
//         request.send();
//     }
// }
//
// let c = new ClientsController();
//
