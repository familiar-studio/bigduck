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
              <div v-for="(event, index) in events" :key="event">
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
import Event from '~components/Event.vue'
import FilterList from '~components/FilterList.vue'
import { mapState, mapGetters } from 'vuex'
import InlineCallout from '~components/InlineCallout.vue'
import Chat from '~components/Chat.vue'
import axios from 'axios'


export default {
  name: 'events',
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
            'property': 'og:title',
            'content': 'Upcoming Events'
          },
          {
            'property': 'twitter:title',
            'content': 'Upcoming Events'
          },
          {
            'property': 'description',
            'content': 'Learn more about our upcoming events.'
          },
          {
            'property': 'og:description',
            'content': 'Learn more about our upcoming events.'
          },
          {
            'property': 'twitter:description',
            'content': 'Learn more about our upcoming events.'
          },
          {
            'property': 'image',
            'content': 'http://bigduck.familiar.studio/wordpress/wp-content/uploads/2017/07/28546982-bf3e1ad0-709a-11e7-9b12-3b5d1238669f.png'
          },
          {
            'property': 'og:image:url',
            'content': 'http://bigduck.familiar.studio/wordpress/wp-content/uploads/2017/07/28546982-bf3e1ad0-709a-11e7-9b12-3b5d1238669f.png'
          },
          {
            'property': 'twitter:image',
            'content': 'http://bigduck.familiar.studio/wordpress/wp-content/uploads/2017/07/28546982-bf3e1ad0-709a-11e7-9b12-3b5d1238669f.png'
          }
        ]
      }
    }
  },
  data() {
    return {
      previouslyLoadedEvents: 0,
      callout: null
    }
  },
  async asyncData({ store, query }) {
    store.commit('resetPage')
    const response = await store.dispatch('fetchByQuery', { isPaged: true, query: query, path: 'wp/v2/bd_event' })
    return {
      events: response.data,
      totalPages: response.headers['x-wp-totalpages'],
      totalRecords: response.headers['x-wp-total']
    }
  },
  computed: {
    ...mapState(['categories', 'eventCategories', 'topics', 'callouts']),
    ...mapGetters(['getTopicsIndexedById', 'getEventCategoriesIndexedById', 'hostname']),
    selectedCategory() {
      return this.$route.query.event_category
    },
    selectedTopic() {
      return this.$route.query.topic
    }
  },
  watch: {
    '$route.query': 'filterResults'
  },
  methods: {
    toggleTaxonomy(event) {
      // make a copy of the curren tquery string
      let query = Object.assign({}, this.$route.query)

      // toggle filters
      if (parseInt(query[event.taxonomy]) === event.id) {
        delete query[event.taxonomy]
      } else {
        query[event.taxonomy] = event.id
      }
      this.$router.push({ name: 'events', query: query })
    },
    resetFilters() {
      this.$router.push({ name: 'events', query: null })
    },
    async filterResults() {
      this.$store.commit('resetPage')
      const response = await this.$store.dispatch('fetchByQuery', { isPaged: true, path: 'wp/v2/bd_event', query: this.$route.query })
      this.events = response.data
      this.totalPages = response.headers['x-wp-totalpages']
      this.totalRecords = response.headers['x-wp-total']
    },
    async nextPage() {
      this.$store.commit('nextPage')
      this.previouslyLoadedEvents = this.events.length
      let query = Object.assign({}, this.$route.query)
      const response = await this.$store.dispatch('fetchByQuery', { isPaged: true, query: query, path: 'wp/v2/bd_event' })
      this.events = this.events.concat(response.data)
    }
  },
  async created() {
    let response = await axios.get(this.hostname + 'wp/v2/pages?slug=events')
    var data = response.data[0]
    this.callout = data.acf;
  }
}
</script>
