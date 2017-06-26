<template>
  <div class="no-hero">
    <div class="row">
      <div class="col-lg-2">
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
              <h4>
                <router-link :to="{name: 'events-speaking'}">
                  <img src="/svgs/speaking-icon.svg" class="mr-2" />Interested in having Big Duck speak at your organization?
                  <span class="label colorChange"> Learn more about our talks…</span>
                </router-link>
              </h4>
            </div>
            <div v-if="events.length > 0">
              <!-- <ListTransition :previous="previouslyLoadedEvents" :current="events.length"> -->
              <transition-group name="fade" appear>
                <div v-for="(event, index) in events" :key="index">
                  <Event :entry="event" :firstBlock="true" :categories="categories" :index="index" :relatedTeamMembers="event.related_team_members.data"></Event>
                  <transition name="list" appear>
                    <Subscribe v-if="callouts && callouts[0] && index % 5 == 1 && index < events.length - 1" :entry="callouts[0]"></Subscribe>
                  </transition>
                </div>
              </transition-group>
              <!-- </ListTransition> -->
              <div class="pager" v-if="events.length < totalRecords">
                <a class="btn btn-primary my-4" href="#" @click.prevent="nextPage">Load more</a>
              </div>
            </div>
            <div v-else>
              No events found in
              <span v-if="selectedTopic">Topic {{getTopicsIndexedById[selectedTopic].name}}</span>
              <span v-if="selectedTopic && selectedCategory"> and </span>
              <div v-if="selectedCategory">Type {{getEventCategoriesIndexedById[selectedCategory].name}}</div>
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
import ListTransition from '~components/ListTransition.vue'
import { mapState, mapGetters } from 'vuex'
import InlineCallout from '~components/InlineCallout.vue'
import Chat from '~components/Chat.vue'
import axios from 'axios'


export default {
  name: 'events',
  components: {
    Event,
    FilterList,
    ListTransition,
    InlineCallout,
    Chat
  },
  head() {
    return {
      title: 'Events',
      meta: [
        { description: 'Overview' },
        { 'og:image': 'Events images' }
      ]
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
  }
}
</script>
