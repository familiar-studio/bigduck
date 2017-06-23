<template>
  <div>
    <div class="jumbotron">
      <div class="container">
        <h1 class="display-2">Developing the <span class="color">{{wordString}}</span><span class="cursor bgChange"></span><br class="hidden-lg-down"/>
        of determined nonprofits.</h1>
        <router-link class="btn btn-primary" to="/services">See how we do it &rarr;</router-link>
      </div>
    </div>
    <Featured v-for="(caseStudy, index) in relatedCaseStudies"  :work="caseStudy" :index="index" :key="index"></Featured>
    <div class="testimonial mb-5">
      <div class="container">
        <blockquote>
          <h3 v-html="page.acf.testimonial"></h3>
          <footer class="label">&mdash;<span v-html="page.acf.citation"></span></footer>
        </blockquote>
      </div>
    </div>
    <div class="container">
      <div v-if="upcomingEvents" class="mt-5">
        <h2>Featured Events</h2>

        <div class="" v-for="(event, index) in upcomingEvents">
          <Event :entry="event" index="index" :relatedTeamMembers="event.related_team_members.data"></Event>
        </div>
        <nuxt-link class="btn btn-primary" to="/events">View All Events</nuxt-link>
      </div>
    </div>

    <div class="container">
      <div v-if="latestInsights" class="my-5">
      <h2>Latest Insights</h2>

        <div class="" v-for="(insight, index) in latestInsights">
          <Post :entry="insight" :index="index + latestInsights.length"></Post>
        </div>
        <nuxt-link class="btn btn-primary" to="/insights">View All Insights</nuxt-link>
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
    head () {
      return {
        title: 'Big Duck',
        titleTemplate: null,
        meta: [
          { description: 'Developing the voices of determined nonprofits.' },
          { 'og:image': '' }
        ]
      }
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
        wordString: 'voices',
        words: [
          'voices',
          'brands',
          'campaigns',
          'teams'
        ],
        wordIndex: 0,
        letterIndex: 6,
        timeWaited: 0,
        interval: null,
        typingStatus: 'waiting',
        looped: false,
        // same as color animation
        totalWordTypeTime: 5000,

        // ANIMATION OPTIONS:
        // how many milliseconds a 'frame' lasts. a letter is typed or deleted in 1 frame
        frameInterval: 100,
        // how many frames to wait after a word has been typed before starting to delete it
        // setting loop to false will stop typing after word returns to 'voices'
        loop: true
      }
    },
    computed: {
      topics () { return this.$store.state.topics },
      types () { return this.$store.state.types },
      topicsIndexedById () { return this.$store.getters['getTopicsIndexedById'] },
      typesIndexedById () { return this.$store.getters['getTypesIndexedById'] },
      word () { return this.words[this.wordIndex] },
      waitingIntervalFrames () {
        // the number of letters to typed and deleted, one frame to change word, times the let of a frame
        return this.totalWordTypeTime - this.frameInterval * ((2 * this.word.length) + 1)
      }
    },
    created () {
      this.interval = setInterval(this.nextFrame, this.frameInterval)
    },
    methods: {
      nextFrame () {
        switch (this.typingStatus) {
          case 'typing':
            if (this.letterIndex < this.words[this.wordIndex].length) {
              this.letterIndex++
            } else {
              this.typingStatus = 'waiting'
              this.timeWaited = 0
            }
            break
          case 'waiting':
            if (this.timeWaited < this.waitingIntervalFrames) {
              this.timeWaited += this.frameInterval
            } else if (this.loop || !this.looped) {
              this.typingStatus = 'deleting'
            }
            break
          case 'deleting':
            if (this.letterIndex > 0) {
              this.letterIndex--
            } else {
              this.typingStatus = 'changingWord'
            }
            break
          default:
            this.wordIndex = (this.wordIndex + 1) % (this.words.length)
            this.looped = this.wordIndex === 0
            this.typingStatus = 'typing'
            break
        }
        this.wordString = this.word.substring(0, this.letterIndex)
      }
    }
  }
</script>
