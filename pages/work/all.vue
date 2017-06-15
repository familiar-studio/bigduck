<template>
  <div>
 
  <div class="container-fluid no-hero">
    <div class="row">
      <div class="col-lg-2">
        <div v-if="topics && types" class="filter-bar">
          <div class="label label-lg">Topics</div>
          <div class="media-list">
            <router-link v-for="topic in topics" key="topic.id" :to="{name: 'work', query: {topic: topic.id}}">
              <div class="media">
                <img :src="topic.acf.icon">
                <div class="media-body">
                  <h6 v-html="topic.name"></h6>
                </div>
              </div>
            </router-link>
          </div>
          <div class="label label-lg">Types</div>
          <div class="media-list">
            <router-link v-for="type in types" key="type.id" :to="{name: 'work', query: {type: type.id}}">
              <div class="media">
                <img :src="type.acf.icon">
                <div class="media-body">
                  <h6 v-html="type.name"></h6>
                </div>
              </div>
            </router-link>
          </div>
          <router-link :to="{name: 'work'}">
            <button class="btn btn-info">Clear All</button>
          </router-link>
        </div>
      </div>
      <div class='col-lg-8'>
        <div class="container" id="content">
          <div v-if="work">
            <h1>Work</h1>
            <Work :work="work"></Work>
            <Pager :totalPages="totalPages" path="/work" ></Pager>
          </div>
          <div v-else>
            Loading case studies...
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<script>
import Work from '../../components/Work.vue'
import Pager from '../../components/Pager.vue'
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
    Work,
    Pager
  },
  computed: {
    ...mapState(['topics', 'types']),
    nextPage () {
      return this.page + 1
    },
    previousPage () {
      return this.page - 1
    }
  },
  created () {
    this.fetchByQuery()
  },
  watch: {
    '$route.query': 'fetchByQuery',
    '$route.query.page': 'fetchByQuery'
  },
  methods: {
    goToNextPage () {
      this.$store.commit('nextPage')
      this.fetchByQuery()
    },
    goToPreviousPage () {
      this.$store.commit('previousPage')
      this.fetchByQuery()
    },
    async fetchByQuery (query) {
      const response = await this.$store.dispatch('fetchByQuery', {query: this.$route.query, path: 'wp/v2/bd_case_study'})
      this.work = response.data
      this.totalPages = response.totalPages
    }
  }
}
</script>
