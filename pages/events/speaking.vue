<template>
  <Page :title="title" :formId="formId" :content="content" :image="image">
  </Page>
</template>
<script>
import Axios from 'axios'
import Page from '~components/Page'

export default {
  name: 'contact',
  head() {
    return {
      title: this.title,
      meta: [
        { 'og:image': this.image }
      ]
    }
  },
  async asyncData({ store }) {
    let response = await Axios.get(store.getters['hostname'] + 'wp/v2/pages?slug=speaking-engagements')
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
