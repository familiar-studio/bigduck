import axios from "axios";

export const state = () => ({
  userProfile: null,
  localHostname: "http://bigduck.test/wp-json/",
  remoteHostname: "https://bigducknyc.com/wp-json/",
  bareLocalHostname: "http://bigduck.test/",
  bareRemoteHostname: "https://bigducknyc.com/",
  categories: null,
  categoriesPath: "wp/v2/categories/",
  ctas: [],
  activeCtaIndex: 0,
  eventCategories: null,
  eventCategoriesPath: "wp/v2/event_category",
  globals: null,
  form: null,
  menuCallouts: null,
  page: 1,
  nextCTAFlag: false,
  postsPerPage: 8,
  previousQuery: "",
  query: {},
  relatedInsightsPerPage: 2,
  sectors: null,
  sectorsPath: "wp/v2/sector",
  topics: null,
  topicsPath: "wp/v2/topic",
  types: null,
  typesPath: "wp/v2/type"
});

export const mutations = {
  processTypeVerbs(state) {
    if (state.types) {
      state.types.forEach(type => {
        if (type.name === "Podcasts") {
          type.verb = "Listen";
        } else if (type.name === "Webinars") {
          type.verb = "Watch";
        } else {
          type.verb = "Read";
        }
      });
    }
  },
  nextPage(state) {
    state.page += 1;
  },
  resetPage(state) {
    state.page = 1;
  },
  setFilterQuery(state, data) {
    state.query = data;
  },
  setCallouts(state, data) {
    state.callouts = data;
  },
  setMenuCallouts(state, data) {
    state.menuCallouts = data;
  },

  setEventCategories(state, data) {
    state.eventCategories = data;
  },
  setSectors(state, data) {
    state.sectors = data;
  },
  setTopics(state, data) {
    state.topics = data;
  },
  setTypes(state, data) {
    state.types = data;
  },
  setQueryString(state, data) {
    state.queryString = data;
  },
  setEvents(state, data) {
    state.events = data;
  },
  updateProfile(state, data) {
    state.userProfile = data;
  },
  setCTAs(state, ctas) {
    //state.chat = "something";
    // need to add check for cookies here to move to next one if already fileld out
    const filteredCtas = ctas.filter(cta => {
      if (localStorage) {
        const storage = localStorage["cta-" + cta.id];

        if (storage) {
          return false;
        }
      }
      return true;
    });

    state.ctas = filteredCtas;
    state.activeCtaIndex = 0;
  },

  nextCTA(state, cta) {
    state.nextCTAFlag = true;

    // create localstorage for that cta
    if (localStorage) {
      localStorage["cta-" + cta.id] = "true";
    }
  },
  incrementCTA(state) {
    state.activeCtaIndex++;
    state.nextCTAFlag = false;
  },
  setGlobals(state, data) {
    state.globals = data;
  }
};

