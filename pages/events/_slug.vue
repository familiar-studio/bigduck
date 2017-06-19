<template>
  <div>
    <div class="img-hero" :style="{ backgroundImage: 'url(' + event.acf.featured_image.url + ')' }">
    </div>
    <figcaption class="caption">{{event.acf.featured_image.caption}}</figcaption>
    <div class="container-fluid">
      <div class="row">
        <div class="col-xl-8 col-lg-9 offset-lg-2">
          <share></share>
          <div class="container bg-white overlap">
            <article class="main">
              <div class="row">
              <div class="col-md-10">
                <div class="badge-group">
                  <div class="badge badge-default" v-for="topic in event.topic">
                    <img :src="getTopicsIndexedById[topic].acf.icon">
                    <div v-html="getTopicsIndexedById[topic].name"></div>
                  </div>
                  <div class="badge badge-default" v-for="eventCategory in event['event_category']">
                    <img :src="getEventCategoriesIndexedById[eventCategory].acf.icon">
                    <div v-html="getEventCategoriesIndexedById[eventCategory].name"></div>
                  </div>

                </div>

                <h1 v-html="event.title.rendered"></h1>
                <h2 v-html="event.acf.subtitle"></h2>
                <p v-html="event.acf.text"></p>
                <div v-if="event.related_team_members.data">
                  <div class="media" v-for="team_member in event.related_team_members.data">
                    <img v-if="team_member.headshot" :src="team_member.headshot.sizes.thumbnail" class="round author-img mr-2">
                    <h6 class="align-self-center mb-0">{{ team_member.member.display_name}}</h6>
                  </div>
                </div>
                <h6 class="align-self-center mt-2">{{ event.acf.location.address }}</h6>
              </div>
              <div class="col-md-2">
                <div class="card event-date">
                  <div class="card-block">
                    <div class="card-title">
                      {{month}}
                    </div>
                    <div class="card-text text-align-center">
                      {{date}}
                    </div>
                  </div>
                </div>
                <div class="event-time mt-1">
                  {{ start_time }}
                </div>
                <a :href="event.acf.registration_url" class="btn btn-primary mt-3 event-registration">
                  Register
                </a>
              </div>
            </div>

            </article>



            <div v-if="relatedEvents">
              <h2>Related Events</h2>
              <div class="" v-for="(event, index) in relatedEvents">
                <Event :entry="event" :index="index" :relatedTeamMembers="event.related_team_members.data"></Event>
              </div>
            </div>
            <div v-if="relatedInsights">
              <h2>Related Insights</h2>
              <div class="" v-for="(insight, index) in relatedInsights">
                <Post :entry="insight" :index="relatedEvents ? index + relatedEvents.length : index"></Post>
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
    head: {
      title: this.event ? this.event.title.rendered : null
    },
    async asyncData ({store, query, params}) {
      let data = {}
      let response = await Axios.get(store.getters['hostname'] + 'wp/v2/bd_event?slug=' + params.slug)
      data.event = response.data[0]
      data.relatedEventsIds = data.event.acf.related_events
      data.relatedInsightsIds = data.event.acf.related_insights
      return data
    },
    created () {
      // get related events
      if (this.relatedEventsIds) {
        Axios.get(this.hostname + 'wp/v2/bd_event', { params: { includes: this.relatedEventsIds } }).then((response) => {
          this.relatedEvents = response.data
        })
      }

      // get related insights
      if (this.relatedInsightsIds) {
        Axios.get(this.hostname + 'wp/v2/bd_event', { params: { includes: this.relatedInsightsIds } }).then((response) => {
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
        return dateFns.format(this.event.acf.start_time, 'Do')
      },
      start_time () {
        return dateFns.format(this.event.acf.start_time, 'h:mm')
      }
    }
  }
</script>
