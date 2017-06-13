<template>
  <div>
    <ul class="nav nav-pills">
      <li class="nav-item">
        <router-link :to="{ name: 'work'}" class="nav-link">Featured Work</router-link>
      </li>
      <li class="nav-item">
        <router-link :to="{ name: 'work-all'}" class="nav-link">All Projects</router-link>
      </li>
    </ul>
    <div v-if="featured">
      <div v-for="work in featured">
        <div :style="{'background': 'url(' + work.acf.hero_image.url + ') ' + work.acf.primary_color }"
        >
        <router-link :to="{ name : 'work-id', params : { id: work.id }}">
          <div class="container">
            <div v-if="topicsIndexedById">
              <div class="media">
                <img class="d-flex mr-3" :src="topicsIndexedById[work.topic[0].term_id].acf.icon">
                <div class="media-body" v-html="topicsIndexedById[work.topic[0].term_id].name"></div>
              </div>
            </div>
            {{ work.acf.client_name }}
            <p v-html="work.acf.short_description"></p>
          </div>
        </router-link>
      </div>
      </div>
    </div>
    <div v-else>
      Loading featured work...
    </div>
  </div>
</template>

<script>
  import axios from 'axios'

  export default {
    name: 'featured-work',
    async asyncData ({state, store}) {
      let data = {}
      let response = await axios.get(store.getters.hostname + 'familiar/v1/featured-work')
      data['featured'] = response.data
      return data
    },
    computed: {
      topicsIndexedById () {
        return this.$store.getters['getTopicsIndexedById']
      }

    }
  }
</script>
