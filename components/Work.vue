<template id="">
  <div class="row">
    <div class="col-md-6 mb-3 d-flex" v-for="(case_study, index) in work" >
      <router-link :to="{name: 'work-slug', params: {slug: case_study.slug}}" :key="case_study.id" class="block-work-small">
        <div v-if="case_study.acf.hero_image" class="img-wrapper">
          <img :src="case_study.acf.hero_image.sizes.cropped_rectangle"  class="img-fluid" />
          <!-- <div class="bg-img" :style=" { backgroundImage: 'url(' + case_study.acf.hero_image.url + ')' }">
          </div> -->
        </div>
        <div class="card">
          <div class="card-block">
            <div class="badge-group" v-if="topics">
              <div class="badge badge-default" v-for="topic in case_study.topic">

                  <div v-html="topicsIndexedById[topic].icon" class="img-fluid"></div>
                  <div v-html="topicsIndexedById[topic].name"></div>
              </div>
            </div>
            <h3 class="card-title"><span class="underlineChange hoverColor">{{ case_study.acf.client_name }}</span></h3>
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
