$(document).ready(function () {
    $("#sidebar").mouseover(function () {
        $("#sidebar").toggleClass("active", false);
    });
    $("#sidebar").mouseout(function () {
        $("#sidebar").toggleClass("active", true);
    });
    window.subjectTypes = [
        'order',
        'log',
        'entry' //todo
    ];
});

window.reloadIcon =
    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise icon-loading" viewBox="0 0 16 16">' +
    '<path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>' +
    '<path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>' +
    '</svg>';

class Subject {
    constructor(id, type, row, content) {
        this.id = id;
        this.type = type;
        this.row = row;
        this.content = content;
    }
}

function executeAfter(executable, stamp) {
    setTimeout(
        executable,
        (stamp - Date.now()) > 0 ? (stamp - Date.now()) : 0
    );
}