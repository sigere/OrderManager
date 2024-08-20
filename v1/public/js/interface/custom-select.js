'use strict';

(function (window, $) {
  $(document).ready(function () {
    $('.filters-form select').each(function () {
      if ($(this).hasClass('js-no-transformation')) {
        console.log(this)
        return
      }

      const self = this
      const boundListener = scrollToElement.bind(self)
      $.extend(self, {
        removeListener: function () {
          document.body.removeEventListener('keypress', boundListener)
        }
      })

      const $this = $(this)
      const numberOfOptions = $(this).children('option').length
      const currentText = $(this).find(':selected').text()

      $this.addClass('hidden')
      $this.wrap('<div class="select"></div>')
      $this.after('<div class="select-styled"></div>')

      const $styledSelect = $this.next('div.select-styled')
      $styledSelect.text($this.children('option').eq(0).text())

      const $list = $('<ul />', {
        class: 'select-options'
      }).insertAfter($styledSelect)

      for (let i = 0; i < numberOfOptions; i++) {
        $('<li />', {
          text: $this.children('option').eq(i).text(),
          rel: $this.children('option').eq(i).val()
        }).appendTo($list)
      }

      const $listItems = $list.children('li')

      $styledSelect.click(function (e) {
        e.stopPropagation()
        document.body.addEventListener('keypress', boundListener)
        $('div.select-styled.active').not(this).each(function () {
          $(this).removeClass('active').next('ul.select-options').hide()
          const listenerHolder = $(this).prev('select')[0]
          listenerHolder.removeListener()
        })
        $(this).toggleClass('active').next('ul.select-options').toggle()
      })

      $listItems.click(function (e) {
        e.stopPropagation()
        $styledSelect.text($(this).text()).removeClass('active')
        $this.val($(this).attr('rel'))
        $list.hide()
        $this.trigger('change')
      })

      $(document).click(function () {
        document.body.removeEventListener('keypress', boundListener)
        $styledSelect.removeClass('active')
        $list.hide()
      })
      $styledSelect.text(currentText)
    })
  })

  function scrollToElement (e) {
    const letter = e.key.toUpperCase()
    const $div = $(this).next().next()
    const $children = $div.children()
    for (let i = 0; i < $children.length; i++) {
      const currentFirstLetter = Array.from($children[i].innerHTML)[0].toUpperCase()
      if (currentFirstLetter === letter) {
        $div[0].scrollTop = $children[i].offsetTop - 20
        return
      }
    }
  }
})(window, jQuery)
