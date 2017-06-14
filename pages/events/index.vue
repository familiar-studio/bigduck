<template>
<div class="container-fluid no-hero">
    <div class="row">
      <div class="col-lg-2">
        <div v-if="topics && types" class="sticky-top filter-bar">

          <div class="label label-lg">Topics</div>
          <div class ="media-list">
            <router-link v-for="topic in topics" key="topic.id" :to="{name: 'events', query: {topic: topic.id}}">
              <div class="media">
                <div v-if="topic.icon" v-html="topic.icon.data"></div>
                <div class="media-body">
                  <h6 class="mt-0" v-html="topic.name"></h6>
                </div>
              </div>
            </router-link>

          </div>

          <div class="label label-lg">Event Type</div>
          <div class ="media-list">
            <router-link v-for="type in types" key="type.id" :to="{name: 'events', query: {type: type.id}}">
              <div class="media">
                <div class="d-flex mr-3" v-if="type.icon" v-html="type.icon.data"></div>
                <div class="media-body">
                  <h6 class="mt-0" v-html="type.name"></h6>
                </div>
              </div>
            </router-link>
          </div>

        </div>
      </div>
      <div class="col-lg-8">
        <div class="container">
          <div v-if="events" >
            <h1>Upcoming Events</h1>
            <h3><img src="http://placehold.it/30x20" /> Interested in having Big Duck speak at your organization? <a href="#">Learn more about our talks...</a></h3>
            <Pager :totalPages="totalPages" path="/events" ></Pager>
            <div v-if="events">
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
  import { mapState } from 'vuex'

  export default {
    name: 'events',
    components: {
      Event,
      Subscribe,
      Pager
    },
    async asyncData ({store, query}) {
      const response = await store.dispatch('fetchByQuery', {query: query, path: 'wp/v2/bd_event'})
      return {
        events: response.data,
        totalPages: response.totalPages
      }
    },
    computed: {
      ...mapState(['categories', 'callouts', 'types', 'topics']),
      nextPage () {
        return this.page + 1
      },
      previousPage () {
        return this.page - 1
      },
      totalPages () {
        return this.$store.getters['totalPages']
      }
    },
    watch: {
      '$route.query': 'fetchByQuery'
    },
    methods: {
    }
  }
</script>
