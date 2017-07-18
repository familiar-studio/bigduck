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
  name: 'privacy',
  async asyncData({ store }) {
    let response = await Axios.get(store.getters['hostname'] + 'wp/v2/pages?slug=privacy-policy')

    if (response.data) {
      return {
        title: response.data[0].title.rendered,
        pageContent: response.data[0].content.rendered
      }
    }
  },
  head() {
    if (this.title) {

      return {
        title: this.title,
        meta: [
          {
            'property': 'og:title',
            'content': this.title
          },
          {
            'property': 'twitter:title',
            'content': this.title
          },
          {
            'property': 'description',
            'content': this.pageContent
          },
          {
            'property': 'og:description',
            'content': this.pageContent
          },
          {
            'property': 'twitter:description',
            'content': this.pageContent
          },
          {
            'property': 'image',
            'content': 'http://bigduck-wordpress.familiar.studio/wp-content/uploads/2017/07/logo.svg'
          },
          {
            'property': 'og:image:url',
            'content': 'http://bigduck-wordpress.familiar.studio/wp-content/uploads/2017/07/logo.svg'
          },
          {
            'property': 'twitter:image',
            'content': 'http://bigduck-wordpress.familiar.studio/wp-content/uploads/2017/07/logo.svg'
          }
        ]
      }
    }
  }
}
</script>
