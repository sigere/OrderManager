import { Controller } from '@hotwired/stimulus'
import $ from 'jquery'
import { executeAfter } from '../app'

export default class extends Controller {
  static targets = []

  popupController

  connect () {
    const self = this
    $(this.element).on('click', this.onButtonClicked.bind(this))
    $(document).on('popup:connected', () => {
      self.popupController = self.application.getControllerForElementAndIdentifier(
        document.querySelector("[data-controller='popup']"),
        'popup'
      )
    })
  };

  onButtonClicked (event) {
    this.popupController.open()

    const self = this
    $.ajax({
      url: '/api/order/search',
      method: 'GET',
      success: (data) => {
        executeAfter(() => {
          self.popupController.display(data.data.renderedSearch)
        })
      },
      error: function (jqXHR) {
        console.error(jqXHR.responseText)
        // self.controller.popupManager.display(jqXHR.responseText);
      }
    })
  };
}
