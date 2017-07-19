<template>
  <div>
    <Featured v-for="(work, index) in featured" :work="work" :index="index" :key="work"></Featured>
  </div>
</template>

<script>
import axios from 'axios'
import Featured from '~components/Featured.vue'

export default {
  name: 'featured-work',
  head () {
    if (this.featured && this.featured[0]) {
      return {
        title: 'Featured Work',
        meta: [
          {
            'property': 'og:title',
            'content': 'Featured Work'
          },
          {
            'property': 'twitter:title',
            'content': 'Featured Work'
          },
          {
            'property': 'description',
            'content': "Learn more about what we've done."
          },
          {
            'property': 'og:description',
            'content': "Learn more about what we've done."
          },
          {
            'property': 'twitter:description',
            'content': "Learn more about what we've done."
          },
          {
            'property': 'image',
            'content': this.featured[0].acf.hero_image.url
          },
          {
            'property': 'og:image:url',
            'content': this.featured[0].acf.hero_image.url
          },
          {
            'property': 'twitter:image',
            'content': this.featured[0].acf.hero_image.url
          }
        ]
      }
    }
  },
  async asyncData({ state, store }) {
    let data = {}
    let response = await axios.get(store.getters.hostname + 'familiar/v1/featured-work')
    data.featured = response.data
    return data
  },
  components: {
    Featured
  }
}
</script>
