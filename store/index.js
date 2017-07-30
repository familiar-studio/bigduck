import axios from "axios";
if (process.BROWSER_BUILD) {
  var jscookie = require("js-cookie");
}
export const state = () => ({
  userProfile: null,
  localHostname: "https://wordpress.bigduck.dev/wp-json/",
  remoteHostname: "http://bigduck-wordpress.familiar.studio/wp-json/",
  backupImages: null,
  bareLocalHostname: "https://wordpress.bigduck.dev/",
  bareRemoteHostname: "http://bigduck-wordpress.familiar.studio/",
  categories: null,
  categoriesPath: "wp/v2/categories/",
  ctas: [],
  activeCtaIndex: 0,
  eventCategories: null,
  eventCategoriesPath: "wp/v2/event_category",
  footer: null,
  footerMeta: null,
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
    state.types.forEach(type => {
      if (type.name === "Podcasts") {
        type.verb = "Listen";
      } else if (type.name === "Webinars") {
        type.verb = "Watch";
      } else {
        type.verb = "Read";
      }
    });
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
  setFooter(state, data) {
    state.footer = data;
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
      if (jscookie) {
        const cookie = jscookie.get("cta-" + cta.id);
        console.log("cookie", cookie);
        if (cookie) {
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

    // create cookie for that cta
    if (jscookie) {
      jscookie.set("cta-" + cta.id, "true", {
        expires: 7
      });
    }
  },
  incrementCTA(state) {
    state.activeCtaIndex++;
    state.nextCTAFlag = false;
  },
  setFooterMeta(state, data) {
    state.footerMeta = data;
  },
  setBackupImages(state, data) {
    if (data && data.acf) {
      state.backupImages = {
        author: data.acf.backup_author_image,
        insights: data.acf.backup_insights_images
      };
    }
  }
};

export const actions = {
  async nuxtServerInit(context) {
    const loaded = await context.dispatch("loadAppInitNeed");
    context.commit("processTypeVerbs");
  },
  loadAppInitNeed({ dispatch }) {
    return Promise.all([
      dispatch("fetchFooter"),
      dispatch("fetchTopics"),
      dispatch("fetchTypes"),
      dispatch("fetchSectors"),
      dispatch("fetchEventCategories"),
      dispatch("fetchMenuCallouts")
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

  fetchMenuCallouts(context) {
    return axios
      .get(context.getters["hostname"] + "wp/v2/pages?slug=menu-callouts")
      .then(response => {
        context.commit("setMenuCallouts", response.data[0].acf);
        context.commit("setBackupImages", response.data[0]);
      });
  },
  fetchFooter(context) {
    return axios
      .get(context.getters.hostname + "acf/v3/options/globals")
      .then(response => {
        context.commit("setFooterMeta", response.data.acf);
      });
  },
  fetchTopics(context) {
    return axios
      .get(context.getters.hostname + context.state.topicsPath)
      .then(response => {
        context.commit("setTopics", response.data);
      });
  },
  fetchSectors(context) {
    return axios
      .get(context.getters.hostname + context.state.sectorsPath)
      .then(response => {
        context.commit("setSectors", response.data);
      });
  },
  fetchTypes(context) {
    return axios
      .get(context.getters.hostname + context.state.typesPath)
      .then(response => {
        context.commit("setTypes", response.data);
      });
  },
  fetchEventCategories(context) {
    return axios
      .get(context.getters.hostname + context.state.eventCategoriesPath)
      .then(response => {
        context.commit("setEventCategories", response.data);
      });
  },
  fetchByQuery(context, args) {
    // if page has changed, make the query again as it was, only with page updated
    // if the query has changed reset page to 1
    let queryString = "";
    const query = args.query;
    if (args.isPaged) {
      query["page"] = context.state.page;
      query["per_page"] = context.state.postsPerPage;
    }
    if (typeof query !== "undefined") {
      let queryArray = [];
      Object.keys(query).forEach(key => {
        queryArray.push(key + "=" + query[key]);
      });
      queryString += "?" + queryArray.join("&");
    }
    console.log(context.getters.hostname + args.path + queryString);
    return axios.get(context.getters.hostname + args.path + queryString);
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

    // return state.localHostname;
    return state.remoteHostname;
  },
  bareHostname: state => {
    // if ((window.location.hostname.indexOf('localhost') > -1 || window.location.hostname.indexOf('.dev') > -1)) {
    //   return state.bareLocalHostname
    // } else {
    // return state.bareRemoteHostname
    // }

    // return state.bareLocalHostname;
    return state.bareRemoteHostname;
    // return state.bareLocalHostname;
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
