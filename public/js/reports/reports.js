// class ReportsController {
//     reportsTable;
//     form;
//     details;
//     preview;
//     buttonPreview;
//     buttonExport;
//     currentReportId;
//
//     constructor() {
//         this.reportsTable = document.getElementById('reports-table');
//         this.form= document.getElementById('form-preview-content-form');
//         this.details= document.getElementById('details-content');
//         this.preview = document.getElementById('form-preview-content-preview');
//         this.addTableListeners();
//     }
//
//     addTableListeners() {
//         let tableRows = this.reportsTable.getElementsByTagName("tr");
//         for (let i = 1; i < tableRows.length; i++) {
//             let row = tableRows[i];
//             let reportId = row.getAttribute("report-id");
//             let cells = row.getElementsByTagName("td");
//             for (let j = 0; j < cells.length; j++) {
//                 cells[j].addEventListener("click", this.loadReport.bind(this, reportId), false);
//             }
//         }
//         $(function () {
//             $("#main-table").tablesorter({
//                 dateFormat: "ddmmyyyy"
//             });
//         });
//     }
//
//     addFormListeners() {
//         this.buttonPreview = document.getElementById('button-preview');
//         this.buttonExport = document.getElementById('button-export');
//
//         this.buttonPreview.addEventListener("click", this.loadPreview.bind(this), false);
//         this.buttonExport.addEventListener("click", this.export.bind(this), false);
//     }
//
//     loadPreview() {
//         let data = new FormData(document.getElementById('form'));
//         let previewRequest = new XMLHttpRequest();
//
//         previewRequest.open('POST', 'reports/api/preview/' + this.currentReportId);
//         previewRequest.onload = function () {
//             let response = JSON.parse(previewRequest.responseText);
//             if (response.success === true) {
//                 c.preview.innerHTML = response.preview;
//             } else {
//                 //todo
//             }
//         }
//
//         previewRequest.send(data);
//     }
//
//     export() {
//         let currentReportId = this.currentReportId;
//         let data = new FormData(document.getElementById('form'));
//         let exportRequest = new XMLHttpRequest();
//
//         exportRequest.open('POST', 'reports/api/export/' + currentReportId);
//         exportRequest.onload = function () {
//             let response = JSON.parse(exportRequest.responseText);
//             if (response.success === true) {
//                 window.open('reports/api/get/' + response.path, '_blank').focus();
//             } else {
//                 //todo
//             }
//         }
//
//         exportRequest.send(data);
//     }
//
//     loadReport(reportId) {
//         let c = this;
//         let formRequest = new XMLHttpRequest();
//         formRequest.open('GET', 'reports/api/form/' + reportId );
//         formRequest.onload = function () {
//             let response = JSON.parse(formRequest.responseText);
//             if (response.success === true) {
//                 c.form.innerHTML = response.form;
//                 c.addFormListeners();
//                 c.currentReportId = reportId;
//             } else {
//                 //todo
//             }
//         }
//
//         let detailsRequest = new XMLHttpRequest();
//         detailsRequest.open('GET', 'reports/api/details/' + reportId);
//         detailsRequest.onload = function () {
//             let response = JSON.parse(detailsRequest.responseText);
//             if (response.success === true) {
//                 c.details.innerHTML = response.details;
//             }
//             else {
//                 //todo
//             }
//         }
//
//         formRequest.send();
//         detailsRequest.send();
//     }
// }
//
// let c = new ReportsController();