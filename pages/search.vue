<template lang="html">
  <div class="container">
    <form  @submit.prevent="search" class="form-inline">
      <input type="text" name="search" id="search" v-model="query" placeholder="search" class="form-control">
      <button type="submit" name="button" class="btn btn-primary">search</button>
    </form>
    <div v-if="results">
      <ul class="list-unstyled">
        <li v-for="result in results">
          <div class="card card-block mb-1">
            <router-link v-if="result.type == 'bd_insight'" :to="{name: 'insights-id', params: {id: result.id}}" href=""><h2>{{result.title.rendered}}</h2></router-link>
            <router-link v-if="result.type == 'bd_case_study'" :to="{name: 'work-id', params: {id: result.id}}" href=""><h2>{{result.title.rendered}}</h2></router-link>
            <router-link v-if="result.type == 'bd_event'" :to="{name: 'events-id', params: {id: result.id}}" href=""><h2>{{result.title.rendered}}</h2></router-link>
            <router-link v-if="result.type == 'bd_service'" :to="{name: 'services-slug', params: {slug: result.slug}}" href=""><h2>{{result.title.rendered}}</h2></router-link>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import Axios from 'axios'

export default {
  data () {
    return {
      totalPages: null,
      query: null
    }
  },
  async asyncData ({route, store}) {
    let response = await Axios.get(store.getters.hostname + 'wp/v2/posts?search=' + route.query.query)
    return {
      results: response.data
    }
  },
  mounted () {
    this.query = this.$route.query.query
    this.search()
  },
  methods: {
    async search () {
      let results = await Axios.get(this.$store.getters.hostname + 'wp/v2/posts?search=' + this.query)
      this.results = results.data
    }
  }
}
</script>
