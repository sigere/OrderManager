'use strict';

(function (window, $) {
  window.FiltersController = function ($wrapper, controller) {
    this.$wrapper = $wrapper
    this.controller = controller

    this.$wrapper.on(
      'submit',
      'form',
      this.submit.bind(this)
    )
  }

  $.extend(window.FiltersController.prototype, {
    submit: function (e) {
      e.preventDefault()
      const self = this
      const data = new FormData(e.currentTarget)
      const url = $(e.currentTarget).data('url')
      const method = $(e.currentTarget).data('method')
      if (method !== 'POST') {
        data.append('_method', method)
      }

      $.ajax({
        url,
        method: 'POST',
        data,
        processData: false,
        contentType: false,
        success: function (data) {
          self.controller.reloadTable()
        },
        error: function (jqXHR) {
          console.error(jqXHR)
          self.popupManager.display(jqXHR.responseText)
        }
      })
    }
  })
})(window, jQuery)
