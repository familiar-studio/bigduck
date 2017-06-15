<template>
  <div>

  <div class="container-fluid no-hero">
    <div class="row">
      <div class="col-lg-2">
        <div v-if="topics && sectors" class="filter-bar">
          <div class="label label-lg">Topics</div>
          <div class="media-list">
            <router-link v-for="topic in topics" key="topic.id" :to="{name: 'work-all', query: {topic: topic.id}}" :class="{ activeFilter: topic.id == selectedTopic }">
              <div class="media">
                <img :src="topic.acf.icon">
                <div class="media-body">
                  <h6 v-html="topic.name"></h6>
                </div>
              </div>
            </router-link>
          </div>
          <div class="label label-lg">Sectors</div>
          <div class="media-list">
            <router-link v-for="sector in sectors" key="sector.id" :to="{name: 'work-all', query: {sector: sector.id}}" :class="{ activeFilter: sector.id == selectedSector }">
              <div class="media">
                <img :src="sector.acf.icon">
                <div class="media-body">
                  <h6 v-html="sector.name"></h6>
                </div>
              </div>
            </router-link>
          </div>
          <router-link :to="{name: 'work-all'}">
            <button class="btn btn-info">Clear All</button>
          </router-link>
        </div>
      </div>
      <div class='col-lg-8'>

          <div class="container" id="content">
            <div v-if="work">
              <h1>Work</h1>
              <transition name="fade">
              <Work :work="work"></Work>
              </transition>
            </div>
          </div>

      </div>
    </div>
  </div>
</div>
</template>

<script>
import Work from '../../components/Work.vue'
import Axios from 'axios'
import { mapState, mapGetters } from 'vuex'

export default {
  name: 'work',
  data () {
    return {
      work: null,
      totalPages: null,
      page: null
    }
  },
  components: {
    Work
  },
  computed: {
    ...mapState(['topics', 'sectors', 'getTopicsIndexedById', 'getSectorsIndexedById']),
    nextPage () {
      return this.page + 1
    },
    previousPage () {
      return this.page - 1
    },
    selectedSector () {
      return this.$route.query.sector
    },
    selectedTopic () {
      return this.$route.query.topic
    }
  },
  async asyncData ({state, store, route}) {
    let data = {}
    let response = await store.dispatch('fetchByQuery', {path: 'wp/v2/bd_case_study', query: route.query})
    data['work'] = response.data
    return data
  },
  watch: {
    'route.query': 'fetchByQuery'
  },
  methods: {
    fetchByQuery () {
      console.log('fetchByQuery')
    }
  }
}
</script>
