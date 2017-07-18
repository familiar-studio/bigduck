<template lang="html">
  <div class="team">
    <div class="img-hero" :style=" { backgroundImage: 'url(' + member.headshot.url + ')' }" v-if="member.headshot">
      <figcaption class="figure-caption">{{member.headshot.caption}}</figcaption>
    </div>
    <div class="container overlap" id="content">
      <article class="main" :class="{'mb-5': !relatedInsights && !relatedEvents }">
        <div class="badge-group">
          <nuxt-link to="/about#team" class="badge badge-default overview-link overview-link">Team</nuxt-link>
        </div>
        <h1 class="mb-1">{{member.name}}</h1>
        <div class="row">
          <div class="col-md-9">
              <h4>
                <span class="mr-3">{{member.job_title}}</span>
                <a v-if="member.twitter_handle" :href="'http://twitter.com/' + member.twitter_handle" target="_blank" class="mr-3">
                <div class="badge badge-default social">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M23.444 4.834c-.814.363-1.5.375-2.228.016.938-.562.981-.957 1.32-2.019-.878.521-1.851.9-2.886 1.104-.827-.882-2.008-1.435-3.315-1.435-2.51 0-4.544 2.036-4.544 4.544 0 .356.04.703.117 1.036-3.776-.189-7.125-1.998-9.366-4.748-.391.671-.615 1.452-.615 2.285 0 1.577.803 2.967 2.021 3.782-.745-.024-1.445-.228-2.057-.568l-.001.057c0 2.202 1.566 4.038 3.646 4.456-.666.181-1.368.209-2.053.079.579 1.804 2.257 3.118 4.245 3.155-1.945 1.524-4.356 2.159-6.728 1.881 2.012 1.289 4.399 2.041 6.966 2.041 8.358 0 12.928-6.924 12.928-12.929l-.012-.588c.887-.64 1.953-1.237 2.562-2.149z"/></svg>
                  <span v-html="member.twitter_handle"></span>
                </div>
                </a>
              <a v-if="member.facebook_page" :href="'http://facebook.com/' + member.facebook_page" target="_blank">
                <div class="badge badge-default social">
                  <span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M18.768 7.465h-4.268v-1.905c0-.896.594-1.105 1.012-1.105h2.988v-3.942l-4.329-.013c-3.927 0-4.671 2.938-4.671 4.82v2.145h-3v4h3v12h5v-12h3.851l.417-4z"/></svg>
                  </span>
                  <span v-html="member.facebook_page"></span>
                </div>
              </a>
              </h4>
            <div class="mt-3" v-html="member.bio"></div>

          </div>
          <div class="col-md-3">

            <aside class="mt-5">
              <h6>{{ member.additional_info_heading_1 }}</h6>
              <div v-html="member.additional_info_body_1" class="mb-4 label"></div>
              <h6>{{ member.additional_info_heading_2 }}</h6>
              <div v-html="member.additional_info_body_2" class="label"></div>
            </aside>
          </div>
        </div>
      </article>
      <div v-if="relatedEvents && relatedEvents.events.length > 0">
        <h2 class="mt-5 mb-3">Events with {{member.name.split(" ")[0]}}</h2>
        <Event v-for="(event, index) in relatedEvents.events" :entry="event.data" :key="event.slug" :index="index" :relatedTeamMembers="event.team_meta"></Event>
      </div>
      <div class="" v-if="relatedInsights && relatedInsights.length > 0">
        <h2 :class="{'mt-5 mb-3': !relatedEvents }">Insights by {{ member.name.split(" ")[0]}}</h2>

        <Post v-for="(insight, index) in relatedInsights" :key="insight.id" :entry="insight" :index="index + relatedEventsLength"></Post>

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
  head () {
    return {
      title: this.member.name,
      meta: [
        {
          'property': 'og:title',
          'content': this.member.name
        },
        {
          'property': 'twitter:title',
          'content': this.member.name
        },
        {
          'property': 'description',
          'content': this.member.job_title
        },
        {
          'property': 'og:description',
          'content': this.member.job_title
        },
        {
          'property': 'twitter:description',
          'content': this.member.job_title
        },
        {
          'property': 'image',
          'content': this.member.headshot.url
        },
        {
          'property': 'og:image',
          'content': this.member.headshot.url
        },
        {
          'property': 'twitter:image',
          'content': this.member.headshot.url
        }
      ]
    }
  },
  data () {
    return {
      relatedEvents: null,
      relatedInsights: null,
      insightsPerPage: 2,
      insightsPage: 0,
      totalInsightsPages: null
    }
  },
  components: {
    Event, Post
  },
  computed: {
    ...mapGetters(['hostname', 'relatedInsightsPerPage']),
    relatedEventsLength () {
      if (this.relatedEvents && this.relatedEvents.events) {
        return this.relatedEvents.events.length
      } else {
        return 0
      }
    }
  },
  async created() {
    let relatedEventIds = this.member.events.map((event) => { return event.ID })
    if (relatedEventIds && relatedEventIds.length > 0 ) {
      let response = await Axios.get(this.hostname + 'familiar/v1/events/user/' + this.member.slug )
      this.relatedEvents = response.data
    }
      let response = await Axios.get(this.hostname + 'familiar/v1/insights/user/' + this.member.slug + '?posts_per_page=' + this.insightsPerPage + '&page=' + this.insightsPage )
      // let response = await Axios.get(this.hostname + 'familiar/v1/insights/user/' + this.member.slug )
      this.totalInsightsPages = response.data.pages
      this.relatedInsights = response.data.data
  },
  async asyncData({ store, params }) {
    console.log(params)
    let response = await Axios.get(store.getters['hostname'] + 'familiar/v1/team/' + params.slug)
    return {
      member: response.data
    }
  }
}
</script>

<style lang="css">

</style>
