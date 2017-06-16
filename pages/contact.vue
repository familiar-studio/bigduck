<template>
<div class="container no-hero">
  <h1>Contact Us</h1>

  <div v-if="pageContent" v-html="pageContent">
  </div>
  <GravityForm :formId="formId"></GravityForm>
</div>
</template>
<script>
  import Axios from 'axios'
  import GravityForm from '~components/GravityForm'

  export default {
    name: 'contact',
    async asyncData ({store}) {
      let response = await Axios.get(store.getters['hostname'] + 'wp/v2/pages?slug=newfangled-testing-gated-content')
      var pageContent = response.data[0].content.rendered
      // var el1 = document.createElement(pageContent)
      // var formId = el1.getElementById('gated-content-form').data('form')
      var pieces = pageContent.split('|')
      return {
        formId: pieces[1],
        pageContent: pieces[0]
      }
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
