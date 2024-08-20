'use strict';

(function (window, $) {
  window.Controller = function ($wrapper) {
    this.$wrapper = $wrapper
    this.currentSubject = null

    this.filtersController = new FiltersController(
      $('.js-left-col'),
      this
    )

    this.contentTableController = new ContentTableController(
      $('.js-mid-col'),
      this
    )

    this.detailsController = new DetailsController(
      $('.js-right-col'),
      this
    )

    this.popupManager = new PopupManager(
      $('body')
    )

    this.searchController = new SearchController(
      $('.js-search-link'),
      this,
      '/search'
    )

    this._initListeners.bind(this)()
    window.onpopstate = _onPopState.bind(this)
  }

  $.extend(window.Controller.prototype, {
    addOrder: function () {
      this.popupManager.open()

      const self = this
      $.ajax({
        url: window.getUrlForSubject({ type: 'order' }),
        method: 'POST',
        success: function (data) {
          executeAfter(function () {
            const $handle = self.popupManager.display(data)
            if (!$handle) {
              return
            }
            $handle.find('form').on(
              'submit',
              self.formSubmit.bind(self)
            )
          })
        },
        error: function (jqXHR) {
          console.error(jqXHR.responseText)
          self.popupManager.display(jqXHR.responseText)
        }
      })
    },

    editEntry: function () {
      const subject = {
        id: this.$wrapper.find('.js-repertory-entry-number').data('entry'),
        type: 'entry'
      }

      this.detailsController.edit(subject)
    },

    addRepertoryEntry: function () {
      this.popupManager.open()
      const currentSubject = this.currentSubject
      const data = new FormData()
      data.append('order', currentSubject.id)

      const self = this
      $.ajax({
        url: window.getUrlForSubject({ type: 'entry' }),
        method: 'POST',
        processData: false,
        contentType: false,
        data,
        success: function (data) {
          executeAfter(function () {
            const $handle = self.popupManager.display(data)
            if (!$handle) {
              return
            }
            $handle.find('form').on(
              'submit',
              self.formSubmit.bind(self)
            )
          })
        },
        error: function (jqXHR) {
          console.error(jqXHR.responseText)
          self.popupManager.display(jqXHR.responseText)
        }
      })
    },

    delete: function () {
      this.popupManager.open()
      const currentSubject = this.currentSubject

      const self = this
      $.ajax({
        url: '/' + currentSubject.type + '/' + currentSubject.id,
        method: 'DELETE',
        success: function (data) {
          executeAfter(function () {
            const $handle = self.popupManager.display(data)
            if (!$handle) {
              return
            }
            $handle.find('form').on(
              'submit',
              self.formSubmit.bind(self)
            )
          })
        },
        error: function (jqXHR) {
          console.error(jqXHR)
          self.popupManager.display(jqXHR.responseText)
        }
      })
    },

    restore: function () {
      this.popupManager.open()
      const currentSubject = this.currentSubject

      const self = this
      $.ajax({
        url: '/' + currentSubject.type + '/' + currentSubject.id + '/restore',
        method: 'POST',
        success: function (data) {
          executeAfter(function () {
            const $handle = self.popupManager.display(data)
            if (!$handle) {
              return
            }
            $handle.find('form').on(
              'submit',
              self.formSubmit.bind(self)
            )
          })
        },
        error: function (jqXHR) {
          console.error(jqXHR)
          self.popupManager.display(jqXHR.responseText)
        }
      })
    },

    formSubmit: function (e) {
      e.preventDefault()
      const self = this
      const data = new FormData(e.currentTarget)
      const url = $(e.currentTarget).data('url')
      const method = $(e.currentTarget).data('method')
      if (method !== 'POST') {
        data.append('_method', method)
      }

      this.popupManager.default()
      $.ajax({
        url,
        method: 'POST',
        data,
        processData: false,
        contentType: false,
        success: function (data) {
          executeAfter(function () {
            const $handle = self.popupManager.display(data)
            if (!$handle) {
              return
            }
            $handle.find('form').on(
              'submit',
              self.formSubmit.bind(self)
            )
          })
        },
        error: function (jqXHR) {
          console.error(jqXHR)
          self.popupManager.display(jqXHR.responseText)
        }
      })
    },

    setCurrentSubject: function (subject) {
      this.currentSubject = subject
      this.contentTableController.reload()
      this.detailsController.reload(subject)
    },

    reloadDetails: function (subject) {
      this.detailsController.reload(subject)
    },

    reloadTable: function () {
      this.contentTableController.reload()
    },

    _initListeners: function () {
      this.$wrapper.on(
        'click',
        '.js-add-order-link',
        this.addOrder.bind(this)
      )

      this.$wrapper.on(
        'click',
        '.js-burger .js-edit-link',
        function () {
          this.detailsController.edit(this.currentSubject)
        }.bind(this)
      )

      this.$wrapper.on(
        'click',
        '.js-burger .js-edit-entry-link',
        this.editEntry.bind(this)
      )

      this.$wrapper.on(
        'click',
        '.js-burger .js-repertory-entry-link',
        this.addRepertoryEntry.bind(this)
      )

      this.$wrapper.on(
        'click',
        '.js-burger .js-delete-link',
        this.delete.bind(this)
      )

      this.$wrapper.on(
        'click',
        '.js-burger .js-restore-link',
        this.restore.bind(this)
      )

      this.$wrapper.on(
        'click',
        '.js-burger .js-share-link',
        function (e) {
          this.detailsController.share(e)
        }.bind(this)
      )
    }
  })
})(window, jQuery)
