import Vue from 'vue'

let MetaPlugin = {};

MetaPlugin.install = function (Vue, options) {

  Vue.prototype.$metaDescription = function(str) {
    if (typeof str !== 'undefined') {
      str = str.replace(/(<([^>]+)>)/ig,"")
      return [
        { hid: 'description', name: 'description', content: str },
        { hid: 'twitter:description', name: 'twitter:description', content: str },
        { hid: 'og:description', property: 'og:description', content: str }
      ]
    } else {
      return []
    }
  },

  Vue.prototype.$metaTitles = function(str) {
    if (typeof str !== 'undefined') {
      return [
        { hid: 'twitter:title', name: 'twitter:title', content: str + ' | Breaking Ground' },
        { hid: 'og:title', property: 'og:title', content: str + ' | Breaking Ground' }
      ]
    } else {
      return []
    }
  },

  Vue.prototype.$metaImages = function(url) {
    if (typeof url !== 'undefined') {
      return [
        { hid: 'image', name: 'image', content: url },
        { hid: 'og:image', property: 'og:image', content: url },
        { hid: 'twitter:image', name: 'twitter:image', content: url }
      ]
    } else {
      return []
    }
  }

}

Vue.use(MetaPlugin)
