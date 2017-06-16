<template>
  <div class="">
    <div v-if="insight">
      <transition name="fade" appear>
        <div class="img-hero" :style=" { backgroundImage: 'url(' + insight.acf.featured_image + ')' }">
        </div>
      </transition>
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-2">

          </div>

          <div class="col-md-8">
            <share></share>
            <transition name="slideUp" appear>
              <div class="container bg-white overlap">
                <article class="main">
                  <div class="badge-group">
                    <router-link class="badge badge-default underlineChange" :to="{name: 'insights'}">Insights</router-link>
                    <div class="badge badge-default" v-for="topic in insight.topic" v-if="topics">
                      <img :src="topicsIndexedById[topic].acf.icon">
                      <div v-html="topicsIndexedById[topic].name"></div>
                    </div>
                    <div class="badge badge-default" v-if="insight.acf.time && types">
                      {{insight.acf.time}} {{insight.acf.time_interval}} {{ typesIndexedById[insight.type[0]].verb }}
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
                          <p class="card-text" v-html="author.acf.bio"></p>
                        <router-link class="btn btn-primary" :to="{name: 'team-slug', params: { slug: insight.acf.author.user_nicename}}">More about {{insight.acf.author.user_firstname}}</router-link>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </transition>
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
          </div>
        </div>
      </div>
    </div>

    <div v-else>
      Loading insight...
    </div>
  </div>
</template>
<script>
  import Share from '../../components/Share.vue'
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
        form: null
      }
    },
    async asyncData ({state, params, store}) {
      let data = {}
      let response = await store.dispatch('fetchOne', {path: 'wp/v2/bd_insight', id: params.id})
      let insight = response
      data['insight'] = insight
      let body = insight.acf.body[0].text
      let relatedWorkIds = insight.acf.related_case_studies
      if (typeof relatedWorkIds !== 'undefined' && relatedWorkIds) {
        response = await Axios.get(store.getters['hostname'] + 'wp/v2/bd_case_study?' + relatedWorkIds.map((obj) => 'include[]=' + obj.ID).join('&'))
        data['relatedCaseStudies'] = response.data
      }
      if (insight.acf.author) {
        response = await Axios.get(store.getters['hostname'] + 'acf/v3/users/' + insight.acf.author.ID)
        data['author'] = response.data
      }
      return data
    },
    head () {
      return {
        title: this.insight.title.rendered + ' | Big Duck'
      }
    },
    computed: {
      ...mapState(['types', 'topics']),
      ...mapGetters(['bareHostname']),
      topicsIndexedById () {
        return this.$store.getters['getTopicsIndexedById']
      },
      typesIndexedById () {
        return this.$store.getters['getTypesIndexedById']
      },
      date () {
        return moment(this.insight.date).format('MMM Do YYYY')
      },
      id () {
        return this.$route.params.id
      }
    },
    async created () {
      // this.form = form
      let response = await this.$store.dispatch('fetchOne', {path: 'wp/v2/bd_insight', id: this.id})
      this.insight = response
      let body = this.insight.acf.body[0].text
      let relatedWorkIds = this.insight.acf.related_case_studies
      if (typeof relatedWorkIds !== 'undefined' && relatedWorkIds) {
        response = await Axios.get(this.$store.getters['hostname'] + 'wp/v2/bd_case_study?' + relatedWorkIds.map((obj) => 'include[]=' + obj.ID).join('&'))
        this.relatedCaseStudies = response.data
      }
      if (this.insight.acf.author) {
        response = await Axios.get(this.$store.getters['hostname'] + 'acf/v3/users/' + this.insight.acf.author.ID)
        this.author = response.data
      }
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
    },
    mounted () {
      // $('form[id^="gform_"]').action(this.$store.getters['bareHostname'])
      let form = document.querySelector('form[id^="gform_"]')
      // let form = document.querySelector('form')
      console.log(form)
      if (form) {
        form.action = this.$store.getters['bareHostname']
        console.log(form)
      }
    }
  }
</script>
