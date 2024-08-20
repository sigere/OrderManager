import { Controller } from '@hotwired/stimulus'
import $ from 'jquery'
import { executeAfter } from '../app'

export default class extends Controller {
   static targets = [
      'form'
   ]

   popupController

   connect () {
      const self = this
      $(this.formTarget).on('submit', this.onSubmit.bind(this))

      $(document).ready(() => {
         self.popupController = self.application.getControllerForElementAndIdentifier(
            document.querySelector('[data-controller=\'popup\']'),
            'popup'
         )
      })
   };

   onSubmit (event) {
      event.preventDefault()
      this.popupController.default()

      const self = this
      const $form = $(this.formTarget)
      $.ajax({
         url: '/api/order',
         method: 'POST',
         data: $form.serialize(),
         success: function (data) {
            executeAfter(function () {
               self.popupController.display(data.message)
            })
         },
         error: function (jqXHR) {
            console.error(jqXHR.responseText)
            // self.controller.popupManager.display(jqXHR.responseText);
         }
      })
   }
}
