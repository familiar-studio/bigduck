<template>
<div>
  <div class="img-hero" :style=" { backgroundImage: 'url(' + data.acf.featured_image  + ')' }">
    <figcaption class="figure-caption">{{data.acf.featured_image.caption}}</figcaption>
  </div>
  <div class="container" id="content">
    <article class="main overlap">
      <h1>{{title}}</h1>
      <div v-if="pageContent" v-html="pageContent">
      </div>
    </article>
    <div class="form-light mb-5">
      <GravityForm v-if="formId" :formId="formId" :showAll="true"></GravityForm>
    </div>
  </div>

</div>
</template>
<script>
  import Axios from 'axios'
  import GravityForm from '~components/GravityForm'

  export default {
    name: 'contact',
    head () {
      return {
        title: 'Contact Us',
        meta: [
          { description: '' },
          { 'og:image': '' }
        ]
      }
    },
    async asyncData ({store}) {
      let response = await Axios.get(store.getters['hostname'] + 'wp/v2/pages?slug=contact-us')
      var data = response.data[0]
      return {
        data: data,
        formId: data.acf.form,
        title: data.title.rendered,
        pageContent: data.content.rendered
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
