<template>
  <div>
    <div class="img-hero" :style="{ backgroundImage: 'url(' + event.acf.featured_image.url + ')' }">
      <figcaption class="figure-caption">{{event.acf.featured_image.caption}}</figcaption>
    </div>
    <div id="content">
      <div class="row no-gutters">
        <div class="col-lg-1 col-xl-2 hidden-md-down">
          <share></share>
        </div>
        <div class="col-lg-10 col-xl-8">
          <div class="container overlap">
            <article class="main" :class="{ 'mb-5': !event.acf.is_webinar }">
              <div class="row">
                <div class="col-lg-9">
                  <div class="badge-group">
                    <router-link class="badge badge-default underline-change overview-link" :to="{name: 'events'}">Events</router-link>
                    <div class="badge badge-default" v-for="topic in event.topic">
                      <div v-html="getTopicsIndexedById[topic].icon"></div>
                      <div v-html="getTopicsIndexedById[topic].name"></div>
                    </div>
                    <div class="badge badge-default" v-for="eventCategory in event['event_category']">
                      <div v-html="getEventCategoriesIndexedById[eventCategory].icon"></div>
                      <div v-html="getEventCategoriesIndexedById[eventCategory].name"></div>
                    </div>
                  </div>
                  <div class="event-title">
                    <h1>
                      <span v-html="event.title.rendered"></span>
                    </h1>
                    <h4>
                      <span v-html="event.acf.subtitle"></span>
                    </h4>

                    <h6 class="mobile-event-date">{{month}} {{date}} {{start_time}}&ndash;{{end_time}}</h6>
                  </div>
                  <div v-html="event.acf.text"></div>

                  <div v-if="event.related_team_members.data || event.acf.guest_speakers.length > 0" class="author-listing">
                    <div class="media speaker mt-3" v-if="event.related_team_members.data" v-for="team_member in event.related_team_members.data">
                      <img v-if="team_member.headshot" :src="team_member.headshot.sizes.thumbnail" class="round author-img mr-2">
                      <img v-else :src="globals.backup_author_image" class="round author-img mr-2">
                      <h6 class="align-self-center mb-0">
                        <nuxt-link :to="'/about/' + team_member.member.user_nicename">{{ team_member.member.display_name}}</nuxt-link>
                      </h6>
                    </div>
                    <div class="speaker media mt-3" v-for="speaker in event.acf.guest_speakers" v-if="event.acf.guest_speakers.length > 0">
                      <img :src="globals.backup_author_image" class="round author-img mr-2">
                      <h6 class="align-self-center mb-0">{{ speaker.speaker_name }}</h6>
                    </div>
                  </div>
                  <h4 class="mt-4 location-name">
                    <span v-if="event.acf.external_location" class="underline-change hover-color">
                      <a :href="event.acf.location_url">{{ event.acf.location_name }}</a>
                    </span>
                    <span v-else>
                      Big Duck
                    </span>
                  </h4>
                  <h6 class="align-self-center mt-2">
                    <span v-if="event.acf.external_location">
                      {{ event.acf.location_address }}
                    </span>
                    <span v-else>
                      20 Jay Street, Suite 524 Brooklyn, NY 11201
                    </span>
                  </h6>
                </div>
                <div class="col-lg-3 d-flex">
                  <aside>
                    <div class="date-block hidden-md-down">
                      <div class="event-date">
                        <h6>{{month}}</h6>
                        <h2>{{date}}</h2>
                      </div>
                      <div class="event-time mt-2">
                        <h6 class="text-center">{{ start_time }}&ndash;{{ end_time }}</h6>
                      </div>
                    </div>
                    <div class="">

                      <div v-if="!formFilled && !contentRefreshed">
                        <a v-if="event.acf.is_webinar" href="#register" class="btn btn-primary my-3 event-registration" v-scroll-to="{ el:'#register'}">Register</a>
                        <a v-else-if="event.acf.registration_url" :href="event.acf.registration_url" target="_blank" class="btn btn-primary my-3 event-registration">Register</a>
                      </div>
                    </div>
                    <div class="hidden-lg-up">
                      <share></share>
                    </div>
                  </aside>
                </div>
              </div>

            </article>

            <div v-if="event.acf.is_webinar" class="form-light" id="register" :class="{'mb-5': !relatedInsights && !relatedEvents}">

              <div v-if="!contentRefreshed && !formFilled">
                <h3>Register for this event</h3>

              </div>
              <GravityForm v-if="!formFilled" :formId=16 @submitted="refreshContent()" storagePrefix="event-" :id="event.id" :title="event.title.rendered" :actonId="event.acf.act_on_form_id"></GravityForm>

              <div v-if="formFilled || contentRefreshed">
                <div v-html="event.acf.post_registration_content"></div>
              </div>

            </div>

            <div v-if="relatedEvents || relatedInsights">
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
        <div class="col-xl-2">
          <Chat></Chat>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import Axios from 'axios'
