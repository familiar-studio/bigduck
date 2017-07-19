import axios from "axios";

export const state = () => ({
  userProfile: null,
  localHostname: "https://wordpress.bigduck.dev/wp-json/",
  remoteHostname: "http://bigduck-wordpress.familiar.studio/wp-json/",
  backupImages: null,
  bareLocalHostname: "https://wordpress.bigduck.dev",
  bareRemoteHostname: "http://bigduck-wordpress.familiar.studio",
  callouts: null,
  categories: null,
  categoriesPath: "wp/v2/categories/",
  chat: [],
  activeChat: null,
  eventCategories: null,
  eventCategoriesPath: "wp/v2/event_category",
  footer: null,
  footerMeta: null,
  form: null,
  inline: [],
  activeInline: null,
  menuCallouts: null,
  page: 1,
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
  setAllActiveCallouts(state, callouts) {
    //state.chat = "something";
    state.chat = [];
    state.inline = [];
    console.log(callouts);
    callouts.forEach(callout => {
      console.log(callout.acf.placement);
      state[callout.acf.placement].push(callout);
    });
    // need to add check for cookies here to move to next one if already fileld out

    if (state.chat.length > 0) {
      state.activeChat = state.chat[0];
    }

    if (state.inline.length > 0) {
      state.activeInline = state.inline[0];
    }
  },
  setActiveCallout(state, data) {
    var parser = new DOMParser();
    var doc = parser.parseFromString(data.html, "text/html");

    var callout = {};
    var forms = doc.getElementsByTagName("form");
    var titles = doc.getElementsByClassName("gform_title");
    var descriptions = doc.getElementsByClassName("gform_description");

    if (forms.length > 0) {
      var form = forms[0];
      callout.formId = Number(form.id.split("_")[1]);
    }

    if (titles.length > 0) {
      callout.title = titles[0].innerHTML;
    }
    if (descriptions.length > 0) {
      callout.description = descriptions[0].innerHTML;
    }

    callout.title = "default cta";
    callout.description = "this is the regular text that loads in here";

    state[data.slug] = callout;
  },
  setFooterMeta(state, data) {
    state.footerMeta = data;
  },
  setBackupImages(state, data) {
    state.backupImages = {
      author: data.acf.backup_author_image,
      insights: data.acf.backup_insights_images
    };
  }
};

export const actions = {
  async nuxtServerInit(context) {
    const loaded = await context.dispatch("loadAppInitNeed");
    context.commit("processTypeVerbs");
  },
  loadAppInitNeed({ dispatch }) {
    return Promise.all([
      dispatch("fetchCallouts"),
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
  fetchCallouts(context) {
    return axios
      .get(context.getters.hostname + "wp/v2/bd_callout")
      .then(response => {
        context.commit("setCallouts", response.data);
      });
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
      .get(
        context.getters.bareHostname + "/wp-json/familiar/v1/sidebars/footer"
      )
      .then(response => {
        context.commit("setFooter", response.data);
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
  async fetchPageCallouts({ rootGetters, commit }) {
    let response = await axios.get(rootGetters.hostname + "wp/v2/sidebarcta");
    if (response.data && response.data[0]) {
      console.log("widgets", response.data);
      commit("setAllActiveCallouts", response.data);
    }
  },
  async fetchFooterMeta({ rootGetters, commit }) {
    let response = await axios.get(
      rootGetters.hostname + "wp/v2/pages?slug=footer"
    );
    commit("setFooterMeta", response.data[0].acf);
  }
};

export const getters = {
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
