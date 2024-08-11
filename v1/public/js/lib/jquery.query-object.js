/**
 * jQuery.query - Query String Modification and Creation for jQuery
 * Written by Blair Mitchelmore (blair DOT mitchelmore AT gmail DOT com)
 * Licensed under the WTFPL (http://sam.zoy.org/wtfpl/).
 * Date: 2009/8/13
 *
 * @author Blair Mitchelmore
 * @version 2.2.3
 *
 **/
new function (settings) {
  // Various Settings
  const $separator = settings.separator || '&'
  const $spaces = settings.spaces !== false
  const $suffix = settings.suffix === false ? '' : '[]'
  const $prefix = settings.prefix !== false
  const $hash = $prefix ? settings.hash === true ? '#' : '?' : ''
  const $numbers = settings.numbers !== false

  jQuery.query = new function () {
    const is = function (o, t) {
      return o != undefined && o !== null && (t ? o.constructor == t : true)
    }
    const parse = function (path) {
      let m; const rx = /\[([^[]*)\]/g; const match = /^([^[]+)(\[.*\])?$/.exec(path); const base = match[1]; const tokens = []
      while (m = rx.exec(match[2])) tokens.push(m[1])
      return [base, tokens]
    }
    const set = function (target, tokens, value) {
      let o; const token = tokens.shift()
      if (typeof target !== 'object') target = null
      if (token === '') {
        if (!target) target = []
        if (is(target, Array)) {
          target.push(tokens.length == 0 ? value : set(null, tokens.slice(0), value))
        } else if (is(target, Object)) {
          var i = 0
          while (target[i++] != null);
          target[--i] = tokens.length == 0 ? value : set(target[i], tokens.slice(0), value)
        } else {
          target = []
          target.push(tokens.length == 0 ? value : set(null, tokens.slice(0), value))
        }
      } else if (token && token.match(/^\s*[0-9]+\s*$/)) {
        var index = parseInt(token, 10)
        if (!target) target = []
        target[index] = tokens.length == 0 ? value : set(target[index], tokens.slice(0), value)
      } else if (token) {
        var index = token.replace(/^\s*|\s*$/g, '')
        if (!target) target = {}
        if (is(target, Array)) {
          const temp = {}
          for (var i = 0; i < target.length; ++i) {
            temp[i] = target[i]
          }
          target = temp
        }
        target[index] = tokens.length == 0 ? value : set(target[index], tokens.slice(0), value)
      } else {
        return value
      }
      return target
    }

    const queryObject = function (a) {
      const self = this
      self.keys = {}

      if (a.queryObject) {
        jQuery.each(a.get(), function (key, val) {
          self.SET(key, val)
        })
      } else {
        self.parseNew.apply(self, arguments)
      }
      return self
    }

    queryObject.prototype = {
      queryObject: true,
      parseNew: function () {
        const self = this
        self.keys = {}
        jQuery.each(arguments, function () {
          let q = '' + this
          q = q.replace(/^[?#]/, '') // remove any leading ? || #
          q = q.replace(/[;&]$/, '') // remove any trailing & || ;
          if ($spaces) q = q.replace(/[+]/g, ' ') // replace +'s with spaces

          jQuery.each(q.split(/[&;]/), function () {
            const key = decodeURIComponent(this.split('=')[0] || '')
            let val = decodeURIComponent(this.split('=')[1] || '')

            if (!key) return

            if ($numbers) {
              if (/^[+-]?[0-9]+\.[0-9]*$/.test(val)) // simple float regex
              { val = parseFloat(val) } else if (/^[+-]?[1-9][0-9]*$/.test(val)) // simple int regex
              { val = parseInt(val, 10) }
            }

            val = (!val && val !== 0) ? true : val

            self.SET(key, val)
          })
        })
        return self
      },
      has: function (key, type) {
        const value = this.get(key)
        return is(value, type)
      },
      GET: function (key) {
        if (!is(key)) return this.keys
        const parsed = parse(key); const base = parsed[0]; const tokens = parsed[1]
        let target = this.keys[base]
        while (target != null && tokens.length != 0) {
          target = target[tokens.shift()]
        }
        return typeof target === 'number' ? target : target || ''
      },
      get: function (key) {
        const target = this.GET(key)
        if (is(target, Object)) { return jQuery.extend(true, {}, target) } else if (is(target, Array)) { return target.slice(0) }
        return target
      },
      SET: function (key, val) {
        if (!key.includes('__proto__')) {
          const value = !is(val) ? null : val
          const parsed = parse(key); const base = parsed[0]; const tokens = parsed[1]
          const target = this.keys[base]
          this.keys[base] = set(target, tokens.slice(0), value)
        }
        return this
      },
      set: function (key, val) {
        return this.copy().SET(key, val)
      },
      REMOVE: function (key, val) {
        if (val) {
          const target = this.GET(key)
          if (is(target, Array)) {
            for (tval in target) {
              target[tval] = target[tval].toString()
            }
            const index = $.inArray(val, target)
            if (index >= 0) {
              key = target.splice(index, 1)
              key = key[index]
            } else {
              return
            }
          } else if (val != target) {
            return
          }
        }
        return this.SET(key, null).COMPACT()
      },
      remove: function (key, val) {
        return this.copy().REMOVE(key, val)
      },
      EMPTY: function () {
        const self = this
        jQuery.each(self.keys, function (key, value) {
          delete self.keys[key]
        })
        return self
      },
      load: function (url) {
        const hash = url.replace(/^.*?[#](.+?)(?:\?.+)?$/, '$1')
        const search = url.replace(/^.*?[?](.+?)(?:#.+)?$/, '$1')
        return new queryObject(url.length == search.length ? '' : search, url.length == hash.length ? '' : hash)
      },
      empty: function () {
        return this.copy().EMPTY()
      },
      copy: function () {
        return new queryObject(this)
      },
      COMPACT: function () {
        function build (orig) {
          const obj = typeof orig === 'object' ? is(orig, Array) ? [] : {} : orig
          if (typeof orig === 'object') {
            function add (o, key, value) {
              if (is(o, Array)) { o.push(value) } else { o[key] = value }
            }
            jQuery.each(orig, function (key, value) {
              if (!is(value)) return true
              add(obj, key, build(value))
            })
          }
          return obj
        }
        this.keys = build(this.keys)
        return this
      },
      compact: function () {
        return this.copy().COMPACT()
      },
      toString: function () {
        const i = 0; const queryString = []; const chunks = []; const self = this
        const encode = function (str) {
          str = str + ''
          str = encodeURIComponent(str)
          if ($spaces) str = str.replace(/%20/g, '+')
          return str
        }
        const addFields = function (arr, key, value) {
          if (!is(value) || value === false) return
          const o = [encode(key)]
          if (value !== true) {
            o.push('=')
            o.push(encode(value))
          }
          arr.push(o.join(''))
        }
        const build = function (obj, base) {
          const newKey = function (key) {
            return !base || base == '' ? [key].join('') : [base, '[', key, ']'].join('')
          }
          jQuery.each(obj, function (key, value) {
            if (typeof value === 'object') { build(value, newKey(key)) } else { addFields(chunks, newKey(key), value) }
          })
        }

        build(this.keys)

        if (chunks.length > 0) queryString.push($hash)
        queryString.push(chunks.join($separator))

        return queryString.join('')
      }
    }

    return new queryObject(location.search, location.hash)
  }()
}(jQuery.query || {}) // Pass in jQuery.query as settings object
