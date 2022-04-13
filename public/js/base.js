$(document).ready(function () {
    $("#sidebar").mouseover(function () {
        $("#sidebar").toggleClass("active", false);
    });
    $("#sidebar").mouseout(function () {
        $("#sidebar").toggleClass("active", true);
    });
});

window.reloadIcon =
    "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"32\" height=\"32\" fill=\"currentColor\" class=\"bi bi-arrow-clockwise icon-loading\" viewBox=\"0 0 16 16\">" +
    "<path fill-rule=\"evenodd\" d=\"M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z\"/>" +
    "<path d=\"M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z\"/>" +
    "</svg>";

window.ajaxDelay = 300;

class Subject {
    constructor(id, type, row, content) {
        this.id = id;
        this.type = type;
        this.row = row;
        this.content = content;
    }
}

window.subjectTypes = [
    "order",
    "log",
    "client",
    "task",
    "entry"
];

/**
 * Backend formatter: App\Service\ResponseFormatter
 */
window.formatter = {
    error: function (message) {
        return "<div class='alert alert-danger'>" + message + "</div>";
    },
    success: function (message) {
        return "<div class='alert alert-success'>" + message + "</div>";
    },
    notice: function (message) {
        return "<div class='alert alert-primary'>" + message + "</div>";
    }
};

function getUrlForSubject(subject) {
    let result;
    switch (subject.type) {
        case "order":
            result = "/order";
            break;
        case "entry":
            result = "/repertory/entry";
            break;
        case "client":
            result = "/clients/client";
            break;
        case "task":
            result = "/tasks/task";
            break;
        default:
            return undefined;
    }
    return subject.id ? result + "/" + subject.id : result;
}

function executeAfter(executable, stamp) {
    if (stamp === undefined) {
        stamp = Date.now() + window.ajaxDelay;
    }
    setTimeout(
        executable,
        (stamp - Date.now()) > 0 ? (stamp - Date.now()) : 0
    );
}

function _onPopState(e) {
    let subject = e.state;
    if (subject === null) {
        this.detailsController.loadDefaultContent();
        return;
    }
    if (
        subject &&
        window.subjectTypes.indexOf(subject.type) > -1 &&
        subject.content &&
        subject.id &&
        subject.type &&
        subject.content.burger &&
        subject.content.details
    ) {
        this.detailsController.insertHTML(subject);
    } else {
        console.error("Error onpopstate. Subject:", subject);
    }
}

$(document).ajaxComplete(function(event, request, settings) {
    let header = request.getResponseHeader("Set-Current-Subject");
    if (header !== null) {
        header = header.split("/");
        let subject = {
            type: header[0],
            id: parseInt(header[1])
        };
        console.log("controller" in window);
        console.log("setCurrentSubject" in window.controller);
        if ("controller" in window && "setCurrentSubject" in window.controller) {
            window.controller.setCurrentSubject(subject);
        }
    }
});