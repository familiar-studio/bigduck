import axios from 'axios'

export const state = () => ({
  localHostname: 'https://wordpress.bigduck.dev/wp-json/',
  remoteHostname: 'http://bigduck-wordpress.familiar.studio/wp-json/',
  bareLocalHostname: 'http://wordpress.bigduck.dev',
  bareRemoteHostname: 'http://bigduck-wordpress.familiar.studio',
  callouts: null,
  categories: null,
  previousQuery: '',
  categoriesPath: 'wp/v2/categories/',
  sectors: null,
  topics: null,
  types: null,
  postsPerPage: 10,
  sectorsPath: 'wp/v2/sector',
  topicsPath: 'wp/v2/topic',
  typesPath: 'wp/v2/type',
  page: null
})

export const mutations = {
  setCallouts (state, data) {
    state.callouts = data
  },
  setCategories (state, data) {
    state.categories = data
  },
  setSectors (state, data) {
    state.sectors = data
  },
  setTopics (state, data) {
    state.topics = data
  },
  setTypes (state, data) {
    state.types = data
  },
  setQueryString (state, data) {
    state.queryString = data
  },
  resetPage (state) {
    state.page = 0
  },
  setEvents (state, data) {
    state.events = data
  }
}

export const actions = {
  injectFormAction () {
      let forms = document.querySelector('[id^="gform_"]')
    },
    error (context, error) {
      console.warn(error)
    },
    subscribe (context, email) {
      // post email to the subscription service
      // and then respond with a thank you
    },
    async fetchCallouts (context) {
      let response = await axios.get(context.getters.hostname + 'wp/v2/bd_callout')
      context.commit('setCallouts', response.data)
    },
    async fetchCategories (context) {
      let response = await axios.get(context.getters.hostname + context.state.categoriesPath)
      context.commit('setCategories', response.data)
    },
    async fetchTopics (context) {
      let response = await axios.get(context.getters.hostname + context.state.topicsPath)
      context.commit('setTopics', response.data)
    },
    async fetchSectors (context) {
      let response = await axios.get(context.getters.hostname + context.state.sectorsPath)
      context.commit('setSectors', reponse.data)
    },
    async fetchTypes (context) {
      let response = await axios.get(context.getters.hostname + context.state.typesPath)
      let types = response.data
      types.forEach((type) => {
        if (type.name === 'Podcasts') {
          type.verb = 'Listen'
        } else if (type.name === 'Webinars') {
          type.verb = 'Watch'
        } else {
          type.verb = 'Read'
        }
      })
      context.commit('setTypes', types)
    },
    async fetchByQuery (context, args) {
      // if page has changed, make the query again as it was, only with page updated
      // if the query has changed reset page to 1
      context.commit('resetPage')
      let queryString = ''
      const query = args.query
      if (typeof query !== 'undefined') {
        let queryArray = []
        Object.keys(query).forEach((key) => {
          queryArray.push(key + '=' + query[key])
        })
        queryString += '?' + queryArray.join('&')
        if (typeof args.page !== 'undefined') {
          queryString += '&page=' + args.page
        }
      }
      var response = await axios.get(context.getters.hostname + args.path + queryString)
      return { data: response.data, totalPages: response.headers['x-wp-totalpages'], totalRecords: response.headers['x-wp-total'] }
    },
    async fetchOne ({dispatch, commit, getters, rootGetters}, args) {
      let response = await axios.get(rootGetters.hostname + args.path + '/' + args.id)
      return response.data
    }
}


export const getters = {
  hostname: state => {
      // if ((window.location.hostname.indexOf('localhost') > -1 || window.location.hostname.indexOf('.dev') > -1)) {
      //   return state.localHostname
      // } else {
      //   return state.remoteHostname
      // }

      return state.localHostname

    },
    bareHostname: state => {
      // if ((window.location.hostname.indexOf('localhost') > -1 || window.location.hostname.indexOf('.dev') > -1)) {
      //   return state.bareLocalHostname
      // } else {
      //   return state.bareRemoteHostname
      // }

      return state.bareLocalHostname
    },
    callouts: state => {
      return state.callouts
    },
    previousQuery: state => {
      return state.previousQuery
    },
    getTopicsIndexedById: (state) => {
      if (state.topics) {
        let topicsByIndex = {}

        state.topics.forEach((topic) => {
          topicsByIndex[topic.id] = topic
        })
        return topicsByIndex
      }
    },
    getTypesIndexedById: (state) => {
      if (state.types) {
        let typesByIndex = {}

        state.types.forEach((type) => {
          typesByIndex[type.id] = type
        })
        return typesByIndex
      }
    },
    getSectorsIndexedById: (state) => {
      if (state.sectors) {
        let sectorsByIndex = {}

        state.sectors.forEach((sector) => {
          sectorsByIndex[sector.id] = sector
        })
        return sectorsByIndex
      }
    }

}