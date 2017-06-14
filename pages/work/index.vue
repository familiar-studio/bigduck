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
        <div class="block-work-featured">
          <router-link :to="{ name : 'work-id', params : { id: work.id }}">
            <div class="bg-img" :style="{'background-image': 'url(' + work.acf.hero_image.url + ') '}"
            ></div>
            <div class="overlay" :style="{ backgroundColor: work.acf.primary_color }"></div>
            <div class="container">
              <div class="badge-group">

                <div class="badge badge-default" v-if="topics" v-for="topic in work.topic">
                    <div v-html="topicsIndexedById[topic].icon"></div>
                    <div v-html="topicsIndexedById[topic].name"></div>
                </div>
                <div class="badge badge-default">
                  <div>{{ work.acf.client_name }}</div>
                </div>

              </div>

              <h1 class="display-2" v-html="work.acf.short_description"></h1>
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
