<template>
<div class="container no-hero">
  <h1>Contact Us</h1>

  <div v-if="pageContent" v-html="pageContent">
  </div>


  <h1>{{formId}}</h1>
  <GravityForm :formId="7"></GravityForm>
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

      console.log('formId', formId)
      return {
        formId: formId,
        pageContent: pageContent
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
