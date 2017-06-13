<template>
  <div>
    <div v-if="page">


  <div class="container  no-hero mb-5">

    <article class="main">

    <h1>Developing the <span class="color">voices</span> of <span class="bg-white">determined</span> nonprofits.</h1>

    </article>
  </div>
   <Featured v-for="(caseStudy, index) in relatedCaseStudies"  :entry="caseStudy" :index="index" :key="index"></Featured>
  <div class="container">


  <div v-if="upcomingEvents">
    <h2>Upcoming Events</h2>
    <div class="" v-for="(event, index) in upcomingEvents">
      <Event :entry="event" index="index"></Event>
    </div>
    <nuxt-link class="btn btn-primary" to="/events">View All Events</nuxt-link>
  </div>
  <!-- <blockquote class="mt-5">
    <p v-html="page.acf.testimonial"></p>
    <footer><span v-html="page.acf.citation"></span></footer>
  </blockquote> -->
  <!-- <div v-if="latestInsights && types && topics" class="mt-5">
    <h2>Latest Insights</h2>
    <div class="" v-for="(insight, index) in latestInsights">
      <Post :entry="insight" :index="index + latestInsights.length"></Post>
    </div>
  </div> -->
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
    fetch ({state, params}) {
      if (!topics) {
        state.store.commit('fetchTopics')
      }
    },
    async asyncData () {
      let data = {}
      let response = await Axios.get(store.getters['hostname'] + 'wp/v2/pages/37')
      let page = response.data
      data['page'] = page
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
      return data
    },
    computed: {
      topics () { return this.$store.state.topics },
      types () { return this.$store.state.types },
      topicsIndexedById () { return this.$store.getters['getTopicsIndexedById'] },
      typesIndexedById () { return this.$store.getters['getTypesIndexedById'] }
    }
  }
</script>
