<template lang="html">
  <div>
    <div class="search-bar">
      <div class="container">
      <ais-index :search-store="searchStore" >
      <ais-search-box class="form-inline" autocomplete="on"></ais-search-box>
      <ais-results>
        <template slot-scope="{ result }">
          <div class="search-result">
            <router-link v-if="result.post_type == 'bd_insight'" :to="{name: 'insights-slug', params: {slug: result.slug}}" href="">
              <h6>Insight</h6>
              <h3><span class="underline-change hover-color" v-html="result.post_title"></span></h3>
              <div class="card-text" v-html="result.short_description"></div>
              <h4 v-html="result.body"></h4>
            </router-link>
            <router-link v-else-if="result.post_type == 'bd_case_study'" :to="{name: 'work-slug', params: {slug: result.slug}}" href="">
              <h6>Work</h6>
              <h3><span class="underline-change hover-color" v-html="result.client_name"></span></h3>
              <div class="card-text" v-html="result.post_title"></div>
            </router-link>
            <router-link v-else-if="result['type'] == 'page'" :to="{ path: result.slug}" href="">
              <h6>Page</h6>
              <h3><span class="underline-change hover-color" v-html="result.post_title"></span></h3>
              <div class="card-text" v-html="result.subtitle"></div>
            </router-link>
            <router-link v-else-if="result['post_type'] == 'bd_event'" :to="{name: 'events-slug', params: {slug: result.slug}}" href="">
              <h6>Event
                {{result.start_time}}
              </h6>
              <h3><span class="underline-change hover-color" v-html="result.post_title"></span></h3>
              <div class="card-text" v-html="result.subtitle"></div>
            </router-link>



            <!-- <hr/> -->
          </div>
        </template>
      </ais-results>
    </ais-index>
      </div>
    </div>
  </div>
</template>

<script>
import Axios from "axios";
import { createFromAlgoliaCredentials, createFromSerialized } from 'vue-instantsearch'
const searchStore = createFromAlgoliaCredentials('R3PZL8WP9J',
  '65ae7f31389bada450fa79a104e7fc9f')
searchStore.indexName = "wp_searchable_posts"

export default {
  data() {
    return {
      totalPages: null,
      query: null,
      searchStore: null
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
    searchStore.start()
    searchStore.refresh()
    await searchStore.waitUntilInSync()

    return { serializedSearchStore: searchStore.serialize()}
  },
  created () {
    this.searchStore = createFromSerialized(this.serializedSearchStore)
  }
};
</script>