import Chat from '~/components/Chat.vue'
import dateFns from 'date-fns'
import Event from '~/components/Event.vue'
import GravityForm from '~/components/GravityForm.vue'
import Post from '~/components/Post.vue'
import Share from '~/components/Share.vue'


import { mapState, mapGetters } from 'vuex'

export default {
  name: 'event',
  components: {
    Chat,
    Event,
    GravityForm,
    Post,
    Share
  },
  data() {
    return {
      relatedEventsIds: null,
      relatedInsightsIds: null,
      relatedInsights: null,
      relatedEvents: null,
      contentRefreshed: false
    }
  },
  head() {
    if (this.event) {

      return {
        title: this.event.title.rendered,
        meta: [
          {
            'property': 'og:title',
            'content': this.event.title.rendered
          },
          {
            'property': 'twitter:title',
            'content': this.event.title.rendered
          },
          {
            'property': 'description',
            'content': this.event.acf.subtitle
          },
          {
            'property': 'og:description',
            'content': this.event.acf.subtitle
          },
          {
            'property': 'twitter:description',
            'content': this.event.acf.subtitle
          },
          {
            'property': 'image',
            'content': this.event.acf.featured_image.url
          },
          {
            'property': 'og:image:url',
            'content': this.event.acf.featured_image.url
          },
          {
            'property': 'twitter:image',
            'content': this.event.acf.featured_image.url
          }
        ]
      }
    }
  },
  async asyncData({ store, query, params }) {
    let data = {}
    let response = await Axios.get(store.getters['hostname'] + 'wp/v2/bd_event', { params: { slug: params.slug } })
    data.event = response.data[0]
    data.relatedEventsIds = data.event.acf.related_events ? data.event.acf.related_events.map((e) => { return e.ID }) : null
    data.relatedInsightsIds = data.event.acf.related_insights ? data.event.acf.related_insights.map((e) => { return e.ID }) : null
    return data
  },
  created() {
    // get related events
    if (this.relatedEventsIds) {
      Axios.get(this.hostname + 'wp/v2/bd_event', { params: { include: this.relatedEventsIds } }).then((response) => {
        this.relatedEvents = response.data
      })
    }

    // get related insights
    if (this.relatedInsightsIds) {
      Axios.get(this.hostname + 'wp/v2/bd_insight', { params: { include: this.relatedInsightsIds } }).then((response) => {
        this.relatedInsights = response.data
      })
    }
  },
  computed: {
    ...mapState(['globals', 'callouts', 'topics']),
    ...mapGetters(['hostname', 'getTopicsIndexedById', 'getEventCategoriesIndexedById']),
    formFilled() {
      if (process.browser && this.event && typeof localStorage !== 'undefined') {
        // figure out whether the user has filled out the form from the cookie
        return localStorage['event-' + this.event.id]
      }
    },
    month() {
      return dateFns.format(this.event.acf.start_time, 'MMM')
    },
    date() {
      return dateFns.format(this.event.acf.start_time, 'D')
    },
    start_time() {
      return dateFns.format(this.event.acf.start_time, 'h:mma')
    },
    end_time() {
      return dateFns.format(this.event.acf.end_time, 'h:mma')
    }
  },
  methods: {
    refreshContent() {
      this.contentRefreshed = true
    }
  }
}
</script>
<!-- /*<style>
  .speaker {
    margin-top: 1rem;
  }
</style>*/ -->
