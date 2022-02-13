class RepertoryController {
    entriesTable;
    detailsContent;
    currentEntryId;
    detailsHeaderId;
    selectedRow;

    constructor() {
        this.selectedRow = null;
        this.entriesTable = document.getElementById('entries-table');
        this.detailsContent = document.getElementById('details-content');
        this.detailsHeaderId = document.getElementById('details-header-id');
        this.addTableListeners();
    }

    addTableListeners() {
        let tableRows = this.entriesTable.getElementsByTagName("tr");
        for (let i = 1; i < tableRows.length; i++) {
            let row = tableRows[i];
            let entryId = row.getAttribute("entry-id");
            let cells = row.getElementsByTagName("td");
            for (let j = 0; j < cells.length - 2; j++) {
                cells[j].addEventListener("click", this.reloadDetails.bind(this, entryId), false);
            }
        }
        $(function () {
            $("#main-table").tablesorter({
                dateFormat: "ddmmyyyy"
            });
        });
    }

    reloadDetails(id, e) {
        if (!id) {
            this.detailsHeaderId.innerHTML = "";
            this.detailsContent.innerHTML = '<div class="alert alert-primary">Wybierz wpis, aby wyświetlić jego szczegóły</div>';
            return;
        }

        let detailsContent = this.detailsContent;
        let detailsHeaderId = this.detailsHeaderId;
        let c = this;
        this.detailsContent.classList.toggle("hidden");
        let request = new XMLHttpRequest();
        request.open("GET", "repertory/entry/" + id, true);
        let stamp = Date.now();
        request.onload =
            function (oEvent) {
                if (request.status === 200) {
                    setTimeout(function () {
                        detailsContent.innerHTML = request.responseText;
                        detailsHeaderId.innerHTML = id;
                        c.currentEntryId = id;
                        c.updateSelected();
                        detailsContent.classList.toggle("hidden");
                    }, 400 - (Date.now() - stamp) > 0 ? 400 - (Date.now() - stamp) : 0);
                } else {
                    console.log(request.status);
                    console.log(request);
                    detailsContent.innerHTML =
                        '<div class="alert alert-danger" role="alert">Wystąpił błąd "' +
                        request.status +
                        " " +
                        request.statusText +
                        '" podczas ładowania szczegółów wpisu ' +
                        id +
                        "</div>";
                    detailsContent.classList.toggle("hidden");
                }

            };
        request.send();
    }

    updateSelected() {
        let tableRows = this.entriesTable.getElementsByTagName("tr");
        if (!this.currentEntryId) {
            return;
        }

        if (this.selectedRow) {
            this.selectedRow.classList.toggle("active-row");
        }

        for (let i = 1; i < tableRows.length; i++) {
            let row = tableRows[i];
            let entryId = row.getAttribute("entry-id");
            if (entryId === this.currentEntryId) {
                this.selectedRow = row;
                break;
            }
        }

        if (this.selectedRow) {
            this.selectedRow.classList.toggle("active-row");
        }
    }
}

let c = new RepertoryController();



