<template>
  <div>
    <div class="img-hero" :style="{ backgroundImage: 'url(' + event.acf.featured_image.url + ')' }">
      <figcaption class="figure-caption">{{event.acf.featured_image.caption}}</figcaption>
    </div>
    <div class="container-fluid" id="content">
      <div class="row">
        <div class="col-lg-1 hidden-md-down">
          <share></share>
        </div>
        <div class="col-lg-10">
          <div class="container overlap">
            <article class="main">
              <div class="row">
              <div class="col-lg-9">
                <div class="badge-group">
                  <router-link class="badge badge-default underlineChange" :to="{name: 'events'}">Event</router-link>
                  <div class="badge badge-default" v-for="topic in event.topic">
                    <img :src="getTopicsIndexedById[topic].acf.icon">
                    <div v-html="getTopicsIndexedById[topic].name"></div>
                  </div>
                  <div class="badge badge-default" v-for="eventCategory in event['event_category']">
                    <img :src="getEventCategoriesIndexedById[eventCategory].acf.icon">
                    <div v-html="getEventCategoriesIndexedById[eventCategory].name"></div>
                  </div>
                </div>
                <div class="event-title">
                  <h1 v-html="event.title.rendered"></h1>
                  <h4 v-html="event.acf.subtitle"></h4>
                </div>
                <p v-html="event.acf.text"></p>
                <div v-if="event.related_team_members.data">
                  <div class="media" v-for="team_member in event.related_team_members.data">
                    <img v-if="team_member.headshot" :src="team_member.headshot.sizes.thumbnail" class="round author-img mr-2">
                    <h6 class="align-self-center mb-0">{{ team_member.member.display_name}}</h6>
                  </div>
                </div>
                <h6 class="align-self-center mt-2">{{ event.acf.location.address }}</h6>
              </div>
              <div class="col-lg-3 d-flex">
                <aside>
                  <div class="event-date">
                    <h6>{{month}}</h6>
                    <h2>{{date}}</h2>
                  </div>
                  <div class="event-time mt-2">
                    <h6>{{ start_time }}&ndash;{{ end_time }}</h6>
                  </div>
                  <a :href="event.acf.registration_url" class="btn btn-primary mt-3 event-registration">
                    Register
                  </a>
                  <div class="hidden-lg-up">
                    <share></share>
                  </div>
                </aside>
              </div>
            </div>

            </article>

            <div v-if="relatedEvents && relatedInsights">
              <h2 class="mb-3 mt-5">Related Events &amp; Insights</h2>
              <div v-if="relatedEvents">
                <div class="" v-for="(event, index) in relatedEvents">
                  <Event :entry="event" :index="index" :relatedTeamMembers="event.related_team_members.data"></Event>
                </div>
              </div>
              <div v-if="relatedInsights">
                <div class="" v-for="(insight, index) in relatedInsights">
                  <Post :entry="insight" :index="relatedEvents ? index + relatedEvents.length : index"></Post>
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
  import Event from '~components/Event.vue'
  import Post from '~components/Post.vue'
  import dateFns from 'date-fns'
  import Share from '~components/Share.vue'

  import { mapState, mapGetters } from 'vuex'

  export default {
    name: 'event',
    components: {
      Event,
      Post,
      Share
    },
    data () {
      return {
        relatedEventsIds: null,
        relatedInsightsIds: null,
        relatedInsights: null,
        relatedEvents: null
      }
    },
    head () {
      return {
        title: this.event.title.rendered,
        meta: [
          { description: this.event.acf.subtitle },
          { 'og:image': this.event.acf.featured_image.url }
        ]
      }
    },
    async asyncData ({store, query, params}) {
      let data = {}
      let response = await Axios.get(store.getters['hostname'] + 'wp/v2/bd_event?slug=' + params.slug)
      data.event = response.data[0]
      data.relatedEventsIds = data.event.acf.related_events.map((e) => { return e.ID })
      data.relatedInsightsIds = data.event.acf.related_insights.map((e) => { return e.ID })
      return data
    },
    created () {
      // get related events
      if (this.relatedEventsIds) {
        Axios.get(this.hostname + 'wp/v2/bd_event', { params: { include: this.relatedEventsIds } }).then((response) => {
          this.relatedEvents = response.data
        })
      }

      // get related insights
      if (this.relatedInsightsIds) {
        Axios.get(this.hostname + 'wp/v2/bd_insight', { params: { include: this.relatedInsightsIds } }).then((response) => {
          console.log('insights', response)
          this.relatedInsights = response.data
        })
      }
    },
    computed: {
      ...mapState(['callouts', 'topics']),
      ...mapGetters(['hostname', 'getTopicsIndexedById', 'getEventCategoriesIndexedById']),
      month () {
        return dateFns.format(this.event.acf.start_time, 'MMM')
      },
      date () {
        return dateFns.format(this.event.acf.start_time, 'D')
      },
      start_time () {
        return dateFns.format(this.event.acf.start_time, 'h:mma')
      },
      end_time () {
        return dateFns.format(this.event.acf.end_time, 'h:mma')
      }
    }
  }
</script>
