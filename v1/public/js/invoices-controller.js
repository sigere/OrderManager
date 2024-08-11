'use strict';

(function (window, $) {
  window.Controller = function ($wrapper) {
    this.$wrapper = $wrapper
    this.$ordersTableContainer = $wrapper.find('.js-orders-table-container')
    this.$clientsTableContainer = $wrapper.find('.js-clients-table-container')
    this.$buyerData = $wrapper.find('.js-buyer-data')

    this.popupManager = new PopupManager(
      $('body')
    )

    this._initListeners.bind(this)()
    const self = this
    $(document).ajaxComplete(function (event, request, settings) {
      if (request.getResponseHeader('Set-Current-Subject') !== null) {
        self.reloadClient()
      }
    })
  }

  $.extend(window.Controller.prototype, {
    reloadClients: function (e) {
      const month = this.$wrapper.find(".js-form-month select[name='invoice_month_form[month]']").val()
      const year = this.$wrapper.find(".js-form-month select[name='invoice_month_form[year]']").val()

      this.$clientsTableContainer.addClass('hidden')
      const self = this
      $.ajax({
        url: '/invoices/client',
        method: 'GET',
        data: {
          month,
          year
        },
        success: function (data) {
          executeAfter(function () {
            self.$clientsTableContainer.html(data)
            self.$clientsTableContainer.removeClass('hidden')
          })
        },
        error: function (jqXHR) {
          console.error(jqXHR.responseText)
          self.popupManager.display(window.formatter.error(jqXHR.responseText))
        }
      })
    },

    selectClient: function (e) {
      const $row = $(e.currentTarget)
      this.currentClient = {
        type: 'client',
        id: $row.data('subject-id')
      }
      this.reloadClient()
    },

    reloadClient: function () {
      const self = this

      self.$ordersTableContainer.addClass('hidden')
      self.$buyerData.addClass('hidden')
      $.ajax({
        url: '/invoices/client/' + self.currentClient.id,
        method: 'GET',
        success: function (data) {
          executeAfter(function () {
            self.$ordersTableContainer.html(data.orders)
            self.$buyerData.html(data.client)

            self.$ordersTableContainer.removeClass('hidden')
            self.$buyerData.removeClass('hidden')
            self.applyTableSorter()
          })
        },
        error: function (jqXHR) {
          console.error(jqXHR)
          self.popupManager.display(jqXHR.responseText)
        }
      })
    },

    settle: function (invoice = false, e) {
      e.preventDefault()
      if (!this.currentClient) {
        this.popupManager.display(window.formatter.error('Nie wybrano klienta'))
        return
      }

      const $buttons = this.$wrapper.find('.js-buttons')
      const buttonsHtml = $buttons.html()

      const orders = this._getOrders()
      const issueDate = this.$wrapper.find('.js-issue-date').val()
      const paymentDate = this.$wrapper.find('.js-payment-date').val()
      const url = invoice ? '/invoices/invoice' : '/invoices/settle'
      const method = invoice ? 'POST' : 'PUT'

      $buttons.html(window.reloadIcon)
      const self = this
      $.ajax({
        url,
        method,
        data: {
          client: self.currentClient.id,
          orders,
          issue_date: issueDate,
          payment_date: paymentDate
        },
        success: function (data) {
          self.popupManager.display(data)
          executeAfter(function () {
            $buttons.html(buttonsHtml)
          })
          self.reloadClient()
        },
        error: function (jqXHR) {
          executeAfter(function () {
            $buttons.html(buttonsHtml)
          })
          console.error(jqXHR)
          self.popupManager.display(jqXHR.responseText)
        }
      })
    },

    updateNetto: function () {
      const $nettoSummaryCell = this.$wrapper.find('.js-orders-table .js-netto-summary')
      const $rows = this.$ordersTableContainer.find("tr[data-valid='1']")

      let sum = 0
      for (let i = 0; i < $rows.length; i++) {
        const $row = $($rows[i])
        if ($row.find('input').is(':checked')) {
          sum += $row.data('netto')
        }
      }
      $nettoSummaryCell.html(sum + 'PLN')
    },

    onRowClicked: function (e) {
      const $row = $(e.target).closest('tr')
      let checkbox = $row.find("input[type='checkbox']")
      checkbox = checkbox.length === 1 ? checkbox[0] : null

      this.updateNetto()
      if (checkbox) {
        checkbox.checked = !checkbox.checked
      }
    },

    applyTableSorter: function () {
      this.$ordersTableContainer.find('.js-orders-table').tablesorter({
        dateFormat: 'ddmmyyyy'
      })
    },

    openOrderInNewTab: function (e) {
      const $row = $(e.currentTarget).closest('tr')
      const subject = {
        id: $row.data('subject-id'),
        type: $row.data('subject-type')
      }

      const self = this
      this.popupManager.open()
      $.ajax({
        url: getUrlForSubject(subject),
        method: 'PUT',
        success: function (data) {
          executeAfter(function () {
            const $handle = self.popupManager.display(data)
            if (!$handle) {
              return
            }
            $handle.find('form').on(
              'submit',
              self.formSubmit.bind(controller)
            )
          })
        },
        error: function (jqXHR) {
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

    _getOrders: function () {
      const $rows = this.$ordersTableContainer.find("tr[data-valid='1']")
      const ids = []
      for (let i = 0; i < $rows.length; i++) {
        const $row = $($rows[i])
        if ($row.data('subject-type') === 'order' && $row.find('input').is(':checked')) {
          ids.push($row.data('subject-id'))
        }
      }
      return ids
    },

    _initListeners: function () {
      const self = this

      this.$wrapper.on(
        'change',
        '.js-form-month select',
        this.reloadClients.bind(this)
      )

      this.$wrapper.on(
        'click',
        '.js-left-col tbody tr',
        this.selectClient.bind(this)
      )

      this.$wrapper.on(
        'click',
        '.js-orders-table input[type="checkbox"]',
        this.updateNetto.bind(this)
      )

      this.$wrapper.on(
        'click',
        '.js-button-invoice',
        this.settle.bind(this, true)
      )

      this.$wrapper.on(
        'click',
        '.js-button-settle',
        this.settle.bind(this, false)
      )

      this.$wrapper.on(
        'click',
        '.js-edit-order-link',
        this.openOrderInNewTab.bind(this)
      )
    }
  })
})(window, jQuery)
