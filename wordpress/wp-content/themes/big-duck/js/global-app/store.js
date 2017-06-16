import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export const store = new Vuex.store({
  state: {
    msg: "Hello from the store."
  },
  actions: {
    loadACF: function() {
      state.commit('saveACF', data);
    }
  },
  mutations: {
    saveACF: function(data) {
      state.acf = data
    }
  }
})
