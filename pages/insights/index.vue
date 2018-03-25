<template>
  <div class="">
    <div class="row no-gutters">
      <div class="col-lg-3 col-xl-2">
        <div v-if="types && topics" class="filter-bar menu">
          <FilterList label="Topics" taxonomy="topic" :terms="topics" :selected="selectedTopic" v-on:clicked="toggleTaxonomy($event)"></FilterList>
          <FilterList label="Types" taxonomy="type" :terms="types" :selected="selectedType" v-on:clicked="toggleTaxonomy($event)"></FilterList>
          <a v-if="selectedTopic || selectedType" href="#" @click.prevent="resetFilters" class="btn btn-primary">Clear All</a>
        </div>
      </div>
      <div class="col-xl-8 col-lg-9">
        <div class="container" id="content">
          <h1>Insights</h1>
          <div v-if="insights && insights.length > 0">

            <div v-for="(insight, index) in insights" :key="insight.slug">
              <Post :entry="insight" :index="index"></Post>

              <InlineCallout class="mb-5" v-if="index % 5 == 1 && index < insights.length - 1" :callout="callout"></InlineCallout>

            </div>

            <div class="pager" v-if="insights.length < totalRecords">
              <a class="btn btn-primary my-4" href="#" @click.prevent="nextPage">Load more</a>
            </div>
          </div>
          <div v-else>
            <div class="">
              There are currently no
              <span v-if="selectedTopic">{{getTopicsIndexedById[selectedTopic].name}}</span>
              <span v-if="selectedTopic && selectedType"> or </span>
              <span v-if="selectedType">{{getTypesIndexedById[selectedType].name}}</span> insights.
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-2">
        <Chat></Chat>
      </div>
    </div>
  </div>
</template>
<script>
import Chat from "~/components/Chat.vue";
import FilterList from "~/components/FilterList.vue";
import InlineCallout from "~/components/InlineCallout.vue";
import { mapState, mapGetters } from "vuex";
import Post from "~/components/Post.vue";
export default {
  name: "insights",
  async asyncData({ app, store, query, errro }) {
    let params = {page: 1, per_page: 8};
    ['topic', 'type', 'slug', 'event_category'].map((s) => {
      if (query[s]) {
        params[s] = query[s]
      }
    })
    const response = await app.$axios.get("/wp/v2/bd_insight", {
      params: params
    })
    return {
      insights: response.data,
      totalPages: response.headers["x-wp-totalpages"],
      totalRecords: response.headers["x-wp-total"]
    };
  },
  async beforeRouteUpdate(to, from, next) {
    let params = {page: this.page, per_page: 8};

    const response = await this.$axios.get("/wp/v2/bd_insight", {
      params: to.query
    })

    this.totalPages = response.headers["x-wp-totalpages"];
    this.totalRecords = response.headers["x-wp-total"];
    if(response.data.length > 0){
      response.data.map((e, i) => this.$set(this.insights, i, e))
    } else {
      this.insights.splice(0)
    }
    next()
  },
  data() {
    return {
      previouslyLoadedInsights: 0,
      callout: null,
      insights: [],
      page: 1,
      postsPerPage: 8
    };
  },
  head() {
    if (this.insights) {
      return {
        title: "Insights",
        meta: [
          ...this.$metaDescription("Read more about the results of our work."),
          ...this.$metaTitles("Insights"),
          ...this.$metaImages()
        ]
      };
    }
  },
  components: {
    Chat,
    FilterList,
    InlineCallout,
    Post
  },
  computed: {
    ...mapState(["types", "topics"]),
    ...mapGetters(["getTopicsIndexedById", "getTypesIndexedById"]),
    selectedType() {
      return this.$route.query.type;
    },
    selectedTopic() {
      return this.$route.query.topic;
    }
  },

  methods: {
    toggleTaxonomy(event) {
      // make a copy of the current query string
      let query = Object.assign({}, this.$route.query);
      // toggle filters
      if (parseInt(query[event.taxonomy]) === event.id) {
        delete query[event.taxonomy];
      } else {
        query[event.taxonomy] = event.id;
      }
      this.$router.push({ name: "insights", query: query });
    },
    resetFilters() {
      this.$router.push({ name: "insights", query: null });
    },
    // async filterResults() {
    //   let params = {page: this.page, per_page: 8};
    //   ['topic', 'type', 'slug', 'event_category'].map((s) => {
    //     if (query[s]) {
    //       params[s] = query[s]
    //     }
    //   })
    //   const response = app.$axios.get("/wp/v2/bd_insight", {
    //     params: params
    //   })
    //   this.insights = response.data;
    //   this.totalPages = response.headers["x-wp-totalpages"];
    //   this.totalRecords = response.headers["x-wp-total"];
    // },
    async nextPage() {
      this.page++
      this.previouslyLoadedInsights = this.insights.length;
      let params = {page: this.page, per_page: 8};
      ['topic', 'type', 'slug', 'event_category'].map((s) => {
        if (query[s]) {
          params[s] = query[s]
        }
      })
      const response = app.$axios.get("/wp/v2/bd_insight", {
        params: params
      })
      this.insights = this.insights.concat(response.data);
    }
  },
  async created() {
    let response = await this.$axios.$get("/wp/v2/pages?slug=insights");
    var data = response[0];
    this.callout = data.acf;
  }
};
</script>
