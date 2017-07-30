<template>
  <div class="container">
    <article class="main">
      <div class="" v-html="data.content.rendered"></div>
    </article>
  </div>
</template>
<script>
import Axios from 'axios'

export default {
  head() {
    return {
      title: 'Bye',
      meta: [
        {
          'property': 'og:title',
          'content': 'Bye'
        },
        {
          'property': 'twitter:title',
          'content': 'Bye'
        },
        {
          'property': 'description',
          'content': "You have ducked out of all future communications."
        },
        {
          'property': 'og:description',
          'content': "You have ducked out of all future communications."
        },
        {
          'property': 'twitter:description',
          'content': "You have ducked out of all future communications."
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
  },
  async asyncData({ store }) {
    let response = await Axios.get(store.getters['hostname'] + 'wp/v2/pages?slug=bye')
    var data = response.data[0]
    return {
      data: data
    }
  }
}
</script>
