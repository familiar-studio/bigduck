<template>
<div>
  <div v-if="featuredImage" class="img-hero" :style="{ backgroundImage: 'url(' + featuredImage + ')' }">
    <figcaption class="figure-caption">Hardcoded for now</figcaption>
  </div>
  <div class="container" :class="{'overlap':featuredImage}">
    <article class="main">
      <h1>{{this.title}}</h1>
      <div v-if="pageContent" v-html="pageContent">
      </div>
    </article>
  </div>
</div>
</template>
<script>
  import Axios from 'axios'

  export default {
    name: 'speaking',
    async asyncData ({store}) {
      let response = await Axios.get(store.getters['hostname'] + 'wp/v2/pages?slug=speaking-engagements')

      if (response.data) {
        return {
          title: response.data[0].title.rendered,
          pageContent: response.data[0].content.rendered,
          featuredImage: response.data[0].acf.featured_image,
          data: response.data[0]
        }
      }
    },
    head () {
      return {
        title: this.title,
        meta: []
      }
    }
  }
</script>
