import { Controller } from '@hotwired/stimulus'
import $ from 'jquery'
import { executeAfter } from '../app'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
   // static targets = [
   //    'button'
   // ]

   popupController

   connect () {
      const self = this;
      $(document).ready(() => {
         self.popupController = self.application.getControllerForElementAndIdentifier(
            document.querySelector("[data-controller='popup']"),
            'popup'
         )
      })

      $(this.element).on('click', this.addOrder.bind(this))
   }

   addOrder (event) {
      event.preventDefault()
      const self = this

      this.popupController.open();
      $.ajax({
         url: '/api/order',
         method: 'POST',
         success: function (data) {
            executeAfter(function () {
               self.popupController.display(data.data.renderedForm)
            })
         },
         error: function (jqXHR) {
            console.error(jqXHR.responseText)
            // self.controller.popupManager.display(jqXHR.responseText);
         }
      })
   }
}
