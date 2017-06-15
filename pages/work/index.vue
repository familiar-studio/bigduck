<template>
  <div>
    <div >
      <div v-for="work in featured">
        <div class="block-work-featured">
          <router-link :to="{ name : 'work-id', params : { id: work.id }}">
            <div class="bg-img" :style="{'background-image': 'url(' + work.acf.hero_image.url + ') '}"
            ></div>
            <div class="overlay" :style="{ backgroundColor: work.acf.primary_color }"></div>
            <div class="container">
              <div class="badge-group">

                <div class="badge badge-default" v-if="topics" v-for="topic in work.topic">
                    <div v-html="getTopicsIndexedById[topic].icon"></div>
                    <div v-html="getTopicsIndexedById[topic].name"></div>
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
  </div>
</template>

<script>
  import axios from 'axios'
  import { mapState, mapGetters } from 'vuex'

  export default {
    name: 'featured-work',
    async asyncData ({state, store}) {
      let data = {}
      let response = await axios.get(store.getters.hostname + 'familiar/v1/featured-work')
      data['featured'] = response.data
      return data
    },
    computed: {
      ...mapGetters(['getTopicsIndexedById'])
    }
  }
</script>
