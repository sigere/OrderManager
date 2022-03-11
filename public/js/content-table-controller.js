'use strict';

(function (window, $) {
    window.ContentTableController = function ($wrapper, controller) {
        this.$wrapper = $wrapper;
        this.controller = controller;
        this.$tableContainer = $wrapper.find('.js-table-container');
        this.$table = $wrapper.find('.js-main-table');

        let subjectType = null;
        let subjectId = null;
        window.subjectTypes.forEach(function (value) {
            let id = $.query.get(value);
            if (id !== "") {
                subjectType = value;
                subjectId = id;
            }
        });

        if (subjectType && subjectId) {
            this._setCurrentSubject({
                id: subjectId,
                type: subjectType,
                row: this.$table.find(
                    '[data-subject-id="' + subjectId + '"][data-subject-type="' + subjectType + '"]'
                )
            });
        }

        this.$table.tablesorter({
            dateFormat: "ddmmyyyy"
        });

        this.$wrapper.on(
            'click',
            'tbody tr',
            this.updateCurrentRow.bind(this)
        );

        this.$wrapper.on(
            'change',
            'tbody .js-update-state-cell select',
            this.updateState.bind(this)
        );

        this.$wrapper.on(
            'click',
            'tbody .js-update-state-cell select',
            function (e) {e.stopPropagation();}
        );
    }

    $.extend(window.ContentTableController.prototype, {
        _setCurrentSubject: function (currentSubject) {
            if (this.currentSubject && this.currentSubject.row) {
                this.currentSubject.row.removeClass('active-row');
            }

            this.currentSubject = currentSubject;
            this.currentSubject.row.addClass('active-row');
        },

        updateCurrentRow: function (e) {
            let $row = $(e.currentTarget);
            let subject = new Subject(
                $row.data('subjectId'),
                $row.data('subjectType'),
                $row,
                null
            );

            this._setCurrentSubject(subject)
            this.controller.reloadDetails(subject);
        },

        updateState: function (e) {
            e.stopPropagation();
            let $select = $(e.currentTarget);
            let self = this;
            let $row = $(e.currentTarget).closest('tr');
            let id = $row.data('subject-id');
            let type = $row.data('subject-type');

            $.ajax({
                url: '/api/' + type + '/' + id + '/state',
                method: 'POST',
                data: {state: $select.val()},
                success: function (data) {
                    $select.attr('data-state', $select.val());
                    if (type === self.currentSubject.type &&
                        id === self.currentSubject.id) {
                        self.controller.detailsController.reload(self.currentSubject);
                    }
                },
                error: function (jqXHR) {
                    self.controller.popupManager.display(jqXHR.responseText);
                }
            });
        },

        reload: function () {
            let self = this;
            $.ajax({
                url: '/order',
                method: 'GET',
                success: function (data) {
                    self.$tableContainer.addClass('hidden');
                    executeAfter(function () {
                        self.$table.html(data);
                        self.setAndHighlightCurrent();
                        self.$tableContainer.removeClass('hidden');
                    }, Date.now() + 400);
                },
                error: function (jqXHR) {
                    self.controller.popupManager.display(jqXHR.responseText);
                }
            });
            this.controller.detailsController.reload(this.currentSubject);
        },

        setAndHighlightCurrent: function (subject) {
            if (subject === null) {
                this.currentSubject = subject;
                this.$table.find('.active-row').removeClass('active-row');
                return;
            } else if (subject === undefined) {
                subject = this.currentSubject;
            }

            this.$table.find('.active-row').removeClass('active-row');
            let $rows = this.$table.find('tr');
            let $found = null;
            for (let i = 1; i < $rows.length; i++) {
                let $row = $($rows[i]);
                if ($row.data('subject-type') === subject.type &&
                    $row.data('subject-id') === subject.id) {
                    $found = $row;
                    break;
                }
            }

            if ($found) {
                $found.addClass('active-row');
            }
        },
    });
})(window, jQuery);