import axios from "axios";

export const state = () => ({
  userProfile: null,
  categories: null,
  ctas: [],
  activeCtaIndex: 0,
  eventCategories: null,
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
  topics: null,
  types: null
});

export const mutations = {
  processTypeVerbs(state) {
    if (state.types && Array.isArray(state.types)) {
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
  async fetchGlobals({ commit, state }) {
    const data = await this.$axios.$get(
      "https://bigducknyc.com/wp-json/acf/v3/options/globals"
    );
    commit("setGlobals", data.acf);
    return data;
  },
  async fetchTopics({ commit, state }) {
    if (state.topics == null) {
      const data = await this.$axios.$get(
        "https://bigducknyc.com/wp-json/wp/v2/topic"
      );
      commit("setTopics", data);
      return data;
    } else {
      return null;
    }
  },
  async fetchSectors({ commit, state }) {
    if (state.topics == null) {
      const data = await this.$axios.$get(
        "https://bigducknyc.com/wp-json/wp/v2/sector"
      );
      commit("setSectors", data);
      return data;
    } else {
      return null;
    }
  },
  async fetchTypes({ state, commit }) {
    if (state.topics == null) {
      const data = await this.$axios.$get(
        "https://bigducknyc.com/wp-json/wp/v2/type"
      );
      commit("setTypes", data);
      return data;
    } else {
      return null;
    }
  },
  async fetchEventCategories({ commit, state }) {
    const data = await this.$axios.$get(
      "https://bigducknyc.com/wp-json/wp/v2/event_category"
    );
    commit("setEventCategories", data);
    return data;
  },
  async fetchCTAs({ commit }) {
    const data = await this.$axios.$get(
      "https://bigducknyc.com/wp-json/wp/v2/sidebarcta"
    );
    commit("setCTAs", data);
    return data;
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
  relatedInsightsPerPage: state => {
    return state.relatedInsightsPerPage;
  },
  previousQuery: state => {
    return state.previousQuery;
  },
  getTopicsIndexedById: state => {
    if (state.topics && Array.isArray(state.topics)) {
      let topicsByIndex = {};
      state.topics.forEach(topic => {
        topicsByIndex[topic.id] = topic;
      });
      return topicsByIndex;
    }
  },
  getTypesIndexedById: state => {
    if (state.types && Array.isArray(state.types)) {
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
