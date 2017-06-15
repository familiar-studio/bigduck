<template>
  <div>
    <div class="jumbotron">
      <div class="container">
        <h1 class="display-2">Developing the <span class="color">{{magicWord}}</span><span class="cursor bgChange"></span><br class="hidden-lg-down"/>
        of determined nonprofits.</h1>
        <router-link class="btn btn-primary" to="/services">See how we do it &rarr;</router-link>
      </div>
    </div>
    <Featured v-for="(caseStudy, index) in relatedCaseStudies"  :work="caseStudy" :index="index" :key="index"></Featured>
    <div class="container">
      <div v-if="upcomingEvents" class="mt-5">
        <h2>Upcoming Events</h2>
        <div class="" v-for="(event, index) in upcomingEvents">
          <Event :entry="event" index="index"></Event>
        </div>
        <nuxt-link class="btn btn-primary" to="/events">View All Events</nuxt-link>
      </div>
    </div>
    <div class="testimonial my-5">
      <div class="container">
        <blockquote>
          <h3 v-html="page.acf.testimonial"></h3>
          <footer class="label">&mdash;<span v-html="page.acf.citation"></span></footer>
        </blockquote>
      </div>
    </div>
    <div class="container">
      <div v-if="latestInsights" class="mt-5">
        <h2>Latest Insights</h2>
        <div class="" v-for="(insight, index) in latestInsights">
          <Post :entry="insight" :index="index + latestInsights.length"></Post>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
  import Axios from 'axios'
  import Featured from '~components/Featured.vue'
  import Event from '~components/Event.vue'
  import Post from '~components/Post.vue'

  export default {
    components: {
      Event,
      Featured,
      Post
    },
    async asyncData ({store}) {
      let data = {}
      let response = await Axios.get(store.getters['hostname'] + 'wp/v2/pages/37')
      let page = response.data
      data['page'] = page
      if (page && page.acf) {
        let relatedWorkIds = page.acf.featured_case_studies
        if (typeof relatedWorkIds !== 'undefined' && relatedWorkIds) {
          response = await Axios.get(store.getters['hostname'] + 'wp/v2/bd_case_study?' + relatedWorkIds.map((obj) => 'include[]=' + obj.ID).join('&'))
          data['relatedCaseStudies'] = response.data
        }
        let upcomingEventIds = page.acf.upcoming_events
        if (typeof upcomingEventIds !== 'undefined' && upcomingEventIds) {
          response = await Axios.get(store.getters['hostname'] + 'wp/v2/bd_event?' + upcomingEventIds.map((obj) => 'include[]=' + obj.ID).join('&'))
          data['upcomingEvents'] = response.data
        }
        let latestInsightIds = page.acf.latest_insights
        if (typeof latestInsightIds !== 'undefined' && latestInsightIds) {
          response = await Axios.get(store.getters['hostname'] + 'wp/v2/bd_insight?' + latestInsightIds.map((obj) => 'include[]=' + obj.ID).join('&'))
          data['latestInsights'] = response.data
        }
      }
      return data
    },
    data () {
      return {
        magicWord: 'voices',
        magicIndex: 0,
        typeIndex: 0,
        typeInterval: 100,
        typeIntervalVar: {},
        wordInterval: 5000,
        magicWords: [
          'voices',
          'brands',
          'campaigns',
          'teams'
        ]
      }
    },
    computed: {
      topics () { return this.$store.state.topics },
      types () { return this.$store.state.types },
      topicsIndexedById () { return this.$store.getters['getTopicsIndexedById'] },
      typesIndexedById () { return this.$store.getters['getTypesIndexedById'] }
    },
    created () {
      setInterval(this.nextWord, this.wordInterval)
      this.typeIntervalVar = setInterval(this.nextLetter, this.typeInterval)
    },
    methods: {
      nextWord: function () {
        this.typeIndex = 0
        this.magicWord = null
        clearInterval(this.typeIntervalVar)
        this.typeIntervalVar = setInterval(this.nextLetter, this.typeInterval)
        if (this.magicIndex === this.magicWords.length - 1) {
          this.magicIndex = 0
        } else {
          this.magicIndex++
        }
      },
      nextLetter: function () {
        if (this.typeIndex < this.magicWords[this.magicIndex].length) {
          this.typeIndex++
          this.magicWord = this.magicWords[this.magicIndex].substring(0, this.typeIndex)
        } else {
          clearInterval(this.typeIntervalVar)
          this.typeIntervalVar = null

          setTimeout(() => {
            this.typeIntervalVar = setInterval(this.removeLetter, this.typeInterval)
          }, 3000)
          // pause
          // start removing letters
        }
      },
      removeLetter: function () {
        if (this.typeIndex > 0) {
          this.typeIndex--
          this.magicWord = this.magicWords[this.magicIndex].substring(0, this.typeIndex)
        } else {
          clearInterval(this.typeIntervalVar)
          this.typeIntervalVar = null
        }
      }
    }
  }
</script>
