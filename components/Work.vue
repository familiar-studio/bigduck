<template id="">
  <div class="row">
    <div class="col-md-6 mb-3" v-for="(case_study, index) in work" >
      <router-link :to="{name: 'work-id', params: {id: case_study.id}}" :key="case_study.id">
        <div v-if="case_study.acf.hero_image">
          <img :src="case_study.acf.hero_image.url"  class="img-fluid" />
        </div>
        <div class="card">
          <div class="card-header" v-if="topics">
            <div class="badge badge-default" v-for="topic in case_study.topic">

                <div v-html="topicsIndexedById[topic].icon" class="img-fluid"></div>
                <div v-html="topicsIndexedById[topic].name"></div>
            </div>
          </div>
          <div class="card-block">
            <h3 class="card-title">{{ case_study.acf.client_name }}</h3>
            <p class="card-text" v-html="case_study.acf.short_description"></p>
          </div>
        </div>
      </router-link>
    </div>
  </div>
</template>
<script>
  export default {
    props: ['work'],
    computed: {
      topicsIndexedById () {
        return this.$store.getters['getTopicsIndexedById']
      },
      topics () {
        return this.$store.state.topics
      }
    }
  }
</script>
