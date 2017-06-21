<template>
  <div>
      <div class="img-hero" :style=" { backgroundImage: 'url(' + service.acf.featured_image.url + ')' }">
        <figcaption class="figure-caption">{{service.acf.featured_image.caption}}</figcaption>
      </div>
      <div id="content">
        <div class="container">
          <article class="main bg-white overlap mb-5">
            <div class="badge-group">
              <router-link class="badge badge-default underlineChange" :to="{name: 'services'}">Services</router-link>
            </div>
            <h1>{{ service.title.rendered }}</h1>
            <div v-html="service.acf.introduction"></div>
          </article>
          <div class="row">
            <div class="col-xl-10 offset-xl-1">
              <div v-for="block in service.acf.service_body">
                <div v-if="block.acf_fc_layout == 'text'" v-html="block.text" class="mb-5 block-text"></div>
                <div v-if="block.acf_fc_layout == 'image'" class="mb-5">
                  <img class="img-fluid img-multiply" :src="block.image.url" />
                </div>
                <h2 v-if="block.acf_fc_layout == 'heading'" class="mt-5" v-html="block.heading"></h2>
              </div>
            </div>
          </div>
        <div class="callout-fullwidth bg-inverse text-white">
          <div class="callout-content">
              <h2>{{ service.acf.cta_text }}</h2>
          </div>
        </div>
        </div>
        <div class="container mt-5" v-if="relatedCaseStudies && relatedCaseStudies.length > 0">
          <h2>Related Case Studies</h2>
          <Work :work="relatedCaseStudies"></Work>
        </div>
      </div>
  </div>
</template>
<script>
  import Axios from 'axios'
  import Subscribe from '~components/subscribe/container.vue'
  import Work from '~components/Work.vue'
  import Post from '~components/Post.vue'

  export default {
    name: 'service',
    head () {
      return {
        title: this.service.title.rendered,
        meta: [
          { description: this.service.acf.introduction },
          { 'og:image': this.service.acf.featured_image.url }
        ]
      }
    },
    data () {
      return {
        service: null,
        relatedCaseStudies: null,
        relatedInsights: null
      }
    },
    computed: {
      slug () {
        return this.$route.params.slug
      },
      callouts () {
        return this.$store.state.callouts
      },
      topics () {
        return this.$store.state.topics
      },
      types () {
        return this.$store.state.types
      },
      topicsIndexedById () {
        return this.$store.getters['getTopicsIndexedById']
      },
      typesIndexedById () {
        return this.$store.getters['getTypesIndexedById']
      }
    },
    components: {
      Subscribe, Work, Post
    },
    async created () {
      let relatedWorkIds = this.service.acf.related_case_studies
      if (relatedWorkIds && typeof relatedWorkIds !== 'undefined') {
        response = await Axios.get(this.$store.getters['hostname'] + 'wp/v2/bd_case_study?' + relatedWorkIds.map((obj) => 'include[]=' + obj.ID).join('&'))
        this.relatedCaseStudies = response.data
      }
    },
    async asyncData ({store, route}) {
      let response = await store.dispatch('fetchByQuery', {path: 'wp/v2/bd_service', query: {slug: route.params.slug}})
      return {service: response.data[0]}
    }
  }
</script>
