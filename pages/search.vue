<template lang="html">
  <div class="container">
    <form  @submit.prevent="search">
      <input type="text" name="search" id="search" v-model="query" placeholder="search">
      <button type="submit" name="button">search</button>
    </form>
      <div v-if="results">
        <ul>
          <li v-for="result in results">
            {{result.title.rendered}}
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
      results: null,
      totalPages: null
    }
  },
  computed: {
    query () {
      return this.$route.query.query
    }
  },
  mounted () {
    this.search()
  },
  methods: {
    async search () {
      let results = await Axios.get(this.$store.getters.hostname + 'wp/v2/posts?search=' + this.query)
      console.log(results.data)
      this.results = results.data
    }
  }
}
</script>

