<template>
<div class="container-fluid no-hero">
    <div class="row">
      <div class="col-lg-2">
        <div v-if="topics && eventCategories" class="sticky-top filter-bar">

          <FilterList label="Topics" taxonomy="topic" :terms="topics" :selected="selected.topic" v-on:clicked="setActiveTaxonomy($event)"></FilterList>
          <FilterList label="Types" taxonomy="event_category" :terms="eventCategories" :selected="selected.event_category" v-on:clicked="setActiveTaxonomy($event)"></FilterList>
          <a href="#" @click.prevent="resetFilters" class="btn btn-primary">Clear All</a>

        </div>
      </div>
      <div class="col-lg-8">
        <div class="container">
          <div v-if="events" id="content">
            <h1>Upcoming Events</h1>
            <h3><img src="http://placehold.it/30x20" />  <router-link :to="{name: 'speakingEngagements'}">Interested in having Big Duck speak at your organization?Learn more about our talks...</router-link></h3>
            <Pager :totalPages="totalPages" path="/events" ></Pager>
            <div v-if="events.length > 0">
              <div v-for="(event, index) in events">
                  <Event :entry="event" :categories="categories" :index="index"></Event>

                  <Subscribe v-if="callouts[0] && index % 5 == 1 && index < events.length - 1" :entry="callouts[0]"></Subscribe>
              </div>
            </div>
            <div v-else>
              No events found.
            </div>
          </div>
          <div v-else>
            Loading events...
          </div>
    </div>
  </div>

</div>
</div>
</template>
<script>
  import Event from '~components/Event.vue'
  import Subscribe from '~components/subscribe/container.vue'
  import Pager from '~components/Pager.vue'
  import FilterList from '~components/FilterList.vue'
  import { mapState, mapGetters } from 'vuex'

  export default {
    name: 'events',
    components: {
      Event,
      Subscribe,
      Pager,
      FilterList
    },
    data () {
      return {
        selected: {
          event_category: null,
          topic: null
        }
      }
    },
    async asyncData ({store, query}) {
      const response = await store.dispatch('fetchByQuery', {query: query, path: 'wp/v2/bd_event'})
      return {
        events: response.data,
        totalPages: response.totalPages
      }
    },
    computed: {
      ...mapState(['categories', 'callouts', 'eventCategories', 'topics']),
      ...mapGetters(['getTopicsIndexedById', 'getEventCategoriesIndexedById']),
      nextPage () {
        return this.page + 1
      },
      previousPage () {
        return this.page - 1
      }
    },
    methods: {
      // given a taxonomy type and id
      async setActiveTaxonomy (event) {
        const type = event.taxonomy
        const id = event.id
        // if the selected taxonomy is equal to the passed id, set it to null, otherwise set it
        this.selected[type] = this.selected[type] === id.toString() ? null : id.toString()
        let query = {}
        // build a query to send to the store
        Object.keys(this.selected).forEach((key) => {
          // if a property is null, skip over it
          if (this.selected[key]) {
            query[key] = this.selected[key]
          }
        })
        this.$store.commit('setFilterQuery', query)
        let response = await this.$store.dispatch('fetchByQuery', {path: 'wp/v2/bd_event', query: this.$store.state.query})
        this.events = response.data
      },
      async resetFilters () {
        this.$store.commit('setFilterQuery', {})
        let response = await this.$store.dispatch('fetchByQuery', {path: 'wp/v2/bd_event', query: this.$store.state.query})
        this.events = response.data
      }
    }
  }
</script>
