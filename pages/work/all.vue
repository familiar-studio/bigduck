<template>
  <div>

  <div class="container-fluid no-hero">
    <div class="row">
      <div class="col-lg-2">
        <div v-if="topics && sectors" class="filter-bar">
          <FilterList label="Topics" taxonomy="topic" :terms="topics" :selected="selected.topic" v-on:clicked="setActiveTaxonomy($event)"></FilterList>
          <FilterList label="Sectors" taxonomy="sector" :terms="sectors" :selected="selected.sector" v-on:clicked="setActiveTaxonomy($event)"></FilterList>
          <router-link :to="{name: 'work-all'}">
            <button class="btn btn-info">Clear All</button>
          </router-link>
          <a href="#" @click.prevent="resetFilters" class="btn btn-primary">Clear All</a>
        </div>
      </div>
      <div class='col-lg-8'>

          <div class="container" id="content">
            <div v-if="work">
              <h1>Work</h1>
              <transition name="fade">
              <Work :work="work" v-if="work.length > 0"></Work>
              <div v-else>No results found</div>
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
      totalPages: null,
      page: null,
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
    ...mapState(['topics', 'sectors', 'getTopicsIndexedById', 'getSectorsIndexedById']),
    nextPage () {
      return this.page + 1
    },
    previousPage () {
      return this.page - 1
    },
    query () {
      let query = {}
      Object.keys(this.selected).forEach((key) => {
        if (this.selected[key]) {
          query[key] = this.selected[key]
        }
      })
      return query
    }
  },
  async asyncData ({store, route}) {
    let data = {}
    let response = await store.dispatch('fetchByQuery', {path: 'wp/v2/bd_case_study', query: store.query})
    data['work'] = response.data
    return data
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
      let response = await this.$store.dispatch('fetchByQuery', {path: 'wp/v2/bd_case_study', query: this.$store.state.query})
      this.work = response.data
    },
    async resetFilters () {
      this.$store.commit('setFilterQuery', {})
      let response = await this.$store.dispatch('fetchByQuery', {path: 'wp/v2/bd_case_study', query: this.$store.state.query})
      this.work = response.data
    }
  }
}
</script>
