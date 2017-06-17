<template>
  <div>
    <div class="img-hero" :style=" { backgroundImage: 'url(' + insight.acf.featured_image + ')' }">
    </div>
    <div class="container-fluid">
      <div class="row">
        <div class="col-xl-8 col-lg-9 offset-lg-2">
          <share></share>

          <div class="container bg-white overlap">
            <article class="main">
              <div class="badge-group">
                <router-link class="badge badge-default underlineChange" :to="{name: 'insights'}">Insights</router-link>
                <div class="badge badge-default" v-for="topic in insight.topic" v-if="topics">
                  <img :src="getTopicsIndexedById[topic].acf.icon">
                  <div v-html="getTopicsIndexedById[topic].name"></div>
                </div>
                <div class="badge badge-default" v-if="insight.acf.time && types">
                  {{insight.acf.time}} {{insight.acf.time_interval}} {{ getTypesIndexedById[insight.type[0]].verb }}
                </div>
                <div class="badge badge-default" >
                  <!-- {{insight.date | formatDate('MMM Do YYYY')}} -->
                </div>
              </div>

              <h1 v-html="insight.title.rendered"></h1>
              <div class="badge badge-default mb-3" v-for="type in insight.type" v-if="types">
                  <img :src="insight.author_headshot.sizes.thumbnail" class="round author-img">
                  <div v-html="insight.acf.author.display_name"></div>
              </div>

              <div v-for="block in insight.acf.body" :class="['block-' + block.acf_fc_layout]">
                <div v-if="block.acf_fc_layout == 'text'" v-html="block.text"></div>
                <template v-if="block.acf_fc_layout == 'callout'">
                  <div v-html="block.text">
                  </div>
                  <img :src="block.image" alt="callout image" v-if="block.image" />
                </template>
            </div>
            </article>
            <div v-if="author && author.acf">
              <div class="author-bio">
                <div class="media">
                  <img class="round" :src="author.acf.headshot.sizes.thumbnail" alt="" />
                  <div class="media-body">
                    <h3>{{insight.acf.author.display_name}} {{insight.acf.author.user_lastname}} is
                        {{ prependIndefiniteArticle(author.acf.job_title) }}
                        {{  author.acf.job_title }} at Big Duck</h3>
                    <router-link class="btn btn-primary" :to="{name: 'team-slug', params: { slug: insight.acf.author.user_nicename}}">More about {{insight.acf.author.user_firstname}}</router-link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-2">
        </div>

      </div>

      <div class="container mt-5" v-if="relatedCaseStudies">
        <h2>Related Case Studies</h2>
        <div class="row">
          <div v-for="case_study in relatedCaseStudies" class="col-md-6">
            <router-link :to="{name: 'work-id', params: {id: case_study.ID}}" :key="case_study.ID">
              <!-- {{caseStudiesById[case_study.ID].acf.hero_image.sizes.large}} -->
              <img :src="case_study.acf.hero_image.sizes.large" style="width:100%">
              <!-- <div v-if="caseStudiesById[case_study.ID].acf.hero_image">
              </div> -->
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
            </router-link>

          </div>
        </div>
      </div>
    </div>
  </div>

</template>
<script>
  import Share from '~components/Share.vue'
  import moment from 'moment'
  import { mapState, mapGetters, mapActions } from 'vuex'
  import Axios from 'axios'

  export default {
    name: 'insight',
    components: {
      Share
    },
    data () {
      return {
        form: null,
        relatedCaseStudies: null,
        author: null
      }
    },
    async asyncData ({state, params, store}) {
      let data = {}
      let response = await Axios.get(store.getters['hostname'] + 'wp/v2/bd_insight/' + params.id)
      data.insight = response.data
      if (data.insight.acf) {
        data.relatedWorkIds = data.insight.acf.related_case_studies
      }
      return data
    },
    head: {
      title: this.insight ? this.insight.title.rendered : null
    },
    computed: {
      ...mapState(['types', 'topics']),
      ...mapGetters(['hostname', 'getTopicsIndexedById', 'getTypesIndexedById']),
      date () {
        return moment(this.insight.date).format('MMM Do YYYY')
      }
    },
    created () {
      // get related case studies
      if (this.relatedWorkIds) {
        Axios.get(this.hostname + 'wp/v2/bd_case_study', { params: { includes: this.relatedWorkIds } }).then((response) => {
          this.relatedCaseStudies = response.data
        })
      }
      // get author info
      Axios.get(this.hostname + 'acf/v3/users/' + this.insight.acf.author.ID).then((response) => {
        this.author = response.data
      })
    },
    // mounted if form exists in dom mounted then change action
    methods: {
      prependIndefiniteArticle (word) {
        if ('aeiou'.indexOf(word.split('')[0].toLowerCase()) > -1) {
          return 'an ' + word
        } else {
          return 'a ' + word
        }
      }
    }
  }
</script>
