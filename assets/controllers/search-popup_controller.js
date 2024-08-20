import { Controller } from '@hotwired/stimulus'
import $ from 'jquery'
import { executeAfter } from '../app'

export default class SearchPopupController extends Controller {
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
   }

   onSubmit (event) {
      event.preventDefault()
      this.popupController.default()

      const self = this
      const $form = $(this.formTarget)
      $.ajax({
         url: '/api/order/search',
         method: 'POST',
         data: $form.serialize(),
         success: function (data) {
            executeAfter(function () {
               self.popupController.display(data.data.renderedSearch)
            })
         },
         error: function (jqXHR) {
            console.error(jqXHR.responseText)

            const data = JSON.parse(jqXHR.responseText)
            if (data.data.renderedSearch !== undefined) {
               executeAfter(function () {
                  self.popupController.display(data.data.renderedSearch)
               })
            }

            // self.controller.popupManager.display(jqXHR.responseText);
         }
      })
   }
}
