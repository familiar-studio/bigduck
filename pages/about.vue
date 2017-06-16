<template>
  <div>
    <transition name="fade" appear>
      <div class="img-hero" :style=" { backgroundImage: 'url(' + page.acf.featured_image.url + ')' }">
      </div>
    </transition>
    <div class="menu">
      <ul class="list-unstyled">
        <li><a href="#we-believe">{{ page.acf.we_believe_headline}}</a></li>
        <li><a href="#values">{{ page.acf.values_headline}}</a></li>
        <li><a href="#our-clients">{{ page.acf.our_clients_headline}}</a></li>
        <li><a href="#open-house">Open House</a></li>
        <li><a href="#team">{{ page.acf.team_headline}}</a></li>
        <li><a href="#jobs">{{ page.acf.jobs_headline}}</a></li>
      </ul>
    </div>
  <div class="container">
    <div v-if="page" >
      <article class="main">
      <h1 id="we-believe">{{ page.acf.we_believe_headline }}</h1>
      <div v-html="page.acf.we_believe_body"></div>

      <h1>{{ page.our_clients_headline }}</h1>

      </article>
      <article class="values mt-5">
        <h1 id="values">{{ page.acf.values_headline }}</h1>
        <div v-html="page.acf.values_body"></div>
        <ul v-for="value in page.acf.values_" class="list-unstyled">
          <li>
            <div class="media">
              <img :src="value.value_icon.url" class="d-flex mr-3">
              <div class="media-body">
                <h2>{{ value.value_name }}</h2>
                <div v-html="value.value_description"></div>
              </div>
            </div>
          </li>
        </ul>
      </article>

      <article v-if="openHouse">
         <h1 id="open-house">Open House</h1>
         <div class="" v-for="event in openHouse">
           <Event :entry="event" :relatedTeamMembers="event.related_team_members.data"></Event>
         </div>
      </article>

      <article v-if="clientsBySector">
        <h1 id="our-clients" v-html="page.acf.our_clients_headline"></h1>
        <p v-html="page.acf.clients_body"></p>
        <div class="" v-for="(sector, key) in clientsBySector">
          <div class="" v-if="clientsBySector[key].length > 0">

          <img v-if="sectorsByIndex[key].acf.icon" :src="sectorsByIndex[key].acf.icon.url" />
          <h2 v-html="sectorsByIndex[key].name"></h2>
          <ul>
            <li class="" v-for="client in clientsBySector[key]">
              {{client.title.rendered}}
            </li>
          </ul>
          </div>
        </div>
      </article>

      <article>
        <h1 id="team">{{page.acf.team_headline}}</h1>
        <div v-html="page.acf.team_body"></div>
        <div class="row">
        <router-link :key="member.id" :to=" {name: 'team-slug', params: {slug: member.slug}}" v-for="member in team" class="col-md-4 card">
            <img class="card-img-top" :src="member.headshot.url" :alt="member.headshot.name" />
            <div class="card-block">
              <h4 class="card-title">{{member.headshot.title}}</h4>
              <div class="card-text">
                {{ member.job_title }}
              </div>
            </div>
          </router-link>
        </div>
      </article>

      <article>
        <h1 id="jobs">{{ page.acf.jobs_headline }}</h1>
        <div v-html="page.acf.jobs_body"></div>
        <div v-if="jobs" >
          <ul v-for="job in jobs" id="accordion">
            <h3>{{job.title.rendered}}</h3>
            <h4 v-html="job.acf.job_description_heading"></h4>
            <p v-html="job.acf.job_description"></p>
          </ul>
        </div>
      </article>
    </div>
  </div>
</div>
</template>
<script>
  import Axios from 'axios'
  import Event from '../components/Event.vue'

  export default {
    name: 'about',
    components: {
      Event
    },
    async asyncData ({store, query, dispatch}) {
      let data = {}
      let page = await Axios.get(store.getters['hostname'] + 'wp/v2/pages?slug=about')
      data['pageObject'] = page.data
      let [team, jobs, clients, openHouse] = await Promise.all([
        store.dispatch('fetch', 'familiar/v1/team'),
        store.dispatch('fetch', 'wp/v2/bd_job'),
        store.dispatch('fetch', 'wp/v2/bd_client'),
        store.dispatch('fetch', 'wp/v2/bd_event?event_category=31')
      ])
      data['team'] = team.data
      data['jobs'] = jobs.data

      // arrange clients into sectors:
      const sectors = store.state.sectors.sort()
      let clientsBySector = {}
      sectors.forEach((sector) => {
        clientsBySector[sector.id] = []
      })
      clients.data.forEach((client) => {
        if (client.sector.length > 0) {
          let clientSector = clientsBySector[client.sector[0]]
          clientSector = clientSector.push(client)
        }
      })
      // sectors.forEach((sector) => {
      //   clientsBySector[sector] = clientsBySector[sector].sort((a, b) => {})
      // })
      data['clientsBySector'] = clientsBySector
      data['openHouse'] = openHouse.data
      return data
    },
    computed: {
      page () {
        return this.pageObject[0]
      },
      sectorsByIndex () {
        return this.$store.getters['getSectorsIndexedById']
      }
    }
  }
</script>
