<template>
  <div>
  <div v-if="service">
    <transition name="fade" appear>
      <div class="img-hero" :style=" { backgroundImage: 'url(' + service.acf.featured_image.url + ')' }"></div>
    </transition>
    <div class="container bg-white overlap mt-5">
      <article class="main">
      <h2>{{ service.title.rendered }}</h2>
      <div v-html="service.acf.introduction"></div>
      </article>
    </div>
    <div class="container overlap mt-5">
      <div v-for="block in service.acf.service_body">
        <div v-if="block.acf_fc_layout == 'text'" v-html="block.text"></div>
        <div v-if="block.acf_fc_layout == 'image'">
          <img :src="block.image.url" />
        </div>
        <h2 v-if="block.acf_fc_layout == 'heading'" class="service-heading" v-html="block.heading"></h2>
      </div>
        <Subscribe v-if="callouts" :entry="callouts[0]"></Subscribe>
        <blockquote v-if="service.acf.testimonial" v-html="acf.testimonial.text"></blockquote>
    </div>
    <div class="container overlap mt-5" v-if="relatedCaseStudies && relatedCaseStudies.length > 0">
      <h2>Related Case Studies</h2>
      <Work :work="relatedCaseStudies"></Work>
      <!-- <div class="row">
        <div v-for="case_study in relatedCaseStudies" class="col-md-6">
          <router-link :to="{name: 'work-id', params: {id: case_study.id}}" :key="case_study.ID">
            <img :src="case_study.acf.hero_image.sizes.large" style="width:100%">
            <div class="card two-up-card mx-4">
              <div class="card-header" v-if="topics">
                <div class="badge badge-default" v-for="topic in case_study.topic">
                    <div v-html="topicsIndexedById[topic].icon.data"></div>
                    <div v-html="topicsIndexedById[topic].name"></div>
                </div>
              </div>
              <div class="card-block py-0">
                <h3 class="card-title">{{ case_study.title.rendered }}</h3>
               <p class="card-text" v-html="case_study.acf.short_description"></p>
              </div>
            </div>
          </router-link>
        </div>
      </div> -->
    </div>
    <div class="container overlap mt-5" v-if="relatedInsights && relatedInsights.length > 0">
      <h2>Related Insights</h2>
      <div class="row">
        <div v-for="insight in relatedInsights" class="col-md-6">
          <router-link :to="{name: 'Insight', params: {id: insight.ID}}" :key="insight.ID">
            <!-- {{caseStudiesById[case_study.ID].acf.hero_image.sizes.large}} -->
            <img :src="insight.acf.hero_image.sizes.large" style="width:100%">
            <!-- <div v-if="caseStudiesById[case_study.ID].acf.hero_image">
            </div> -->
            <div class="card two-up-card mx-4">
              <div class="card-header" v-if="topics">
                <div class="badge badge-default" v-for="topic in insight.topic">
                    <div v-html="topicsIndexedById[topic].icon.data"></div>
                    <div v-html="topicsIndexedById[topic].name"></div>
                </div>
              </div>
              <div class="card-block py-0">
                <h3 class="card-title">{{ insight.title.rendered }}</h3>
               <p class="card-text" v-html="insight.acf.short_description"></p>
              </div>
            </div>
          </router-link>

        </div>
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
      let relatedInsightIds = this.service.acf.related_insights
      if (typeof relatedInsightIds !== 'undefined') {
        response = await Axios.get(this.$store.getters['hostname'] + 'wp/v2/bd_insight?' + relatedInsightIds.map((obj) => 'include[]=' + obj.ID).join('&'))
        this.relatedInsights = response.data
      }
    }
  }
</script>
