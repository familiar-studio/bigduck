<template lang="html">
  <div class="team-member">
    <div class="img-hero" :style=" { backgroundImage: 'url(' + member.headshot.url + ')' }">
      <figcaption class="figure-caption">{{member.headshot.caption}}</figcaption>
    </div>
    <div class="container overlap" id="content">
      <article class="main">
        <div class="badge badge-default">
          Team
        </div>
        <div class="row">
          <div class="col-md-9">
            <h1 class="mt-4 mb-1">{{member.name}}</h1>
            <h4>{{member.job_title}}</h4>
            <div class="mt-3" v-html="member.bio"></div>
          </div>
          <div class="col-md-3">
            <div class="mt-5 social-handle">
            <a :href="'http://twitter.com/' + member.twitter_handle">
            <div v-if="member.twitter_handle" class="badge badge-default">
                <div>
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M23.444 4.834c-.814.363-1.5.375-2.228.016.938-.562.981-.957 1.32-2.019-.878.521-1.851.9-2.886 1.104-.827-.882-2.008-1.435-3.315-1.435-2.51 0-4.544 2.036-4.544 4.544 0 .356.04.703.117 1.036-3.776-.189-7.125-1.998-9.366-4.748-.391.671-.615 1.452-.615 2.285 0 1.577.803 2.967 2.021 3.782-.745-.024-1.445-.228-2.057-.568l-.001.057c0 2.202 1.566 4.038 3.646 4.456-.666.181-1.368.209-2.053.079.579 1.804 2.257 3.118 4.245 3.155-1.945 1.524-4.356 2.159-6.728 1.881 2.012 1.289 4.399 2.041 6.966 2.041 8.358 0 12.928-6.924 12.928-12.929l-.012-.588c.887-.64 1.953-1.237 2.562-2.149z"/></svg>
                </div>
                <div v-html="member.twitter_handle"></div>
            </div>
              </a>
              <a :href="'http://facebook.com/' + member.facebook_page">
            <div v-if="member.facebook_page" class="badge badge-default">
                <div>
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M18.768 7.465h-4.268v-1.905c0-.896.594-1.105 1.012-1.105h2.988v-3.942l-4.329-.013c-3.927 0-4.671 2.938-4.671 4.82v2.145h-3v4h3v12h5v-12h3.851l.417-4z"/></svg>
                </div>
                <div v-html="member.facebook_page"></div>
            </div>
              </a>
          </div>
          <aside class="mt-5">
            <h4>{{ member.additional_info_heading_1 }}</h4>
            <div v-html="member.additional_info_body_1" class="mb-4"></div>
            <h4>{{ member.additional_info_heading_2 }}</h4>
            <div v-html="member.additional_info_body_2"></div>
          </aside>
          </div>
        </div>
      </article>

      <div v-if="this.relatedEvents && this.relatedEvents.length > 0">
        <h2>Events with {{member.name.split(" ")[0]}}</h2>
        <Event v-for="(event, index) in this.relatedEvents" :entry="event" :key="event.id" :index="index"></Event>
      </div>
      <div class="" v-if="this.relatedInsights && this.relatedInsights.length > 0">
        <h2>Insights by {{ member.name.split(" ")[0]}}</h2>
        <Post v-for="(insight, index) in this.relatedInsights" :key="insight.id" :entry="insight" :index="index"></Post>
      </div>
    </div>
  </div>
</template>

<script>
import Axios from 'axios'
import Event from '~components/Event.vue'
import Post from '~components/Post.vue'
import { mapGetters } from 'vuex'

export default {
  name: 'team-member',
  components: {
    Event, Post
  },
  data () {
    return {
      relatedEvents: null,
      relatedInsights: null
    }
  },
  computed: {
    ...mapGetters(['hostname'])
  },
  async created () {
    let relatedEventIds = this.member.events.map((event) => { return event.ID })
    if (typeof relatedEventIds !== 'undefined' && relatedEventIds) {
      let response = await Axios.get(this.hostname + 'wp/v2/bd_event', { params: { include: relatedEventIds } })
      this.relatedEvents = response.data
    }
    let relatedInsightIds = this.member.insights.map((insight) => { return insight.ID })
    if (typeof relatedInsightIds !== 'undefined' && relatedInsightIds) {
      let response = await Axios.get(this.hostname + 'wp/v2/bd_insight', { params: { include: relatedInsightIds } })
      this.relatedInsights = response.data
    }
  },
  async asyncData ({store, params}) {
    let response = await Axios.get(store.getters['hostname'] + 'familiar/v1/team/' + params.slug)
    return {
      member: response.data
    }
  },
  head () {
    return {
      title: this.member.name,
      meta: [
        { description: this.member.job_title },
        { 'og:image': this.member.headshot.url }
      ]
    }
  }
}
</script>

<style lang="css">
</style>
