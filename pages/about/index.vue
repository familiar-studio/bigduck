<template>
  <div>
    <div class="img-hero" :style=" { backgroundImage: 'url(' + page.acf.featured_image.url + ')' }">
      <figcaption class="figure-caption">{{page.acf.featured_image.caption}}</figcaption>
    </div>
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-2">
          <div class="menu subnav">
            <ul class="nav flex-column">
              <li class="nav-item">
                <a class="nav-link" :class="{active:scrollPos == 0}" href="#" @click.prevent="$scrollTo(0)" ><span>{{ page.acf.we_believe_headline}}</span></a>
              </li>
              <li class="nav-item">
                <a class="nav-link" :class="{active:scrollPos == 1}" href="#" @click.prevent="$scrollTo(1)" ><span>{{ page.acf.values_headline}}</span></a>
              </li>
              <li class="nav-item">
                <a class="nav-link" :class="{active:scrollPos == 2}" href="#" @click.prevent="$scrollTo(2)" ><span>Open House</span></a>
              </li>
              <li class="nav-item">
                <a class="nav-link" :class="{active:scrollPos == 3}" href="#" @click.prevent="$scrollTo(3)" ><span>{{ page.acf.our_clients_headline}}</span></a>
              </li>
              <li class="nav-item">
                <a class="nav-link" :class="{active:scrollPos == 4}" href="#" @click.prevent="$scrollTo(4)" ><span>{{ page.acf.team_headline}}</span></a>
              </li>
              <li class="nav-item">
                <a class="nav-link" :class="{active:scrollPos == 5}" href="#" @click.prevent="$scrollTo(5)" ><span>{{ page.acf.jobs_headline}}</span></a>
              </li>
            </ul>
          </div>
        </div>
        <div class="col-lg-8">
          <div class="container">
            <div v-scroll-spy="scrollPos" :steps="30" :time="200">
              <article class="main overlap">
                <h1 id="we-believe">{{ page.acf.we_believe_headline }}</h1>
                <div v-html="page.acf.we_believe_body"></div>
                <h1>{{ page.our_clients_headline }}</h1>
              </article>

              <article class="values bgChange break-container mt-5">
                <div>
                  <h1 id="values">{{ page.acf.values_headline }}</h1>
                  <div v-html="page.acf.values_body"></div>
                  <ul class="list-unstyled">
                    <li v-for="value in page.acf.values_">
                      <div class="media">
                        <img :src="value.value_icon.url" class="d-flex mr-3">
                        <div class="media-body">
                          <h3>{{ value.value_name }}</h3>
                          <div v-html="value.value_description"></div>
                        </div>
                      </div>
                    </li>
                  </ul>
                </div>
              </article>

              <article v-if="openHouse">
                <h1 class="mt-5" id="open-house">Open House</h1>
                <div v-html="page.acf.open_house_body"></div>
                <div class="" v-for="(event, index) in openHouse">
                  <!-- {{event}} -->
                  <Event :entry="event" :index="index" :relatedTeamMembers="event.related_team_members.data"></Event>
                </div>
              </article>

              <article>
                <h1 id="our-clients" v-html="page.acf.our_clients_headline"></h1>
                <p v-html="page.acf.clients_body"></p>
                <div class="" v-for="(client, index) in page.acf.clients">
                  <div class="" v-if="client.client_category">
                  <div class="media" @click.prevent="toggleClient(client.client_category[0])" :class="{ 'active': openCategory === client.client_category[0] }">
                    <img class="mr-3" v-if="client.client_category"
                    :src="sectorsByIndex[client.client_category[0]].acf['taxonomy-icon']" /></img>
                    <div>
                      <h2><a href="#" ><span v-html="sectorsByIndex[client.client_category[0]].name"></span></a></h2>
                      <ul class="list-unstyled collapse" :class="{'show': openCategory === client.client_category[0]}">
                        <li class="" v-for="client_list in client.c">
                          <a :href="client_list.website">{{client_list.name}}</a>
                        </li>
                      </ul>
                    </div>
                  </div>
                  <!-- </div> -->
                  </div>
                  <hr v-if="index < page.acf.clients.length - 1"></hr>
                </div>
              </article>

              <article class="break-container bg-white team pt-4">
                <h1 id="team">{{page.acf.team_headline}}</h1>
                <div v-html="page.acf.team_body" class="mb-5"></div>

                <div class="row">
                <router-link :key="member.id" :to=" {name: 'about-slug', params: {slug: member.slug}}" v-for="member in team" class="col-md-4 mb-4">
                    <img class="img-fluid" :src="member.headshot.url" :alt="member.headshot.name" />
                    <div>
                      <h4 class="mt-3 mb-1">{{member.headshot.title}}</h4>
                      <h6>{{ member.job_title }}</h6>
                    </div>
                  </router-link>
                </div>
              </article>

              <article id="jobs">
                <h1>{{ page.acf.jobs_headline }}</h1>
                <div v-html="page.acf.jobs_body" class="mb-5"></div>
                <div v-if="jobs">
                  <div v-for="(job, index) in jobs" :class="{'active': job.id === openJob}">
                    <h2 class="mt-4" ><a href="#" @click.prevent="toggleJob(job.id)">{{job.title.rendered}}</a></h2>
                    <div class="collapse" :class="{'show': job.id === openJob}">
                      <h4 v-html="job.acf.job_description_heading"></h4>
                      <p v-html="job.acf.job_description"></p>
                      <a class="btn btn-primary" :href="applyUrl">Apply</a>
                    </div>
                    <hr></hr>
                  </div>
                </div>
              </article>
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

  export default {
    name: 'about',
    components: {
      Event
    },
    data () {
      return {
        applyUrl: 'http://bigduck.nyc',
        scrollPos: 0,
        openCategory: null,
        openJob: null
      }
    },
    ready: function () {
      this.$scrollSet()
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
    },
    methods: {
      toggleClient (categoryId) {
        this.openCategory = this.openCategory === categoryId ? null : categoryId
      },
      toggleJob (jobId) {
        this.openJob = this.openJob === jobId ? null : jobId
      }
    },
    head () {
      return {
        title: 'About',
        meta: [
          { description: this.page.acf.we_believe_body },
          { 'og:image': this.page.acf.featured_image.url }
        ]
      }
    }
  }
</script>
