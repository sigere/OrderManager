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
            let self = this;

            if (!subject) {
                this.$element.addClass("hidden");
                executeAfter(function () {
                    self.loadDefaultContent();
                    self.$element.removeClass("hidden");
                });
                return;
            }

            this.$element.addClass("hidden");
            $.ajax({
                url: getUrlForSubject(subject),
                method: "GET",
                success: function (data) {
                    subject.content = JSON.parse(data);
                    executeAfter(function () {
                        self.insertHTML(subject);
                        self.$element.removeClass("hidden");
                    });

                    window.history.pushState(
                        {
                            "content": subject.content,
                            "id": subject.id,
                            "type": subject.type
                        },
                        "",
                        $.query.set(subject.type, subject.id)
                    );
                },
                error: function (jqXHR) {
                    self.$element.html(jqXHR.responseText);
                }
            });
        },

        insertHTML: function (subject) {
            let $subjectId = this.$wrapper.find(".js-subject-id");
            let $burger = this.$wrapper.find(".js-burger");

            this.$element.html(subject.content.details);
            $burger.html(subject.content.burger);
            $subjectId.html(subject.id);
        },

        loadDefaultContent: function () {
            let $burger = this.$wrapper.find(".js-burger");
            let $subjectId = this.$wrapper.find(".js-subject-id");

            $burger.html("");
            $subjectId.html("");
            this.$element.html(this.defaultContent);
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
        },

        share: function (e) {
            let $row = $(e.currentTarget);
            let defaultHTML = $row.html();

            let text = window.location.origin + window.location.pathname +
                "?" + this.controller.currentSubject.type + "=" + this.controller.currentSubject.id;
            navigator.clipboard.writeText(text).then(function () {
                $row.html("<div class='alert-success'>Done!</div>");
                executeAfter(function () {
                    $row.html(defaultHTML);
                }, Date.now() + 1500);
            }, function(err) {
                console.error('Async: Could not copy text: ', err);
            });
        },

        edit: function (subject) {
            let controller = this.controller;
            controller.popupManager.open();

            $.ajax({
                url: getUrlForSubject(subject),
                method: "PUT",
                success: function (data) {
                    executeAfter( function () {
                        let $handle = controller.popupManager.display(data);
                        if (!$handle) {
                            return;
                        }
                        $handle.find("form").on(
                            "submit",
                            controller.formSubmit.bind(controller)
                        );
                    });
                },
                error: function (jqXHR) {
                    controller.popupManager.display(jqXHR.responseText);
                }
            });
        },
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