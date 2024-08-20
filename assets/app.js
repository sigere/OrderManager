import 'bootstrap'
import './bootstrap.js'
import './styles/app.css'
import $ from 'jquery'
import 'tablesorter'

export const ajaxDelay = 300
export function executeAfter (executable, stamp) {
  if (stamp === undefined) {
    stamp = Date.now() + ajaxDelay
  }
  setTimeout(
    executable,
    (stamp - Date.now()) > 0 ? (stamp - Date.now()) : 0
  )
}

export function setQueryStringParameter (key, value) {
   const url = new URL(window.location.href)
   url.searchParams.set(key, value)
   window.history.pushState({}, '', url)
}

$.tablesorter.defaults.dateFormat = 'ddmmyyyy'

const sidebar = $('#sidebar')
sidebar.mouseover(function () {
  sidebar.toggleClass('active', false)
})
sidebar.mouseout(function () {
  sidebar.toggleClass('active', true)
})
