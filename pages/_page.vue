<template>
  <Page :title="title" :formId="formId" :content="content" :image="image">
  </Page>
</template>

<script>
import Axios from 'axios'
import Page from '~/components/Page'


export default {


  // if url doesnt exist try and find it in insights
  async asyncData({ store, redirect, params }) {

    let response = await Axios.get(store.getters['hostname'] + 'wp/v2/pages', { params: { slug: params.page } })
    if (response.data.length > 0) {

      var data = response.data[0]
      return {
        data: data,
        image: data.acf.featured_image,
        formId: data.acf.form,
        title: data.title.rendered,
        content: data.content.rendered
      }

    } else {
      return redirect('/insights/' + params.page)
    }
  },
  components: {
    Page
  },
  head() {
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
            'hid': "description",
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
}
</script>

