<template>
  <div class="services-index">
    <div class="img-hero" :style=" { backgroundImage: 'url(' + service.acf.featured_image.url + ')' }">
      <figcaption class="figure-caption">{{service.acf.featured_image.caption}}</figcaption>
    </div>
    <div id="content">
      <div class="container">
        <article class="main bg-white overlap mb-5">
          <div class="badge-group">
            <router-link class="badge badge-default underline-change overview-link" :to="{name: 'services'}">Services</router-link>
          </div>
          <h1>{{ service.title.rendered }}</h1>
          <div v-html="service.acf.introduction"></div>
        </article>

        <div v-for="block in service.acf.service_body">
          <div v-if="block.acf_fc_layout == 'text'" v-html="block.text" class="mb-5 block-text"></div>
          <h2 v-if="block.acf_fc_layout == 'heading'" class="mt-5" v-html="block.heading"></h2>
          <div v-if="block.acf_fc_layout == 'faq' && block.questions.length > 0">
            <FAQ :questions="block.questions"></FAQ>
          </div>

          <div class="" v-if="block.acf_fc_layout == 'image'" class="mb-5">
            <img :src="block.image.url" style="width: 100%;">
            <figcaption v-if="block.caption" class="figure-caption mt-1">{{ block.caption }}</figcaption>
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

          <!-- CALLOUT  -->
          <div v-if="block.acf_fc_layout == 'callout'" class="cs-block-callout break-container" :style="{ backgroundColor: service.acf.primary_color }">
            <div class="row">
              <div class="col-md-6 col-img">
                <div :style=" { backgroundImage: 'url(' + block.image.url + ')' }" class="bg-img"></div>
              </div>
              <div class="col-md-6 col-text">
                <h2 v-html="block.headline"></h2>
                <div v-html="block.text"></div>
                <a :href="block.website" v-if="block.website" class="btn btn-info" target="_blank">Visit Site</a>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-5" v-if="relatedCaseStudies && relatedCaseStudies.length > 0">
          <h2>Related Case Studies</h2>
          <Work :work="relatedCaseStudies"></Work>
        </div>

        <div class="callout-fullwidth text-white color bg-change">
          <div class="row">
            <div class="col-lg-10 offset-lg-1">
              <GravityForm v-if="service.acf.form" :formId="service.acf.form" :heading="service.acf.cta_text" :showAll="true" btnType="info"></GravityForm>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>
<script>
import Axios from 'axios'
import ColorCallout from '~/components/ColorCallout.vue'
import FAQ from '~/components/FAQ.vue'
import GravityForm from '~/components/GravityForm.vue'
import Work from '~/components/Work.vue'
import Post from '~/components/Post.vue'

export default {
  name: 'service',
  head() {
    if (this.service) {
      return {
        title: this.service.title.rendered,
        meta: [
          {
            'property': 'og:title',
            'content': this.service.title.rendered
          },
          {
            'property': 'twitter:title',
            'content': this.service.title.rendered
          },
          {
            'property': 'description',
            'content': this.service.acf.introduction
          },
          {
            'property': 'og:description',
            'content': this.service.acf.introduction
          },
          {
            'property': 'twitter:description',
            'content': this.service.acf.introduction
          },
          {
            'property': 'image',
            'content': this.service.acf.featured_image.url
          },
          {
            'property': 'og:image:url',
            'content': this.service.acf.featured_image.url
          },
          {
            'property': 'twitter:image',
            'content': this.service.acf.featured_image.url
          }
        ]
      }
    }
  },
  data() {
    return {
      service: null,
      relatedCaseStudies: null,
      relatedInsights: null
    }
  },
  computed: {
    slug() {
      return this.$route.params.slug
    },
    callouts() {
      return this.$store.state.callouts
    },
    topics() {
      return this.$store.state.topics
    },
    types() {
      return this.$store.state.types
    },
    topicsIndexedById() {
      return this.$store.getters['getTopicsIndexedById']
    },
    typesIndexedById() {
      return this.$store.getters['getTypesIndexedById']
    }
  },
  components: {
    Work, Post, GravityForm, FAQ
  },
  async created() {
    let relatedWorkIds = this.service.acf.related_case_studies
    if (relatedWorkIds && typeof relatedWorkIds !== 'undefined') {
      let response = await Axios.get(this.$store.getters['hostname'] + 'wp/v2/bd_case_study?' + relatedWorkIds.map((obj) => 'include[]=' + obj.ID).join('&'))
      this.relatedCaseStudies = response.data
    }
  },
  async asyncData({ store, route }) {
    let response = await store.dispatch('fetchByQuery', { path: 'wp/v2/bd_service', query: { slug: route.params.slug } })
    return { service: response.data[0] }
  }
}
</script>
