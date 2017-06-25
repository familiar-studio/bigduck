<template>
  <div>
    <div class="img-hero" v-if="insight.acf.featured_image" :style=" { backgroundImage: 'url(' + insight.acf.featured_image + ')' }">
      <figcaption class="figure-caption">{{insight.acf.featured_image.caption}}</figcaption>
    </div>
    <div class="">
      <div class="row">
        <div class="col-lg-1 hidden-md-down">
          <Share></Share>
        </div>
        <div class="col-lg-10">
          <div class="container" :class="{'overlap':insight.acf.featured_image}">
            <article class="main">
              <div class="badge-group">
                <nuxt-link class="badge badge-default underlineChange" :to="{name: 'insights'}">
                  Insights
                </nuxt-link>
                <div class="badge badge-default" v-for="topic in insight.topic" v-if="topics">
                  <img :src="getTopicsIndexedById[topic].acf.icon">
                  <div v-html="getTopicsIndexedById[topic].name"></div>
                </div>
                <div class="badge badge-default">
                  <span v-if="types && insight.type[0]">
                    <span v-if="getTypesIndexedById[insight.type[0]].verb == 'Read' && insight.calculated_reading_time">
                      {{insight.calculated_reading_time.data}}
                    </span>
                    <span v-else>
                      {{ insight.acf.time }} {{ insight.acf.time_interval }}
                    </span>
                    {{ getTypesIndexedById[insight.type[0]].verb }}
                  </span>
                </div>
                <div class="badge badge-default">
                  {{ date }}
                </div>
              </div>
  
              <h1 v-html="insight.title.rendered"></h1>
              <div class="badge badge-default mb-3" v-if="insight.author">
                <img v-if="insight.author_headshot.data.sizes" :src="insight.author_headshot.data.sizes.thumbnail" class="round author-img mr-2">
                <div v-if="!insight.acf.is_guest_author" v-html="insight.acf.author.display_name"></div>
                <div v-if="insight.acf.is_guest_author && insight.acf.author" v-html="entry.acf.author.display_name"></div>
              </div>
  
              <div v-for="block in insight.acf.body" :class="['block-' + block.acf_fc_layout]">
                <div v-if="block.acf_fc_layout == 'text'" v-html="block.text"></div>
                <template v-if="block.acf_fc_layout == 'callout'">
                  <div v-html="block.text">
                  </div>
                  <img :src="block.image" alt="callout image" v-if="block.image" />
                </template>
              </div>
              <div class="hidden-lg-up mt-4">
                <Share></Share>
              </div>
            </article>
  
            <div v-if="formId" class="form-light">
              <GravityForm :formId="formId" :viewAll="true" :gatedContent="true" @submitted="refreshContent()"></GravityForm>
            </div>
  
            <article class="main" v-if="formId === false && insight.content.rendered">
              <div v-html="insight.content.rendered"></div>
            </article>
  
            <article class="mb-5 container" v-if="author && insight.author.acf ">
              <div class="author-bio">
                <div class="row">
                  <div class="col-md-2 author-bio-pic">
                    <img class="round" v-if="insight.author_headshot.data.sizes" :src="insight.author_headshot.data.sizes.thumbnail" alt="" />
                  </div>
                  <div class="col-md-10 author-bio-text">
                    <h3>
                      {{insight.acf.author.display_name}} is {{ prependIndefiniteArticle(author.acf.job_title) }} at Big Duck
                    </h3>
                    <nuxt-link class="btn btn-primary" :to="{name: 'about-slug', params: { slug: insight.acf.author.user_nicename}}">
                      More about {{insight.acf.author.user_firstname}}
                    </nuxt-link>
                  </div>
                </div>
              </div>
  
            </article>
  
            <div class="mb-5" v-if="relatedCaseStudies">
              <h2>Related Case Studies</h2>
              <div class="row">
                <div v-for="case_study in relatedCaseStudies" class="col-md-6">
                  <nuxt-link :to="{name: 'work-slug', params: {slug: case_study.slug}}" :key="case_study.ID">
                    <img v-if="case_study.acf.hero_image" :src="case_study.acf.hero_image.sizes.large" style="width:100%">
                    <div class="card two-up-card mx-4">
                      <div class="card-header" v-if="topics && types">
                        <div class="badge badge-default" v-for="topic in case_study.topic">
                          <div v-html="getTopicsIndexedById[topic].icon.data"></div>
                          <div v-html="getTopicsIndexedById[topic].name"></div>
                        </div>
                      </div>
                      <div class="card-block py-0">
                        <h3 class="card-title">{{ case_study.title.rendered }}</h3>
                        <p class="card-text" v-html="case_study.acf.short_description"></p>
                      </div>
                    </div>
                  </nuxt-link>
  
                </div>
              </div>
            </div>
  
            <div class="mb-5" v-if="relatedInsights">
              <h2>Related Insights</h2>
              <div v-if="relatedInsights">
                <div v-for="(insight, index) in relatedInsights">
                  <Post :entry="insight" :index="index"></Post>
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
import dateFns from 'date-fns'
import GravityForm from '~components/GravityForm.vue'
import { mapState, mapGetters, mapActions } from 'vuex'
import Post from '~components/Post.vue'
import Share from '~components/Share.vue'

