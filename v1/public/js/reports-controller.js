'use strict';

(function (window, $) {
  window.Controller = function ($wrapper) {
    this.$wrapper = $wrapper
    this.currentReport = null
    this.currentReportParameters = null
    this.$content = $wrapper.find('.js-table-container')
    this.$rowsCountContainer = $wrapper.find('.js-rows-count-container')

    this.popupManager = new PopupManager(
      $('body')
    )

    this._initListeners.bind(this)()
    window.onpopstate = window._onPopState.bind(this)
  }

  $.extend(window.Controller.prototype, {
    onSuccessExecuteResponse: function (data) {
      this.$content.html(data)
    },

    execute: function (e) {
      const self = this
      const reportId = $(e.currentTarget).data('report-id')
      const url = '/reports/execute/' + reportId
      this.popupManager.default()
      this.popupManager.open()
      $.ajax({
        url,
        method: 'POST',
        success: function (data) {
          executeAfter(function () {
            const $handle = self.popupManager.display(data)
            $handle.find('form').on(
              'submit',
              self.reportFormSubmit.bind(self)
            )
          })
        },
        error: function (jqXHR) {
          console.error(jqXHR)
          self.popupManager.display(jqXHR.responseText)
        }
      })
    },

    download: function () {
      console.log('tak')
      console.log(this.currentReport)
      let url = '/reports/export/' + this.currentReport
      url += '?' + new URLSearchParams(this.currentReportParameters).toString()
      $.ajax({
        url,
        method: 'GET',
        success: function (data) {
          if (typeof data === 'object' && data.path) {
            console.log(data.path)
            window.open('/reports/download/' + data.path)
            return
          }
          console.error('Invalid response', data)
        },
        error: function (jqXHR) {
          console.error(jqXHR)
          self.popupManager.display(jqXHR.responseText)
        }
      })
    },

    reportFormSubmit: function (e) {
      e.preventDefault()
      const self = this
      const data = new FormData(e.currentTarget)
      this.currentReportParameters = data
      this.currentReport = $(e.currentTarget).data('report-id')
      let url = $(e.currentTarget).data('url')
      const method = $(e.currentTarget).data('method')
      if (method === 'GET' || method === 'get') {
        url += '?' + new URLSearchParams(data).toString()
      }
      if (method !== 'POST') {
        data.append('_method', method)
      }

      this.popupManager.default()
      $.ajax({
        url,
        data,
        method: 'POST',
        processData: false,
        contentType: false,
        success: function (data) {
          executeAfter(function () {
            if (typeof data === 'object' && data.content) {
              self.onSuccessExecuteResponse(data.content)
              self.popupManager.close()

              if (data.rowsCount) {
                self.$rowsCountContainer.html(data.rowsCount)
              }

              if (data.burger) {
                const $burger = self.$wrapper.find('.js-burger')
                $burger.html(data.burger)
              }
              return
            }

            const $handle = self.popupManager.display(data)
            $handle.find('form').on(
              'submit',
              self.reportFormSubmit.bind(self)
            )
          })
        },
        error: function (jqXHR) {
          console.error(jqXHR)
          self.popupManager.display(jqXHR.responseText)
        }
      })
    },

    _initListeners: function () {
      this.$wrapper.on(
        'click',
        '.js-reports-list-element',
        this.execute.bind(this)
      )

      this.$wrapper.on(
        'click',
        '.js-download-link',
        this.download.bind(this)
      )
    }
  })
})(window, jQuery)
