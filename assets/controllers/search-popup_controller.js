import {Controller} from "@hotwired/stimulus";
import $ from "jquery"
import {executeAfter} from "../app";

export default class SearchPopupController extends Controller {
    static targets = [
        'form'
    ];

    popupController;

    connect() {
        $(this.formTarget).on('submit', this.onSubmit.bind(this));
        this.popupController = this.application.getControllerForElementAndIdentifier(
            document.querySelector("[data-controller='popup']"),
            "popup"
        );
    }

    onSubmit(event) {
        event.preventDefault();

        const self = this;
        const $form = $(this.formTarget);
        $.ajax({
            url: '/api/orders/search',
            method: "POST",
            data: $form.serialize(),
            success: function (data) {
                executeAfter( function () {
                    self.popupController.display(data.data.renderedSearch);
                });
            },
            error: function (jqXHR) {
                console.error(jqXHR.responseText);
                // self.controller.popupManager.display(jqXHR.responseText);
            }
        });
    }
}