<template lang="html">
  <div>
    <div class="search-bar">
      <div class="container">
        <form @submit.prevent="search()" class="form-inline">
          <input type="text" placeholder="Search" v-model="query" class="form-control form-control-lg"/>
          <button type="submit" class="btn btn-secondary">
            <svg width="26px" height="26px" viewBox="0 0 26 26" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" id="search-icon">
              <path d="M25.5611068,23.4388932 C26.146029,24.0268104 26.146029,24.9731896 25.5574713,25.5647236 C25.2731412,25.8461524 24.8928615,26 24.5,26 C24.1053281,26 23.7235341,25.8447477 23.4388932,25.5601068 L15.0643004,17.185514 C13.4604395,18.3533221 11.5289078,19 9.5,19 C4.26071525,19 0,14.7392847 0,9.5 C0,4.26071525 4.26071525,0 9.5,0 C14.7392847,0 19,4.26071525 19,9.5 C19,11.528581 18.353522,13.4600566 17.1863031,15.0640896 L25.5611068,23.4388932 Z M3,9.5 C3,13.0837153 5.91628475,16 9.5,16 C13.0837153,16 16,13.0837153 16,9.5 C16,5.91628475 13.0837153,3 9.5,3 C5.91628475,3 3,5.91628475 3,9.5 Z"></path>
            </svg>
          </button>
        </form>
      </div>
    </div>
    <div class="container">
      <ul v-if="results && results.length > 0" class="list-unstyled">
        <li v-for="result in results">
          <div class="search-result">
            <router-link v-if="result['post-type'] == 'bd_insight'" :to="{name: 'insights-slug', params: {slug: result.slug}}" href="">
              <h6>Insight</h6>
              <h3><span class="underline-change hover-color" v-html="result.title.rendered"></span></h3>
              <div class="card-text" v-html="result.acf.short_description"></div>
            </router-link>
            <router-link v-else-if="result['post-type'] == 'bd_case_study'" :to="{name: 'work-slug', params: {slug: result.slug}}" href="">
              <h6>Work</h6>
              <h3><span class="underline-change hover-color" v-html="result.acf.client_name"></span></h3>
              <div class="card-text" v-html="result.title.rendered"></div>
            </router-link>
            <router-link v-else-if="result['type'] == 'page'" :to="{ path: result.slug}" href="">
              <h6>Page</h6>
              <h3><span class="underline-change hover-color" v-html="result.title.rendered"></span></h3>
              <div class="card-text" v-html="result.acf.subtitle"></div>
            </router-link>
            <router-link v-else-if="result['type'] == 'bd_event'" :to="{name: 'events-slug', params: {slug: result.slug}}" href="">
              <h6>Event {{result.acf.start_time}}</h6>
              <h3><span class="underline-change hover-color" v-html="result.title.rendered"></span></h3>
              <div class="card-text" v-html="result.acf.subtitle"></div>
            </router-link>
            <router-link v-else-if="result['type'] == 'bd_service'" :to="{name: 'services-slug', params: {slug: result.slug}}" href="">
              <h6>Service</h6>
              <h3><span class="underline-change hover-color" v-html="result.title.rendered"></span></h3>
              <div class="card-text" v-html="result.acf.short_description"></div>
            </router-link>
            <router-link v-else :to="{name: 'insights-slug', params: {slug: result.slug}}" href="">
              <h6>Insight</h6>
              <h3><span class="underline-change hover-color" v-html="result.title.rendered"></span></h3>
              <div class="card-text" v-html="result.acf.short_description"></div>
            </router-link>

            <!-- <hr/> -->
          </div>
        </li>
      </ul>

      <h3 v-else class="mt-5">We couldn’t find any search results for &ldquo;{{query}}.&rdquo;</h3>

    </div>
  </div>
</template>

<script>
import Axios from "axios"; import WPAPI from 'wpapi'; let wp = new WPAPI({ endpoint: '//bigducknyc.com/wp-json'});

export default {
  data() {
    return {
      totalPages: null,
      query: null,
      results: null
    };
  },
  head() {
    return {
      title: "Search",
      meta: [
        {
          hid: "og:title",
          content: "Search"
        },
        {
          hid: "twitter:title",
          content: "Search"
        },
        {
          hid: "description",
          content: "Find anything on our site."
        },
        {
          hid: "og:description",
          content: "Find anything on our site."
        },
        {
          hid: "twitter:description",
          content: "Find anything on our site."
        }
      ]
    };
  },
  async asyncData({ route, store }) {
    // let response = await Axios.get(store.getters.hostname + 'wp/v2/bd_insight?filter[s]=' + route.query.query)
    // return {
    //   results: response.data
    // }
  },
  mounted() {
    this.query = this.$route.query.query;
    this.search();
  },
  methods: {
    async search() {
      ///wp-json/wp/v2/multiple-post-type?search=awesome&type[]=post&type[]=page&type[]=article
      let results = await Axios.get(
        this.$store.getters.hostname + "wp/v2/multiple-post-type",
        {
          params: {
            search: this.query,
            type: ["bd_insight", "bd_service", "bd_case_study, bd_event"]
          }
        }
      );
      this.results = results.data;
      this.results.forEach(result => {
        result["post-type"] = result.guid.rendered
          .split("?post_type=")[1]
          .split("&")[0];
      });
    }
  }
};
</script>
