<template lang="html">
  <div>
    <ais-index :search-store="searchStore" :query="queryString">
    <div class="search-bar">
      <div class="container">
      <ais-search-box class="form-inline" placeholder="Search"></ais-search-box>
      <!-- <ais-stats>
        <template slot-scope="{ totalResults, query }">
        Showing {{ totalResults }} results.
      </template>
      </ais-stats> -->
      </div>
    </div>
    <div class="row no-gutters">

      <div class="col-lg-3 col-xl-2">
        <div class="filter-bar menu">
          <div class="filter-list">
            <div class="media-list">
              <ais-refinement-list attribute-name="post_type_label" :sort-by="['name:asc']"></ais-refinement-list>
            </div>
            <div class="media-list">
              <div class="label label-lg">Topic</div>
              <ais-refinement-list attribute-name="taxonomies.topic" :sort-by="['name:asc']"></ais-refinement-list>
            </div>
            <div class="media-list">
              <div class="label label-lg">Type</div>
              <ais-refinement-list attribute-name="taxonomies.type" :sort-by="['name:asc']"></ais-refinement-list>
            </div>
          </div>

        </div>
      </div>
      <div class="col-lg-9 col-xl-8 search-main">
      <ul class="list-unstyled">
    <ais-results :results-per-page="20">
      <template slot-scope="{ result }">
        <li>
          <div class="search-result">
          <router-link v-if="result.post_type == 'bd_insight' && (selectedPostType === 'insights' || selectedPostType === null)" :to="{name: 'insights-slug', params: {slug: result.post_name}}" href="">
            <h6>Insight</h6>
            <h3><span class="underline-change hover-color" v-html="result.post_title"></span></h3>
            <div class="card-text" v-if="result._snippetResult.short_description.matchLevel === 'full'" v-html="result._snippetResult.short_description.value"></div>
            <div class="card-text" v-else-if="result._snippetResult.body.matchLevel === 'full'" v-html="result._snippetResult.body.value"></div>
            <div class="card-text" v-else v-html="result.short_description"></div>
          </router-link>
          <router-link v-else-if="result.post_type == 'bd_case_study' && (selectedPostType === 'work' || selectedPostType === null)" :to="{name: 'work-slug', params: {slug: result.post_name}}" href="">
            <h6>Work</h6>
            <h3><span class="underline-change hover-color" v-html="result.client_name"></span></h3>
            <div class="card-text" v-if="result._snippetResult.short_description.matchLevel === 'full'" v-html="result._snippetResult.short_description.value"></div>
            <div class="card-text" v-else-if="result._snippetResult.body.matchLevel === 'full'" v-html="result._snippetResult.body.value"></div>
            <div class="card-text" v-else v-html="result.post_title"></div>
          </router-link>
          <router-link v-else-if="result['post_type'] == 'bd_event' && (selectedPostType === 'events' || selectedPostType === null)" :to="{name: 'events-slug', params: {slug: result.post_name}}" href="">
            <h6>Event
              {{result.start_time}}
            </h6>
            <h3><span class="underline-change hover-color" v-html="result.post_title"></span></h3>
            <div class="card-text" v-html="result.subtitle"></div>
            <div class="card-text" v-if="result._snippetResult.text !== null && typeof result._snippetResult.text !== 'undefined'" v-html="result._snippetResult.text.value"></div>
          </router-link>
          <router-link v-else-if="result['post_type'] == 'bd_job' && (selectedPostType === 'jobs' || selectedPostType === null)" :to="{name: 'about', params: {slug: result.post_name}}" href="">
            <h6>Job</h6>
            <h3><span class="underline-change hover-color" v-html="result.post_title"></span></h3>
            <div class="card-text" v-if="result._snippetResult.job_description.matchLevel === 'full'" v-html="result._snippetResult.job_description.matchLevel.value"></div>
            <div class="card-text" v-else-if="result._snippetResult.requirements_body.matchLevel === 'full'" v-html="result._snippetResult.requirements_body.value"></div>
            <div class="card-text" v-else v-html="result.job_description"></div>
          </router-link>
          <router-link v-else-if="result['post_type'] == 'bd_service' && (selectedPostType === 'services' || selectedPostType === null)" :to="{name: 'services-slug', params: {slug: result.post_name}}">
            <h6>Service</h6>
            <h3><span class="underline-change hover-color" v-html="result.post_title"></span></h3>
            <div class="card-text" v-if="result._snippetResult.body.matchLevel === 'full'" v-html="result._snippetResult.body.value"></div>
            <div class="card-text" v-else-if="result._snippetResult.introduction.matchLevel === 'full'" v-html="result._snippetResult.introduction.value"></div>
            <div class="card-text" v-else-if="result._snippetResult.short_description.matchLevel === 'full'" v-html="result._snippetResult.short_description.value"></div>
            <div class="card-text" v-else v-html="result.short_description"></div>

          </router-link>
        </div>
        </li>
      </template>

    </ais-results>
    <ais-no-results></ais-no-results>
    <ais-pagination @page-change="onPageChange"></ais-pagination>
  </ul>
</div>
<div class="col-lg-2"></div>
  </div>
  </ais-index>

  </div>
</template>

<script>
import FilterList from "~/components/FilterList";
import { createFromAlgoliaCredentials, createFromSerialized } from 'vue-instantsearch'
const searchStore = createFromAlgoliaCredentials('R3PZL8WP9J',
  '65ae7f31389bada450fa79a104e7fc9f')
searchStore.indexName = "wp_searchable_posts"

export default {
  components: {
    FilterList
  },
  data() {
    return {
      totalPages: null,
      query: null,
      searchStore: null,
      postTypes: [
        { name: 'Insights', id: 'insights'},
        { name: 'Work', id: 'work' },
        { name: 'Events', id: 'events' },
        { name: 'Jobs', id: 'jobs' },
        { name: 'Services', id: 'services' }
      ],
      selectedPostType: null
    };
  },
  methods: {
    togglePostType(e) {
      this.selectedPostType = this.selectedPostType === e.id ? null : e.id
    },
    onPageChange() {
      window.scrollTo(0,0);
    }
  },
  head() {
    return {
      title: "Search",
      meta: [
        ...this.$metaDescription("Find anything on our site."),
        ...this.$metaTitles("Search"),
        ...this.$metaImages()
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
  },
  computed: {
    queryString () {
      return this.$route.query.query;
    }
  }
};
</script>
<style>
  .ais-refinement-list__checkbox,
  .ais-refinement-list__count,
  .ais-refinement-list__checkbox[value="Pages"] + .ais-refinement-list__value,
  .ais-refinement-list__checkbox[value="Sidebar CTAs"] + .ais-refinement-list__value,
  .ais-refinement-list__checkbox[value="Emails"] + .ais-refinement-list__value,
  .ais-refinement-list__checkbox[value="Posts"] + .ais-refinement-list__value,
  .ais-pagination__item.ais-pagination__item--disabled {
    display: none;
  }
</style>
