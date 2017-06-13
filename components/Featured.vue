<template>
  <div :style="{'background': 'url(' + work.acf.hero_image.url + ') ' + work.acf.primary_color }"
  >
  <router-link :to="{ name : 'work-id', params : { id: work.id }}">
    <div class="container">
      <div v-if="topicsIndexedById && work.topic[0]">
        <div class="media">
          <img class="d-flex mr-3" :src="topicsIndexedById[work.topic[0]].acf.icon">
          <div class="media-body" v-html="topicsIndexedById[work.topic[0]].name"></div>
        </div>
      </div>
      {{ work.acf.client_name }}
      <p v-html="work.acf.short_description"></p>
    </div>
  </router-link>
  </div>
</template>

<script>
  export default {
    name: 'featured',
    props: ['work'],
    computed: {
      topicsIndexedById () {
        return this.$store.getters['getTopicsIndexedById']
      },
      typesIndexedById () {
        return this.$store.getters['getTypesIndexedById']
      },
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
