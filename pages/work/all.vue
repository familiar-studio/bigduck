<template>
  <div class="">
    <div class="row no-gutters">
      <div class="col-lg-3 col-xl-2 hidden-md-down">
        <div v-if="topics" class="filter-bar menu">
          <FilterList label="Topics" taxonomy="topic" :terms="topics" :selected="selectedTopic" v-on:clicked="toggleTaxonomy($event)"></FilterList>
          <a v-if="selectedTopic" href="#" @click.prevent="resetFilters" class="btn btn-primary">Clear All</a>
        </div>
      </div>
      <div class='col-lg-9 col-xl-8'>

        <div class="container" id="content">
          <div v-if="work">
            <Work :work="work" v-if="work.length > 0"></Work>
            <div v-else>
              <span v-if="selectedTopic">There is currently no work in {{getTopicsIndexedById[selectedTopic].name}}</span>
            </div>
          </div>
        </div>

      </div>
      <div class="col-lg-2">
        <Chat></Chat>
      </div>
    </div>
  </div>
</template>

<script>
import Work from "~/components/Work.vue";
import FilterList from "~/components/FilterList.vue";
import Chat from "~/components/Chat.vue";

import { mapState, mapGetters } from "vuex";

export default {
  name: "work",
  data() {
    return {
      work: null,
      selected: {
        topic: null,
        sector: null
      },
      page: 1
    };
  },
  components: {
    Work,
    FilterList,
    Chat
  },
  computed: {
    ...mapState(["topics", "sectors"]),
    ...mapGetters(["getTopicsIndexedById"]),
    selectedTopic() {
      return this.$route.query.topic;
    }
  },
  watch: {
    "$route.query": "filterResults"
  },
  async asyncData({ app, store, query }) {
    let params = { page: 1, per_page: 8 };
    if (query.topic) {
      params.topic = query.topic
    }
    const response  = await app.$axios.$get("/wp/v2/bd_case_study", {
      params: params
    })
    return {
      work: response
    };
  },
  head() {
    if (this.work[0]) {
      return {
        title: "Work - All Projects",
        meta: [
          ...this.$metaDescription("All Projects"),
          ...this.$metaTitles("Work - All Projects | Big Duck"),
          ...this.$metaImages()
        ]
      };
    }
  },
  methods: {
    toggleTaxonomy(event) {
      // make a copy of the curren tquery string
      let query = Object.assign({}, this.$route.query);

      // toggle filters
      if (parseInt(query[event.taxonomy]) === event.id) {
        delete query[event.taxonomy];
      } else {
        query[event.taxonomy] = event.id;
      }
      this.$router.push({ name: "work-all", query: query });
    },
    resetFilters() {
      this.$router.push({ name: "work-all", query: null });
    },
    async filterResults() {
      this.page = 1
      const response = await this.$axios.$get("/wp/v2/bd_case_study", {
        params: this.$route.query
      })
      this.insights = response;
    }
  }
};
</script>
