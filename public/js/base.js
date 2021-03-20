$(document).ready(function () {
    $("#sidebar").mouseover(function () {
        $("#sidebar").toggleClass("active", false);
    });
    $("#sidebar").mouseout(function () {
        $("#sidebar").toggleClass("active", true);
    });
});