export const actions = {
  async nuxtServerInit(context) {
    const loaded = await context.dispatch("loadAppInitNeed");
    context.commit("processTypeVerbs");
  },
  loadAppInitNeed({ dispatch }) {
    return Promise.all([
      dispatch("fetchGlobals"),
      dispatch("fetchTopics"),
      dispatch("fetchTypes"),
      dispatch("fetchSectors"),
      dispatch("fetchEventCategories")
    ]);
  },
  formInjection(context, body) {
    return false;
  },
  error(context, error) {
    console.warn(error);
  },
  fetch(context, path) {
    return axios.get(context.getters["hostname"] + path);
  },

  fetchGlobals(context) {
    return axios
      .get(context.getters.hostname + "acf/v3/options/globals")
      .then(response => {
        context.commit("setGlobals", response.data.acf);
      });
  },
  fetchTopics(context) {
    if (context.state.topics == null) {
      return axios
        .get(context.getters.hostname + context.state.topicsPath)
        .then(response => {
          context.commit("setTopics", response.data);
        });
    } else {
      return null;
    }
  },
  fetchSectors(context) {
    if (context.state.topics == null) {
      return axios
        .get(context.getters.hostname + context.state.sectorsPath)
        .then(response => {
          context.commit("setSectors", response.data);
        });
    } else {
      return null;
    }
  },
  fetchTypes(context) {
    if (context.state.topics == null) {
      return axios
        .get(context.getters.hostname + context.state.typesPath)
        .then(response => {
          context.commit("setTypes", response.data);
        });
    } else {
      return null;
    }
  },
  fetchEventCategories(context) {
    return axios
      .get(context.getters.hostname + context.state.eventCategoriesPath)
      .then(response => {
        context.commit("setEventCategories", response.data);
      });
  },
  fetchByQuery(context, { query, path, isPaged }) {
    // if page has changed, make the query again as it was, only with page updated
    // if the query has changed reset page to 1
    let queryString = "";
    let params = {};
    if (isPaged) {
      params.page = context.state.page;
      params.per_page = context.state.postsPerPage;
    }

    if (query.topic) {
      params.topic = query.topic;
    }
    if (query.type) {
      params.type = query.type;
    }

    if (query.slug) {
      params.slug = query.slug;
    }
    if (query.event_category) {
      params.event_category = query.event_category;
    }

    //return this.$axios.$get(args.path + queryString);
    //return axios.get(context.getters.hostname + args.path + queryString);
    return axios.get(context.getters.hostname + path, { params: params });
  },
  async fetchOne({ dispatch, commit, getters, rootGetters }, args) {
    let response = await axios.get(
      rootGetters.hostname + args.path + "/" + args.slug
    );
    return response.data;
  },
  async fetchCTAs({ rootGetters, commit }) {
    let response = await axios.get(rootGetters.hostname + "wp/v2/sidebarcta");
    if (response.data && response.data[0]) {
      commit("setCTAs", response.data);
    }
  }
};

export const getters = {
  activeCta: state => {
    if (state.ctas.length > state.activeCtaIndex) {
      return state.ctas[state.activeCtaIndex];
    } else {
      return null;
    }
  },
  hostname: state => {
    // if ((window.location.hostname.indexOf('localhost') > -1 || window.location.hostname.indexOf('.dev') > -1)) {
    //   return state.localHostname
    // } else {
    //   return state.remoteHostname
    // }

    //return state.localHostname;
    return state.remoteHostname;
  },
  bareHostname: state => {
    // if ((window.location.hostname.indexOf('localhost') > -1 || window.location.hostname.indexOf('.dev') > -1)) {
    //   return state.bareLocalHostname
    // } else {
    // return state.bareRemoteHostname
    // }

    //return state.bareLocalHostname;
    return state.bareRemoteHostname;
  },
  relatedInsightsPerPage: state => {
    return state.relatedInsightsPerPage;
  },
  previousQuery: state => {
    return state.previousQuery;
  },
  getTopicsIndexedById: state => {
    if (state.topics) {
      let topicsByIndex = {};
      state.topics.forEach(topic => {
        topicsByIndex[topic.id] = topic;
      });
      return topicsByIndex;
    }
  },
  getTypesIndexedById: state => {
    if (state.types) {
      let typesByIndex = {};

      state.types.forEach(type => {
        typesByIndex[type.id] = type;
      });
      return typesByIndex;
    }
  },
  getSectorsIndexedById: state => {
    if (state.sectors) {
      let sectorsByIndex = {};

      state.sectors.forEach(sector => {
        sectorsByIndex[sector.id] = sector;
      });
      return sectorsByIndex;
    }
  },
  getEventCategoriesIndexedById: state => {
    if (state.eventCategories) {
      let eventCategoriesByIndex = {};

      state.eventCategories.forEach(eventCategory => {
        eventCategoriesByIndex[eventCategory.id] = eventCategory;
      });
      return eventCategoriesByIndex;
    }
  }
};
