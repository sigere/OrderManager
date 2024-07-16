import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {
    connect() {
        console.log(this.element);
        $(this.element).mouseover(function () {
            $("#sidebar").toggleClass("active", false);
        });
        $(this.element).mouseout(function () {
            $("#sidebar").toggleClass("active", true);
        });
    }
}