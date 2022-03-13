"use strict";

(function (window, $) {
    window.DetailsController = function ($wrapper, controller) {
        this.$wrapper = $wrapper;
        this.controller = controller;
        this.$element = this.$wrapper.find(".col-content");
        this.defaultContent = this.$element.html();

        this.$element.on(
            'click',
            '.expandable .trigger .property .value',
            this.manageExpandable.bind(this)
        );
    };

    $.extend(window.DetailsController.prototype, {
        reload: function (subject) {
            let $burger = this.$wrapper.find(".js-burger");
            let $subjectId = this.$wrapper.find(".js-subject-id");
            let self = this;

            if (!subject) {
                this.$element.addClass("hidden");
                executeAfter(function () {
                    $burger.html("");
                    self.$element.html(self.defaultContent);
                    self.$element.removeClass("hidden");
                }, Date.now() + 400);
                return;
            }

            let stamp = Date.now() + 400;
            this.$element.addClass("hidden");
            $.ajax({
                url: "/order/" + subject.id,
                method: "GET",
                success: function (data) {
                    let parsed = JSON.parse(data);
                    executeAfter(function () {
                        self.$element.html(parsed.details);
                        $burger.html(parsed.burger);
                        $subjectId.html(subject.id);
                        self.$element.removeClass("hidden");
                    }, stamp);
                },
                error: function (jqXHR) {
                    self.$element.html(jqXHR.responseText);
                }
            });
        },

        manageExpandable: function (e) {
            let $svg = this.$element.find('.expandable .trigger .property svg');
            let $body = this.$element.find('.expandable .body');
            if ($body.hasClass('active')) {
                $svg.css("rotate", "none");
                $body.removeClass('active');
            } else {
                $svg.css("rotate", "90deg");
                $body.addClass('active');
            }
        }
    });

})(window, jQuery);


// class DetailsController {
//     constructor(idHolder, contentHolder, subjectType, onUpdate) {
//         this.idHolder = idHolder;
//         this.contentHolder = contentHolder;
//         this.subjectType = subjectType;
//         this.current = null;
//         this.onUpdate = onUpdate;
//
//         $.query.get(subjectType);
//         if (idHolder.innerHTML !== "") {
//
//         }
//     }
//
//     executeAfter(executable, stamp) {
//         setTimeout(
//             executable,
//             (stamp - Date.now()) > 0 ? (stamp - Date.now()) : 0
//         );
//     }
//
//     hide() {
//         if (!this.contentHolder.classList.contains("hidden")) {
//             this.contentHolder.classList.toggle("hidden")
//         }
//     }
//
//     expose() {
//         if (this.contentHolder.classList.contains("hidden")) {
//             this.contentHolder.classList.toggle("hidden")
//         }
//     }
//
//     reload(id) {
//         let storedThis = this;
//
//         let request = new XMLHttpRequest();
//         request.open(
//             "GET",
//             this.subjectType + "/" + id,
//             true
//         );
//
//         request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
//         request.onload = function (oEvent) {
//             storedThis.executeAfter(function () {
//                 storedThis.contentHolder.innerHTML = request.responseText;
//                 storedThis.idHolder.innerHTML = id;
//                 storedThis.current = new Subject(
//                     Number(id),
//                     request.responseText,
//                     storedThis.subjectType
//                 );
//                 storedThis.pushState({
//                     "content": request.responseText,
//                     "id": id,
//                     "type": storedThis.subjectType
//                 });
//                 storedThis.expose();
//                 storedThis.onUpdate();
//             }, Date.now() + 400);
//         };
//
//         this.hide();
//         request.send();
//     }
//
//     pushState(subject) {
//         window.history.pushState(
//             {
//                 "content": subject.content,
//                 "id": subject.id
//             },
//             "",
//             $.query.set(this.subjectType, subject.id)
//         );
//     }
//
//     popState(event) {
//         let subject = event.state;
//         let storedThis = this;
//         this.hide();
//         this.executeAfter(function () {
//             storedThis.contentHolder.innerHTML = subject.content;
//             storedThis.idHolder.innerHTML = subject.id;
//             storedThis.currentId = Number(subject.id);
//             storedThis.expose();
//         }, Date.now() + 400);
//     }
// }