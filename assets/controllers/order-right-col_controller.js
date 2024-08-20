import { Controller } from '@hotwired/stimulus'
import $ from 'jquery'
import { executeAfter } from '../app'
import { setQueryStringParameter } from '../app'

export default class extends Controller {
   static targets = [
      'header',
      'details',
      'id',
   ]

   popupController

   connect () {
      const self = this;
      $(document).ready(() => {
         self.popupController = self.application.getControllerForElementAndIdentifier(
            document.querySelector("[data-controller='popup']"),
            'popup'
         )

      //    TMP
         self.loadOrder(57218)
      })

      // $(this.element).on('click', this.addOrder.bind(this))
   }

   loadOrder (id) {
      const self = this
      $.ajax({
         url: `/api/order/details/${id}`,
         method: 'GET',
         success: (data) => {
            setQueryStringParameter('order', id)
            self.detailsTarget.innerHTML = data.data.renderedDetails
            self.idTarget.innerHTML = data.data.id
         },
         error: (error) => {
            console.error(error)
         }
      })
   }
}
