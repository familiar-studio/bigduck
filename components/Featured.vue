<template>
  <div class="block-work-featured">
  <router-link :to="{ name : 'work-id', params : { id: work.id }}">
    <div class="bg-img" :style="{'background-image': 'url(' + work.acf.hero_image.url + ') '}"
    ></div>
    <div class="overlay" :style="{ backgroundColor: work.acf.primary_color }"></div>
    <div class="container">
      <div class="badge-group">
        <div class="badge badge-default" v-if="topics && getTopicsIndexedById" v-for="topic in work.topic">
          <template v-if="getTopicsIndexedById[topic.term_id]">
            <div v-html="getTopicsIndexedById[topic.term_id].icon"></div>
            <div v-html="getTopicsIndexedById[topic.term_id].name"></div>
          </template>
        </div>
        <div class="badge badge-default">
          <div>{{ work.acf.client_name }}</div>
        </div>

      </div>

      <h1 class="display-2" v-html="work.acf.short_description"></h1>
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
      blockClass () {
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
