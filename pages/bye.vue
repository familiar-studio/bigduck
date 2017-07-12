<template>
  <div class="container">
    <div class="" v-html="data.content.rendered"></div>
  </div>
</template>
<script>
  import Axios from 'axios'

  export default {

    async asyncData({ store }) {
      let response = await Axios.get(store.getters['hostname'] + 'wp/v2/pages?slug=bye')
      var data = response.data[0]
      return {
        data: data
      }
    }
  }
</script>
