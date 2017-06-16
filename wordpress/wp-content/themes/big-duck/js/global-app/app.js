import axios from 'axios'
import Vue from 'vue'
import VueRouter from 'vue-router'
import Vuex from 'vuex'
import MainComponent from './main-component.vue'
// import store from './store.js'

// Vue.use(VueRouter)
Vue.use(Vuex)

export const globalApp = new Vue({
  el: "#global-app",
  delimiters: ["${", "}"],
  // router,
  // store,
  components: { 'main-component': MainComponent },
  data: {
    msg: "Hello Wordpress",
    acf: {}
  },
  created: function(){
    const id = 46;

    axios.get('/wp-json/acf/v2/post/' + id)
    .then((response) => {
      this.acf = response.data.acf;
      // store.dispatch(("setACF"), response.data)
    })
    .catch((error) => {
      console.log("error");
      console.log(error);
    })
  }

})
