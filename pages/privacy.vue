<template>
<div class="container bg-white ">
  <article class="main">
    <h1>{{this.title}}</h1>
    <div v-if="pageContent" v-html="pageContent">
    </div>
  </article>
</div>
</template>
<script>
  import Axios from 'axios'

  export default {
    name: 'speaking',
    async asyncData ({store}) {
      let response = await Axios.get(store.getters['hostname'] + 'wp/v2/pages?slug=privacy-policy')

      if (response.data) {
        return {
          title: response.data[0].title.rendered,
          pageContent: response.data[0].content.rendered
        }
      }
    },
    head () {
      return {
        title: this.title,
        meta: [ ]
      }
    }
  }
</script>
