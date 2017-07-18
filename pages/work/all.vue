<template>
  <div class="">
    <div class="row">
      <div class="col-lg-3 col-xl-2 hidden-md-down">
        <div v-if="topics" class="filter-bar menu">
          <FilterList label="Topics" taxonomy="topic" :terms="topics" :selected="selectedTopic" v-on:clicked="toggleTaxonomy($event)"></FilterList>
          <a v-if="selectedTopic" href="#" @click.prevent="resetFilters" class="btn btn-primary">Clear All</a>
        </div>
      </div>
      <div class='col-lg-9 col-xl-8'>

        <div class="container" id="content">
          <div v-if="work">
            <h1>Work</h1>
            <Work :work="work" v-if="work.length > 0"></Work>
            <div v-else>
              There are is currently no work in the
              <span v-if="selectedTopic">{{getTopicsIndexedById[selectedTopic].name}}</span> topic

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
import Work from '~components/Work.vue'
import FilterList from '~components/FilterList.vue'
import Chat from '~components/Chat.vue'

import Axios from 'axios'
import { mapState, mapGetters } from 'vuex'


export default {
  name: 'work',
  data() {
    return {
      work: null,
      selected: {
        topic: null,
        sector: null
      }
    }
  },
  components: {
    Work, FilterList, Chat
  },
  computed: {
    ...mapState(['topics', 'sectors']),
    ...mapGetters(['getTopicsIndexedById']),
    selectedTopic() {
      return this.$route.query.topic
    }
  },
  watch: {
    '$route.query': 'filterResults'
  },
  async asyncData({ store, query }) {
    try {
      store.commit('resetPage')
      const response = await store.dispatch('fetchByQuery', { query: query, path: 'wp/v2/bd_case_study' })
      return {
        work: response.data
      }
    } catch (e) {
      console.error(e)
    }
  },
  head () {
    if (this.work[0]) {
      return {
        title: 'Work - All Projects',
        meta: [
          {
            'property': 'og:title',
            'content': 'Work - All Projects'
          },
          {
            'property': 'twitter:title',
            'content': 'Work - All Projects'
          },
          {
            'property': 'description',
            'content': "All Projects"
          },
          {
            'property': 'og:description',
            'content': "All Projects"
          },
          {
            'property': 'twitter:description',
            'content': "All Projects"
          },
          {
            'property': 'image',
            'content': this.work[0].acf.hero_image.url
          },
          {
            'property': 'og:image:url',
            'content': this.work[0].acf.hero_image.url
          },
          {
            'property': 'twitter:image',
            'content': this.work[0].acf.hero_image.url
          }
        ]
      }
    }
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
      this.$router.push({ name: 'work-all', query: query })
    },
    resetFilters() {
      this.$router.push({ name: 'work-all', query: null })
    },
    async filterResults() {
      this.$store.commit('resetPage')
      const response = await this.$store.dispatch('fetchByQuery', { path: 'wp/v2/bd_case_study', query: this.$route.query })

      this.insights = response.data
    }
  }
}
</script>
