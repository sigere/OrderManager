import { Controller } from '@hotwired/stimulus'

export default class ControllerConnectedEvent extends CustomEvent {
  constructor (controller) {
    if (!(controller instanceof Controller)) {
      throw new Error('Controller must be instance of stimulus controller.')
    }

    super(controller.identifier + ':connected', {
      controller,
      bubbles: true
    })
  }
}
