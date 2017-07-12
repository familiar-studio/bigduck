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
  head() {
    return {
      title: 'Featured Work',
      meta: [
        { description: '' },
        { 'og:image': '' }
      ]
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
