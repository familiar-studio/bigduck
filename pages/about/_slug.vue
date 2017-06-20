<template lang="html">
  <div>
    <transition name="fade" appear>
      <div class="img-hero" :style=" { backgroundImage: 'url(' + member.headshot.url + ')' }">
        <figcaption class="figure-caption">{{member.headshot.caption}}</figcaption>
      </div>
    </transition>
    <div class="container">
      <div class="" v-if="member">
        <article class="main">
          <div class="badge badge-default">
            Team
          </div>
          <div class="row">
            <div class="col-md-9">
              <h1>{{member.name}}</h1>
              <h2>{{member.job_title}}</h2>
              <p v-html="member.bio"></p>
            </div>
            <div class="col-md-3">
              <div v-if="member.twitter_handle">
                <a :href="'http://twitter.com/' + member.twitter_handle">{{ member.twitter_handle }}</a> on Twitter
              </div>
              <div v-if="member.facebook_page">
                <a :href="'http://facebook.com/' + member.facebook_page">{{ member.facebook_page }}</a> on Twitter
              </div>
              <h4>{{ member.additional_info_heading_1 }}</h4>
              <div v-html="member.additional_info_body_1"></div>
              <h4>{{ member.additional_info_heading_2 }}</h4>
              <div v-html="member.additional_info_body_2"></div>
            </div>
          </div>
        </article>
      </div>
    </div>
    <div class="container">
          <div v-if="relatedEvents && relatedEvents.length > 0">
            <h2>Events with {{member.name.split(" ")[0]}}</h2>
            <Event v-for="(event, index) in relatedEvents" :entry="event" :key="event.id" :relatedTeamMembers="event.related_team_members.data" :index="index"></Event>
          </div>
          <div class="" v-if="relatedInsights && relatedInsights.length > 0">
            <h2>Insights by {{ member.name.split(" ")[0]}}</h2>
            <Post v-for="(insight, index) in relatedInsights" :key="insight.id" :entry="insight" :index="index"></Post>
          </div>
          <!-- <div v-if="posts">
            Posts by {{ member.acf.name.split(" ")[0]}}
            <ul>
              <li v-for="post in posts">
                <router-link :to="{ name: 'post', params: { slug: post.slug }}">{{ post.title }}</router-link>
              </li>
            </ul>
          </div>
          <div v-else>
            No posts to show.
          </div>
            <div v-if="work">
              <h2>Work</h2>
              <div class="card" v-for="case_study in work">
                <router-link :to="{ name: 'CaseStudy', params: { id: case_study.ID }}">
                  <div class="card-header"> {{case_study.post_title}}
                  </div>
                </router-link>
              </div>
            </div>
          <div v-else>
            Loading work...
          </div> -->
        </div>
    </div>
</template>

<script>
import Axios from 'axios'
import Event from '~components/Event.vue'
import Post from '~components/Post.vue'

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
  async created () {
    let relatedEventIds = this.member.events
    if (typeof relatedEventIds !== 'undefined' && relatedEventIds) {
      let response = await Axios.get(store.getters['hostname'] + 'wp/v2/bd_event?' + relatedEventIds.map((obj) => 'include[]=' + obj.ID).join('&'))
      this.relatedEvents = response.data
    }
    let relatedInsightIds = this.member.insights
    if (typeof relatedInsightIds !== 'undefined' && relatedInsightIds) {
      response = await Axios.get(store.getters['hostname'] + 'wp/v2/bd_insight?' + relatedInsightIds.map((obj) => 'include[]=' + obj.ID).join('&'))
      this.relatedInsights = response.data
    }
  },
  async asyncData ({store, params}) {
    let data = {}
    let response = await Axios.get(store.getters['hostname'] + 'familiar/v1/team/' + params.slug)
    let member = response.data
    data['member'] = member

    return data
  }
}
</script>

<style lang="css">
</style>
