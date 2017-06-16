<template>
<div class="container no-hero">
  <h1>Contact Us</h1>

  <div v-if="pageContent" v-html="pageContent">
  </div>
    
</div>
</template>
<script>
  import Axios from 'axios'
  export default {
    name: 'contact',
    async asyncData ({store}) {
      let response = await Axios.get(store.getters['hostname'] + 'wp/v2/pages?slug=newfangled-testing-gated-content')
      return {
        pageContent: response.data[0].content.rendered
      }
    },
    async created () {
      let content = await Axios.get(this.$store.getters['hostname'] + 'wp/v2/pages?slug=newfangled-testing-gated-content')
      console.log('content', content)
    }
  }
</script>
