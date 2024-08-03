import 'bootstrap';
import './bootstrap.js';
import './styles/app.css';
import $ from "jquery";
import "tablesorter"

export const ajaxDelay = 300;
export function executeAfter(executable, stamp) {
    if (stamp === undefined) {
        stamp = Date.now() + ajaxDelay;
    }
    setTimeout(
        executable,
        (stamp - Date.now()) > 0 ? (stamp - Date.now()) : 0
    );
}

$.tablesorter.defaults.dateFormat = 'ddmmyyyy';

let sidebar = $("#sidebar");
sidebar.mouseover(function () {
    sidebar.toggleClass("active", false);
});
sidebar.mouseout(function () {
    sidebar.toggleClass("active", true);

});
