<template>
  <Page :title="title" :formId="formId" :content="content" :image="image">
  </Page>
</template>
<script>
import Axios from 'axios'
import Page from '~components/Page'

export default {
  name: 'contact',
  head () {
    if (this.title && this.title) {
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
            'content': this.content
          },
          {
            'property': 'og:description',
            'content': this.content
          },
          {
            'property': 'twitter:description',
            'content': this.content
          },
          {
            'property': 'image',
            'content': this.image
          },
          {
            'property': 'og:image:url',
            'content': this.image
          },
          {
            'property': 'twitter:image',
            'content': this.image
          }
        ]
      }
    }
  },
  async asyncData({ store }) {
    let response = await Axios.get(store.getters['hostname'] + 'wp/v2/pages?slug=contact-us')
    var data = response.data[0]
    return {
      data: data,
      image: data.acf.featured_image,
      formId: data.acf.form,
      title: data.title.rendered,
      content: data.content.rendered
    }
  },
  components: {
    Page
  }
}
</script>