export default {
  name: 'insight',
  components: {
    Share,
    GravityForm,
    Post
  },
  data() {
    return {
      relatedCaseStudies: null,
      relatedInsights: null,
      author: null
    }
  },
  async asyncData({ state, params, store }) {
    let data = {}
    let response = await Axios.get(store.getters['hostname'] + 'wp/v2/bd_insight', { params: { slug: params.slug } })
    data.insight = response.data[0]
    if (data.insight.acf.related_case_studies) {
      data.relatedWorkIds = data.insight.acf.related_case_studies.map((caseStudy) => { return caseStudy.ID })
    }
    if (data.insight.acf.related_insights) {
      data.relatedInsightIds = data.insight.acf.related_insights.map((insight) => { return insight.ID })
    }
    return data
  },
  head() {
    return {
      title: this.insight.title.rendered ? this.insight.title.rendered : null,
      meta: [
        { description: 'Overview' },
        { 'og:image': this.insight.acf.featured_image }
      ]
    }
  },
  computed: {
    ...mapState(['types', 'topics']),
    ...mapGetters(['hostname', 'getTopicsIndexedById', 'getTypesIndexedById']),
    date() {
      return dateFns.format(this.insight.date, 'MMM D, YYYY')
    },
    formId() {
      if (process.BROWSER_BUILD) {
        var parser = new DOMParser()
        var doc = parser.parseFromString(this.insight.content.rendered, 'text/html')
        var formId = doc.getElementById('form-id')
        if (formId) {
          return Number(formId.innerHTML)
        } else {
          return false
        }
      }
      return null
    }
  },
  created() {
    // get related case studies
    if (this.relatedWorkIds) {
      Axios.get(this.hostname + 'wp/v2/bd_case_study', { params: { include: this.relatedWorkIds } }).then((response) => {
        this.relatedCaseStudies = response.data
      })
    }
    if (this.relatedInsightIds) {
      Axios.get(this.hostname + 'wp/v2/bd_insight', { params: { include: this.relatedInsightIds } }).then((response) => {
        this.relatedInsights = response.data
      })
    }
    if (!this.insight.is_guest_author) {
      Axios.get(this.hostname + 'acf/v3/users/' + this.insight.acf.author.ID).then((response) => {
        this.author = response.data
      })
    }
  },
  // mounted if form exists in dom mounted then change action
  methods: {
    ...mapActions(['fetch']),
    prependIndefiniteArticle(word) {
      if (word) {
        if ('aeiou'.indexOf(word.split('')[0].toLowerCase()) > -1) {
          return 'an ' + word
        } else {
          return 'a ' + word
        }
      }
      return null
    },
    async refreshContent() {
      let response = await Axios.get(this.hostname + 'wp/v2/bd_insight/', { query: { slug: this.$route.params.slug } })
      this.insight = response.data
    }

  }
}
</script>
