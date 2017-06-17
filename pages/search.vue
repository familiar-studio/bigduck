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
            <a href=""><h2>{{result.title.rendered}}</h2></a>
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
      totalPages: null
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

