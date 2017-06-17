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
  import GravityForm from '~components/GravityForm'

  export default {
    name: 'speaking',
    async asyncData ({store}) {
      let response = await Axios.get(store.getters['hostname'] + 'wp/v2/pages?slug=interested-in-having-big-duck-speak-at-your-organization')

      if (response.data) {
        return {
          title: response.data[0].title.rendered,
          pageContent: response.data[0].content.rendered
        }
      }
    },
    head: {
      title: 'Speaking Engagements'
    },
    async created () {
      // let content = await Axios.get(this.$store.getters['hostname'] + 'wp/v2/pages?slug=newfangled-testing-gated-content')
      // console.log('content', content)
    },
    components: {
      GravityForm
    }
  }
</script>
