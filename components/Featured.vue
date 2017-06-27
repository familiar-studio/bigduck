<template>
  <div v-once class="block-work-featured">
    <router-link :to="{ name : 'work-slug', params : { slug: work.slug }}">
      <div class="bg-img" :style="{'background-image': 'url(' + work.acf.hero_image.url + ') '}"></div>
      <div class="overlay" :style="{ backgroundColor: work.acf.primary_color }"></div>
      <div class="container">
        <div class="badge-group">
          <div class="badge badge-default" v-if="topics && getTopicsIndexedById" v-for="topic in work.topic">
            <template v-if="getTopicsIndexedById[topic]">
              <div v-html="getTopicsIndexedById[topic].icon"></div>
              <div v-html="getTopicsIndexedById[topic].name"></div>
            </template>
          </div>
          <div class="badge badge-default">
            <div>{{ work.acf.client_name }}</div>
          </div>
  
        </div>
  
        <h1 v-if="work.title" class="display-2" v-html="work.title.rendered"></h1>
      </div>
    </router-link>
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'

export default {
  name: 'featured',
  props: ['work'],
  computed: {
    ...mapState(['types', 'topics', 'eventCategories']),
    ...mapGetters(['getTopicsIndexedById']),
    blockClass() {
      if (this.index === 0) {
        return 'first-block'
      } else if (this.index % 2 === 0) {
        return 'odd-block'
      } else {
        return 'even-block'
      }
    }
  }
}
</script>
