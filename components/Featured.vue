<template>
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
