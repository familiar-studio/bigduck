<template>
  <div>

  <div class="container-fluid no-hero">
    <div class="row">
      <div class="col-lg-2">
        <div v-if="topics && sectors" class="filter-bar">
          <FilterList label="Topics" taxonomy="topic" :terms="topics" :selected="selectedTopic" v-on:clicked="toggleTaxonomy($event)"></FilterList>
          <FilterList label="Sectors" taxonomy="sector" :terms="sectors" :selected="selectedSector" v-on:clicked="toggleTaxonomy($event)"></FilterList>
          <a v-if="selectedTopic || selectedSector" href="#" @click.prevent="resetFilters" class="btn btn-primary">Clear All</a>
        </div>
      </div>
      <div class='col-lg-8'>

          <div class="container" id="content">
            <div v-if="work">
              <h1>Work</h1>
              <transition name="fade">
              <Work :work="work" v-if="work.length > 0"></Work>
              <div v-else>
                No case studies found in
                <span v-if="selectedTopic">Topic {{getTopicsIndexedById[selectedTopic].name}}</span>
                <span v-if="selectedTopic && selectedSector"> and </span>
                <div v-if="selectedSector">Sector {{getSectorsIndexedById[selectedSector].name}}</div>
              </div>
              </transition>
            </div>
          </div>

      </div>
    </div>
  </div>
</div>
</template>

<script>
import Work from '~components/Work.vue'
import FilterList from '~components/FilterList.vue'
import Axios from 'axios'
import { mapState, mapGetters } from 'vuex'

export default {
  name: 'work',
  data () {
    return {
      work: null,
      selected: {
        topic: null,
        sector: null
      }
    }
  },
  components: {
    Work, FilterList
  },
  computed: {
    ...mapState(['topics', 'sectors']),
    ...mapGetters(['getTopicsIndexedById', 'getSectorsIndexedById']),
    selectedTopic () {
      return this.$route.query.topic
    },
    selectedSector () {
      return this.$route.query.sector
    }
  },
  watch: {
    '$route.query': 'filterResults'
  },
  async asyncData ({store, query}) {
    try {
      store.commit('resetPage')
      const response = await store.dispatch('fetchByQuery', {query: query, path: 'wp/v2/bd_case_study'})
      return {
        work: response.data
      }
    } catch (e) {
      console.error(e)
    }
  },
  head () {
    return {
      title: 'Work - All Projects',
      meta: [
        { description: '' },
        { 'og:image': '' }
      ]
    }
  },
  methods: {
    toggleTaxonomy (event) {
      // make a copy of the curren tquery string
      let query = Object.assign({}, this.$route.query)

      // toggle filters
      if (parseInt(query[event.taxonomy]) === event.id) {
        delete query[event.taxonomy]
      } else {
        query[event.taxonomy] = event.id
      }
      this.$router.push({ name: 'work-all', query: query })
    },
    resetFilters () {
      this.$router.push({ name: 'work-all', query: null })
    },
    async filterResults () {
      this.$store.commit('resetPage')
      const response = await this.$store.dispatch('fetchByQuery', { path: 'wp/v2/bd_case_study', query: this.$route.query })

      this.insights = response.data
    }
  }
}
</script>
