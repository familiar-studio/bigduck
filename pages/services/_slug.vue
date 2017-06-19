<template>
  <div>
    <div v-if="service">
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
        </div>
        <Subscribe v-if="callouts" :entry="callouts[0]"></Subscribe>
        <div class="container mt-5" v-if="relatedCaseStudies && relatedCaseStudies.length > 0">
          <h2>Related Case Studies</h2>
          <Work :work="relatedCaseStudies"></Work>
        </div>
      </div>
    </div>
    <div v-else>
      Loading the service...
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
      let response = await this.$store.dispatch('fetchByQuery', {path: 'wp/v2/bd_service', query: {slug: this.slug}})
      this.service = response.data[0]
      let relatedWorkIds = this.service.acf.related_case_studies
      if (relatedWorkIds && typeof relatedWorkIds !== 'undefined') {
        response = await Axios.get(this.$store.getters['hostname'] + 'wp/v2/bd_case_study?' + relatedWorkIds.map((obj) => 'include[]=' + obj.ID).join('&'))
        this.relatedCaseStudies = response.data
      }
    }
  }
</script>
