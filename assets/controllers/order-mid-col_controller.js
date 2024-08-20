import { Controller } from '@hotwired/stimulus'
import $ from 'jquery'
import { executeAfter } from '../app'

export default class extends Controller {
  static targets = [
    'header',
    'content',
    'tableWrapper',
    'rowsCountWrapper'
  ]

  connect () {
    $(document.documentElement).on('filtersUpdated', this.reload.bind(this))
  };

  reload () {
    const $tableWrapper = $(this.tableWrapperTarget)
    const $rowsCountWrapper = $(this.rowsCountWrapperTarget)
    const $table = $($tableWrapper.find('table')[0])
    const url = $table.data('url')
    let method = $table.data('method')
    method = (method === undefined) ? 'GET' : method

    $.ajax({
      url,
      method,
      dataType: 'json',
      success: function (data) {
        executeAfter(function () {
          $tableWrapper.html(data.data.renderedTable)
          $rowsCountWrapper.html(data.data.renderedRowsCount)
        })
      },
      error: function (jqXHR) {
        console.error(jqXHR)
        // self.controller.popupManager.display(jqXHR.responseText);
      }
    })
    // this.controller.detailsController.reload(this.controller.currentSubject);
  };
}
