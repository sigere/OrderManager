import { Controller } from '@hotwired/stimulus'
import $ from 'jquery'

export default class extends Controller {
  static targets = ['dateHelpers']

  connect () {
    const $buttons = $(this.dateHelpersTarget.getElementsByTagName('button'))
    const $dateFromInput = $('#order_filters_form_dateFrom')
    const $dateToInput = $('#order_filters_form_dateTo')

    $buttons.each((index, button) => {
      $(button).on('click', (event) => {
        event.preventDefault()
        const $button = $(event.currentTarget)

        const interval = $button.attr('data-interval')
        const today = new Date()
        let startDate = new Date()
        let endDate = new Date()

        switch (interval) {
          case 'this_week': {
            const dayOfWeek = today.getDay()
            const diffToMonday = (dayOfWeek === 0 ? 6 : dayOfWeek - 1)
            startDate.setDate(today.getDate() - diffToMonday)
            endDate = new Date(startDate)
            endDate.setDate(startDate.getDate() + 6)
            break
          }
          case 'last_week': {
            startDate.setDate(today.getDate() - today.getDay() - 6)
            endDate.setDate(startDate.getDate() + 6)
            break
          }
          case 'this_month': {
            startDate = new Date(today.getFullYear(), today.getMonth(), 1)
            endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0)
            break
          }
          case 'last_month': {
            startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1)
            endDate = new Date(today.getFullYear(), today.getMonth(), 0)
            break
          }
        }

        const formatDate = (date) => {
          const year = date.getFullYear()
          const month = String(date.getMonth() + 1).padStart(2, '0')
          const day = String(date.getDate()).padStart(2, '0')
          return `${year}-${month}-${day}`
        }

        $dateFromInput.val(formatDate(startDate))
        $dateToInput.val(formatDate(endDate))
      })
    })
  }

  submit (event) {
    event.preventDefault()

    const form = event.currentTarget
    const $form = $(form)
    $.ajax({
      url: form.action,
      method: $form.attr('data-method'),
      data: $form.serialize(),
      success: function (data) {
        form.dispatchEvent(new CustomEvent('filtersUpdated', {
          bubbles: true
        }))
      },
      error: function (jqXHR) {
        console.error(jqXHR)
        // self.popupManager.display(jqXHR.responseText);
      }
    })
  }
}
