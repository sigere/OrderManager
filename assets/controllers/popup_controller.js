import { Controller } from '@hotwired/stimulus'
import $ from 'jquery'
import { executeAfter } from '../app'
import ControllerConnectedEvent from '../Evenets/controller-connected_event'

export default class extends Controller {
  static targets = [
    'content',
    'overlay'
  ]

  $overlay
  $content
  $wrapper
  defaultContent

  connect () {
    this.$content = $(this.contentTarget)
    this.$overlay = $(this.overlayTarget)
    this.$wrapper = $(this.element)
    this.defaultContent = this.$content.html()

    this.$overlay.on(
      'click',
      this.close.bind(this)
    )

    $(document).on(
      'keydown',
      function (event) {
        if (event.key === 'Escape') {
          this.close()
        }
      }.bind(this)
    )

    this.element.dispatchEvent(new ControllerConnectedEvent(this))
  }

  open () {
    if (!this.$wrapper.hasClass('active')) {
      this.$wrapper.addClass('active')
    }
  };

  close () {
    if (this.$wrapper.hasClass('active')) {
      this.$wrapper.removeClass('active')
    }

    executeAfter(function () {
      this.$content.html(this.defaultContent)
    }.bind(this), Date.now() + 250)
  };

  default () {
    this.$content.html(this.defaultContent)
  };

  display (data) {
    this.open()
    this.$content.html(data)
  };
}
