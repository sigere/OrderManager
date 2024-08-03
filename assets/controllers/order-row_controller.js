import {Controller} from "@hotwired/stimulus";
import {executeAfter} from "../app";
import $ from "jquery";

export default class extends Controller {
    static targets = [
        "stateCell",
        "placeholder",
    ];

    connect() {
        const select = $(this.stateCellTarget).children('select')[0];
        select.addEventListener('change', this.stateChanged.bind(this));
    };

    stateChanged(event) {
        let $select = $(event.currentTarget);
        event.stopPropagation();

        // let self = this;
        let $row = $(event.currentTarget).closest("tr");
        let id = $row.data("subject-id");
        let type = $row.data("subject-type");

        if (type !== 'order') {
            throw new Error('Unsupported type!');
        }

        // let currentSubject = this.controller.currentSubject;
        let $cell = $select.parent();
        let $placeholder = $(this.placeholderTarget);

        $select.css({display: "none"});
        $placeholder.css({display: "block"});

        // console.log($select.val());
        $.ajax({
            url: "/api/orders/" + id + "/state",
            method: "PUT",
            data: {state: $select.val()},
            success: function (data) {
                $row.addClass("hidden");
                executeAfter(function () {
                    $row.replaceWith(data.data.renderedRow);
                    $row.removeClass("hidden");

                    // $select.css({display: "block"});
                    // $placeholder.css({display: "none"});
                    // $select.attr("data-state", $select.val());
                    // if (type === currentSubject.type &&
                    //     id === currentSubject.id) {
                    //     self.controller.detailsController.reload(currentSubject);
                    // }
                });
            },
            error: function (jqXHR) {
                console.error(jqXHR.responseText);
                // $placeholder.html("Wystąpił błąd.");
                // executeAfter(function () {
                //     $placeholder.html(window.reloadIcon);
                //     $placeholder.css({display: "none"});
                //     $select.css({display: "block"});
                // }, Date.now() + 5000);
                // self.controller.popupManager.display(jqXHR.responseText);
            }
        });
    };
}