<template>
  <div class="">
    <div v-if="event && event.acf">
    <transition name="fade" appear>
      <div class="img-hero" :style="{ backgroundImage: 'url(' + event.acf.featured_image.url + ')' }">
      </div>
    </transition>
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-2">
        </div>

        <div class="col-md-8">
          <div class="share" id="share">
            <h6>Share</h6>
            <a href="#">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M18.768 7.465h-4.268v-1.905c0-.896.594-1.105 1.012-1.105h2.988v-3.942l-4.329-.013c-3.927 0-4.671 2.938-4.671 4.82v2.145h-3v4h3v12h5v-12h3.851l.417-4z"/></svg>
            </a>
            <a href="#">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M23.444 4.834c-.814.363-1.5.375-2.228.016.938-.562.981-.957 1.32-2.019-.878.521-1.851.9-2.886 1.104-.827-.882-2.008-1.435-3.315-1.435-2.51 0-4.544 2.036-4.544 4.544 0 .356.04.703.117 1.036-3.776-.189-7.125-1.998-9.366-4.748-.391.671-.615 1.452-.615 2.285 0 1.577.803 2.967 2.021 3.782-.745-.024-1.445-.228-2.057-.568l-.001.057c0 2.202 1.566 4.038 3.646 4.456-.666.181-1.368.209-2.053.079.579 1.804 2.257 3.118 4.245 3.155-1.945 1.524-4.356 2.159-6.728 1.881 2.012 1.289 4.399 2.041 6.966 2.041 8.358 0 12.928-6.924 12.928-12.929l-.012-.588c.887-.64 1.953-1.237 2.562-2.149z"/></svg>
            </a>
            <a href="#">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M6.527 21.5h-5v-13h5v13zm-2.509-15h-.03c-1.51 0-2.488-1.182-2.488-2.481 0-1.329 1.008-2.412 2.547-2.412 1.541 0 2.488 1.118 2.519 2.447-.001 1.3-.978 2.446-2.548 2.446zm11.509 6c-1.105 0-2 .896-2 2v7h-5s.059-12 0-13h5v1.485s1.548-1.443 3.938-1.443c2.962 0 5.062 2.144 5.062 6.304v6.654h-5v-7c0-1.104-.895-2-2-2z"/></svg>
            </a>
          </div>
          <transition name="slideUp" appear>
            <div class="container bg-white overlap-300">
              <article class="main row mb-5">
                <div class="col-md-10">
                <div class="badge-group" v-if="topics && types">
                  <!-- <router-link class="badge badge-default color-underline" :to="{name: 'Insights'}">Insights</router-link> -->
                  <div class="badge badge-default" v-for="topic in event.topic">
                    <img :src="topicsIndexedById[topic].acf.icon">
                    <div v-html="topicsIndexedById[topic].name"></div>
                  </div>
                  <div class="badge badge-default" v-for="type in event.type">
                    <img :src="typesIndexedById[type].acf.icon">
                    <div v-html="typesIndexedById[type].name"></div>
                  </div>
                  <div class="badge badge-default" v-for="type in event.type">
                    <!-- <img :src="typesIndexedById[type].acf.icon">
                    <div v-html="typesIndexedById[type].name"></div> -->
                  </div>
                  <!-- <div class="badge badge-default" v-if="insight.date">
                    {{ month }} {{ day }}, {{ year }}
                  </div> -->
                </div>

                <h1 v-html="event.title.rendered"></h1>
                <h2 v-html="event.acf.subtitle"></h2>
                <p v-html="event.content.rendered"></p>
                <div v-for="block in event.acf.body" :class="['block-' + block.acf_fc_layout]">
                  <div v-if="block.acf_fc_layout == 'text'" v-html="block.text"></div>
                  <template v-if="block.acf_fc_layout == 'callout'">
                    <div v-html="block.text">
                    </div>
                    <img :src="block.image" alt="callout image" v-if="block.image" />
                  </template>
              </div>
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

              </article>
              <div class="" v-if="types && topics && categories">


              <div v-if="relatedEvents">
                <h2>Related Events</h2>
                <div class="" v-for="(event, index) in relatedEvents">
                  <Event :entry="event" :categories="categories" :index="index"></Event>
                </div>
              </div>
              <div v-if="relatedInsights">
                <h2>Related Insights</h2>
                <div class="" v-for="(insight, index) in relatedInsights">
                  <Post :entry="insight" :categories="categories" :index="index + relatedEvents.length"></Post>
                </div>
              </div>
              </div>
            </div>
          </transition>
        </div>

        <div class="col-md-2">
        </div>

      </div>

      <div class="container overlap-300 mt-5" >
        <!-- <h2>Related Case Studies</h2> -->
        <!-- <div class="row">
          <div v-for="case_study in insight.acf.related_case_studies" class="col-md-6">
            <router-link :to="{name: 'CaseStudy', params: {id: case_study.ID}}" :key="case_study.ID">
              <img :src="caseStudiesById[case_study.ID].acf.hero_image.sizes.large" style="width:100%">
              <div class="card two-up-card mx-4">
                <div class="card-header" v-if="categories">
                  <div class="badge badge-default" v-for="topic in caseStudiesById[case_study.ID].topic">
                      <div v-html="topicsIndexedById[topic].icon.data"></div>
                      <div v-html="topicsIndexedById[topic].name"></div>
                  </div>
                </div>
                <div class="card-block py-0">
                  <h3 class="card-title">{{ caseStudiesById[case_study.ID].title.rendered }}</h3>
                 <p class="card-text" v-html="caseStudiesById[case_study.ID].acf.short_description"></p>
                </div>
              </div>
            </router-link>

          </div>
        </div> -->
      </div>
    </div>
  </div>
</div>
</template>
<script>
  import Axios from 'axios'
  import Event from '~components/Event.vue'
  import Post from '~components/Post.vue'
  import moment from 'moment'
  import { mapState, mapGetters } from 'vuex'

  export default {
    name: 'event',
    components: {
      Event,
      Post
    },
    async asyncData ({store, query, params}) {
      let data = {}
      data.event = await store.dispatch('fetchOne', {path: 'wp/v2/bd_event', id: params.id})

      let relatedEventIds = data.event.acf.related_events
      let relatedInsightIds = data.event.acf.related_insights

      if (relatedEventIds) {
        let responseEvents = await Axios.get(store.getters['hostname'] + 'wp/v2/bd_event?' + relatedEventIds.map((obj) => 'include[]=' + obj.ID).join('&'))
        data.relatedEvents = responseEvents.data
      }

      if (relatedInsightIds) {
        let responseInsights = await Axios.get(store.getters['hostname'] + 'wp/v2/bd_insight?' + relatedInsightIds.map((obj) => 'include[]=' + obj.ID).join('&'))
        data.relatedInsights = responseInsights.data
      }
      return data
    },
    computed: {
      ...mapState(['categories', 'callouts', 'types', 'topics']),
      ...mapGetters(['getTopicsIndexedById', 'getTypesIndexedById']),
      month () {
        return moment(this.event.acf.start_time).format('MMM')
      },
      date () {
        return moment(this.event.acf.start_time).format('Do')
      },
      start_time () {
        return moment(this.event.acf.start_time).format('h:mm')
      }
    }
  }
</script>
