<template>
  <div class="">
    <div class="row no-gutters">
      <div class="col-lg-3 col-xl-2">
        <div v-if="topics && eventCategories" class="filter-bar menu">
          <FilterList label="Topics" taxonomy="topic" :terms="topics" :selected="selectedTopic" v-on:clicked="toggleTaxonomy($event)"></FilterList>
          <FilterList label="Types" taxonomy="event_category" :terms="eventCategories" :selected="selectedCategory" v-on:clicked="toggleTaxonomy($event)"></FilterList>
          <a v-if="selectedCategory || selectedTopic" href="#" @click.prevent="resetFilters" class="btn btn-primary">Clear All</a>
        </div>
      </div>
      <div class="col-lg-9 col-xl-8">
        <div class="container">
          <div id="content">
            <div class="page-title">
              <h1>Upcoming Events</h1>

              <div class="media lead">
                <img src="/svgs/speaking-icon.svg" class="d-flex mr-2" />
                <div class="media-body">
                  <router-link :to="{name: 'events-speaking'}">

                    Interested in having Big Duck speak at your organization?
                    <br/>
                    <span class="underline-change-thick hover-color"> Learn more about our talks…</span>
                  </router-link>

                </div>
              </div>
            </div>
            <div v-if="events && events.length > 0">
              <div v-for="(event, index) in events" :key="event.id">
                <Event :entry="event" :index="index"></Event>

                <InlineCallout class="mb-5" v-if="index % 5 == 1 && index < events.length - 1" :callout="callout"></InlineCallout>

              </div>

              <div class="pager" v-if="events.length < totalRecords">
                <a class="btn btn-primary my-4" href="#" @click.prevent="nextPage">Load more</a>
              </div>
            </div>
            <div v-else>
              There are currently no upcoming
              <span v-if="selectedTopic">{{getTopicsIndexedById[selectedTopic].name}}</span>
              <span v-if="selectedTopic && selectedCategory"> or </span>
              <span v-if="selectedCategory">{{getEventCategoriesIndexedById[selectedCategory].name}}</span> events.
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
import Event from "~/components/Event.vue";
import FilterList from "~/components/FilterList.vue";
import { mapState, mapGetters } from "vuex";
import InlineCallout from "~/components/InlineCallout.vue";
import Chat from "~/components/Chat.vue";

export default {
  name: "events",
  components: {
    Event,
    FilterList,
    InlineCallout,
    Chat
  },
  head() {
    if (this.events) {
      return {
        title: "Upcoming Events",
        meta: [
          {
            hid: "og:title",
            property: "og:title",
            content: "Upcoming Events"
          },
          {
            hid: "twitter:title",
            property: "twitter:title",
            content: "Upcoming Events"
          },
          {
            hid: "description",
            name: "description",
            content: "Learn more about our upcoming events."
          },
          {
            hid: "og:description",
            property: "og:description",
            content: "Learn more about our upcoming events."
          },
          {
            hid: "twitter:description",
            property: "twitter:description",
            content: "Learn more about our upcoming events."
          }
        ]
      };
    }
  },
  data() {
    return {
      previouslyLoadedEvents: 0,
      callout: null,
      page: 1,
      postsPerPage: 8,
      query: {}
    };
  },
  computed: {
    ...mapState(["categories", "eventCategories", "topics", "callouts"]),
    ...mapGetters([
      "getTopicsIndexedById",
      "getEventCategoriesIndexedById",
      "hostname"
    ]),
    selectedCategory() {
      return this.$route.query.event_category;
    },
    selectedTopic() {
      return this.$route.query.topic;
    }
  },
  async asyncData ({app, query}) {
    let params = {page: 1, per_page: 8};
    // if a category is not being filtered, its key should not be present in the params
    ['topic', 'type', 'slug', 'event_category'].map((s) => {
      if (query[s]) {
        params[s] = query[s]
      }
    })
    const response = await app.$axios.get("/wp/v2/bd_event", {
      params: params
    })
    return {
      events: response.data,
      totalPages: response.headers["x-wp-totalpages"],
      totalRecords: response.headers["x-wp-total"]
    }
 },
  async beforeRouteUpdate( to, from, next ){
      this.page = 1
      let params = {page: this.page, per_page: this.postsPerPage}
      let query = Object.assign({}, to.query);
      ['topic', 'type', 'slug', 'event_category'].map((s) => {
        if (query[s]) {
          params[s] = query[s]
        }
      })
      const response = await this.$axios.get("/wp/v2/bd_event", {
        params: params
      })
      console.log('events before new query: ', this.events.length)
      this.events = response.data
      console.log('events after new query: ', this.events.length)
      console.log('firing beforeRouteUpdate')
      console.log(this.events.length)
      this.totalPages = response.headers["x-wp-totalpages"];
      this.totalRecords = response.headers["x-wp-total"];

      // if `next()` is called, the query string updates correctly, as do the current filters' highlights, which depend on it
      // if `next()` is not called, the correct events are listed but the current filters are incorrect
      next()
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
      this.query = query
      this.$router.push({ query: query });
      // this.filterResults()
    },
    resetFilters() {
      this.$router.push({ name: "events", query: null });
    },
    async nextPage() {
      this.page++
      this.previouslyLoadedEvents = this.events.length;
      let query = Object.assign({}, this.$route.query);
      const response = await this.$axios.$get("/wp/v2/bd_event", {
        params: {
          page: this.page,
          per_page: this.postsPerPage,
          topic: query.topic,
          type: query.type,
          slug: query.slug,
          event_category: query.event_category
        }
      })
      this.events = this.events.concat(response.data);
    }
  },
  async created() {
    let response = await this.$axios.$get("/wp/v2/pages?slug=events");
    var data = response[0];
    this.callout = data.acf;
  }
}

</script>
