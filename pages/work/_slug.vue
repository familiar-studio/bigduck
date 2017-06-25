<template>
  <div>
    <div class="img-hero" :style=" { backgroundImage: 'url(' + caseStudy.acf.hero_image.url + ')' }">
      <figcaption class="figure-caption">{{caseStudy.acf.hero_image.caption}}</figcaption>
    </div>
    <div>
      <div class="row">
        <div class="col-lg-1 hidden-md-down">
          <share></share>
        </div>
        <div class="col-lg-10">
          <div class="container overlap ">
            <article class="main overflow-x-hidden" id="content">
              <div class="badge-group">
                <nuxt-link class="badge badge-default underlineChange" :to="{name: 'work'}">Work</nuxt-link>
                <div class="badge badge-default" v-html="caseStudy.acf.client_name"></div>
              </div>
  
              <h1>{{ caseStudy.title.rendered }}</h1>
              <div class="badge-group" v-if="topics">
                <div class="badge badge-default" v-for="topic in caseStudy.topic">
                  <div v-html="topicsIndexedById[topic].icon"></div>
                  <div v-html="topicsIndexedById[topic].name"></div>
                </div>
                <div class="badge badge-default" v-for="sector in caseStudy.sector">
                  <div v-html="sectorsIndexedById[sector].icon"></div>
                  <div v-html="sectorsIndexedById[sector].name"></div>
                </div>
              </div>
  
              <div class="row cs-intro">
                <div class="col-lg-9">
                  <h5 v-html="caseStudy.acf.introduction"></h5>
                  <div v-html="caseStudy.acf.article_text"></div>
  
                </div>
                <div class="col-lg-3">
                  <aside>
                    <h6>Services Provided</h6>
                    <ul class="list-unstyled service-list">
                      <li v-for="service in caseStudy.acf.services_provided" class="label">{{ service.service }}</li>
                    </ul>
                  </aside>
                  <div class="hidden-lg-up mt-4">
                    <share></share>
                  </div>
                </div>
              </div>
            </article>
  
            <div v-for="(block, index) in caseStudy.acf.body">
  
              <!-- TEXT  -->
              <div v-if="block.acf_fc_layout == 'text'" class="cs-block-text">
                <h2 class="mb-3 mt-5" v-html="block.heading"></h2>
                <div v-html="block.text"></div>
              </div>
  
              <!-- GALLERY  -->
              <div v-if="block.acf_fc_layout == 'gallery'" class="cs-block-gallery break-container">
                <div class="container">
                  <flickity :images="block.gallery"></flickity>
                </div>
              </div>
  
              <!-- CALLOUT  -->
              <div v-if="block.acf_fc_layout == 'callout'" class="cs-block-callout break-container" :style="{ backgroundColor: caseStudy.acf.primary_color }">
                <div class="row">
                  <div class="col-md-6 push-md-6 col-img">
                    <div :style=" { backgroundImage: 'url(' + block.image.url + ')' }" class="bg-img"></div>
                  </div>
                  <div class="col-md-6 pull-md-6 col-text">
                    <h2>{{ block.headline }}</h2>
                    <div v-html="block.text"></div>
                    <a :href="block.website" v-if="block.website" class="btn btn-info" target="_blank">Visit Site</a>
                  </div>
                </div>
              </div>
  
              <!-- TESTIMONIAL -->
              <div v-if="block.acf_fc_layout == 'testimonial'" class="cs-block-testimonial testimonial break-container">
                <div class="row">
                  <div class="col-md-8">
                    <blockquote>
                      <h3 v-html="block.quote"></h3>
                      <footer class="label">&mdash; {{ block.credit }}</footer>
                    </blockquote>
                  </div>
                  <div v-if="block.image" class="col-md-4">
                    <img :src="block.image.sizes.cropped_400_square" alt="block.image.name" class="img-fluid">
                  </div>
                </div>
              </div>
  
              <!-- FACTOID -->
              <div v-if="block.acf_fc_layout == 'factoid'" class="cs-block-factoid">
                <h1 class="display-1">{{block.number}}</h1>
                <h6 v-html="block.description"></h6>
              </div>
            </div>
  
            <h2 class="mb-3 mt-5">Related Service</h2>
            <div v-if="relatedService">
              <Service :entry="relatedService"></Service>
            </div>
  
            <div class="callout-fullwidth text-white color bgChange">
              <div class="container">
                <div class="row">
                  <div class="col-lg-8 offset-lg-2">
                    <h3>{{ caseStudy.acf.cta_text }}</h3>
                    <GravityForm :formId="formId" :showAll="true"></GravityForm>
                  </div>
                </div>
              </div>
            </div>
  
          </div>
  
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import Axios from 'axios'
import GravityForm from '~components/GravityForm.vue'
import Service from '~components/Service.vue'
import share from '~components/Share.vue'
import Work from '~components/Work.vue'
import { mapState, mapGetters } from 'vuex'
import flickity from '~components/Flickity.vue'

export default {
  name: 'case_study',
  head() {
    return {
      title: this.caseStudy.title.rendered,
      meta: [
        { description: this.caseStudy.client_name },
        { 'og:image': this.caseStudy.acf.hero_image.url }
      ]
    }
  },
  data() {
    return {
      caseStudy: null,
      relatedService: null,
      formId: 5
    }
  },
  components: {
    GravityForm,
    flickity,
    Service,
    share,
    Work
  },
  computed: {
    ...mapState([
      'callouts',
      'topics',
      'types'
    ]),
    ...mapGetters(['hostname']),
    id() {
      return this.$route.params.id
    },
    topicsIndexedById() {
      return this.$store.getters['getTopicsIndexedById']
    },
    sectorsIndexedById() {
      return this.$store.getters['getSectorsIndexedById']
    }
  },
  methods: {
    next() {
      this.$refs.flickity.next()
    },
    previous() {
      this.$refs.flickity.previous()
    }
  },
  async asyncData({ params, store }) {
    let data = {}
    let response = await Axios.get(store.getters['hostname'] + 'wp/v2/bd_case_study?slug=' + params.slug)
    console.log(store.getters['hostname'] + 'wp/v2/bd_case_study?slug=' + params.slug)
    data['caseStudy'] = response.data[0]
    return data
  },
  async created() {
    let topics = this.caseStudy.topic
    let related = await Axios.get(this.hostname + 'wp/v2/bd_service?topic=' + topics[0])
    this.relatedService = related.data[0]
    // let relatedWorkIds = this.caseStudy.acf.related_case_studies
    // if (typeof relatedWorkIds !== 'undefined' && relatedWorkIds) {
    //   let response = await Axios.get(this.$store.getters['hostname'] + 'wp/v2/bd_case_study?' + relatedWorkIds.map((obj) => 'include[]=' + obj.ID).join('&'))
    //   this.relatedCaseStudies = response.data
    // }
  }
}
</script>
