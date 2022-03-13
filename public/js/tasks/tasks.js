// class TasksController {
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
//         this.addButton.addEventListener("click", this.addTask.bind(this), false);
//         this.editButton.addEventListener("click", this.updateTask.bind(this), false);
//         this.addTableListeners();
//     }
//
//     addTableListeners() {
//         let tableRows = this.tableContainer.getElementsByTagName("tr");
//
//         for (let i = 1; i < tableRows.length; i++) {
//             let row = tableRows[i];
//             let taskId = row.getAttribute("task-id");
//             let cells = row.getElementsByTagName("td");
//             for (let j = 0; j < cells.length - 1; j++) {
//                 cells[j].addEventListener("click", this.reloadDetails.bind(this, taskId), false);
//             }
//             let button = cells[cells.length - 1].getElementsByTagName("button");
//             if(button.length){
//                 button = button[0];
//                 button.addEventListener("click", this.setDone.bind(this, taskId), false);
//             }
//         }
//         $(function () {
//             $("#main-table").tablesorter();
//         });
//     }
//
//     setDone(id, e) {
//         let c = this;
//
//         let request = new XMLHttpRequest();
//         request.open("POST", "tasks/api/setDone/" + id, true);
//         let stamp = Date.now();
//         request.onload =
//             function (oEvent) {
//                 setTimeout(function () {
//                     c.reloadDetails(id);
//                     c.reloadTable();
//                 }, 400 - (Date.now() - stamp) > 0 ? 400 - (Date.now() - stamp) : 0);
//             };
//         request.send();
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
//             let taskId = row.getAttribute("task-id");
//             if (taskId === this.currentId) {
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
//         request.open("POST", "/tasks/api/reloadTable", true);
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
//             this.detailsContent.innerHTML = '<div class="alert alert-primary">Wybierz zadanie, aby wyświetlić jego szczegóły</div>';
//             return;
//         }
//
//         let detailsContent = this.detailsContent;
//         let detailsHeaderId = this.detailsHeaderId;
//         let c = this;
//         this.detailsContent.classList.toggle("hidden");
//         let request = new XMLHttpRequest();
//         request.open("POST", "/tasks/api/details/" + id, true);
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
//                         '" podczas ładowania szczegółów zadania ' +
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
//     addTask() {
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
//         request.open("POST", "/tasks/api/addTask", true);
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
//                 let addTaskForm = document.forms.namedItem("add_task_form");
//                 addTaskForm.addEventListener("submit", function (e) {
//                     e.preventDefault();
//                     let formData = new FormData(addTaskForm);
//                     let request = new XMLHttpRequest();
//                     request.open("POST", "/tasks/api/addTask", true);
//                     request.onload = function (oEvent) {
//                         responseText = request.responseText;
//                         status = request.status;
//                         newId = request.getResponseHeader("TaskId");
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
//     updateTask() {
//         if (!this.currentId) {
//             alert("Nie wybrano żadnego zadania");
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
//         request.open("POST", "/tasks/api/updateTask/" + this.currentId, true);
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
//                     let addTaskForm = document.forms.namedItem("add_task_form");
//                     addTaskForm.addEventListener("submit", function (e) {
//                         e.preventDefault();
//                         let formData = new FormData(addTaskForm);
//                         let request = new XMLHttpRequest();
//                         request.open("POST", "/tasks/api/updateTask/" + c.currentId, true);
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
// let c = new TasksController();
//
