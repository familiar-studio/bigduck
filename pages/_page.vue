<template>
  <div class="container">
    <article class="main">
      <h1 v-html="page.title.rendered"></h1>
      <div v-html="page.content.rendered"></div>
    </article>
  
  </div>
</template>

<script>
import Axios from 'axios'

export default {


  // if url doesnt exist try and find it in insights
  async asyncData({ store, redirect, params }) {

    let response = await Axios.get(store.getters['hostname'] + 'wp/v2/pages', { params: { slug: params.page } })
    if (response.data.length > 0) {

      return {
        page: response.data[0]
      }

    } else {
      return redirect('/insights/' + params.page)
    }
  },
  head() {
    if (this.page) {
      return {
        title: this.page.title.rendered,
        meta: [
          {
            'property': 'og:title',
            'content': this.page.title.rendered
          },
          {
            'property': 'twitter:title',
            'content': this.page.title.rendered
          }
        ]
      }
    }
  }
}
</script>

