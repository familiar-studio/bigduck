<template>
  <div class="container  no-hero">
    <div v-if="page" >
      <article class="main">
      <h1>{{ page.acf.we_believe_headline }}</h1>
      <div v-html="page.acf.we_believe_body"></div>

      <h1>{{ page.our_clients_headline }}</h1>

      </article>
      <article class="values mt-5">
        <h1>{{ page.acf.values_headline }}</h1>
        <div v-html="page.acf.values_body"></div>
        <ul v-for="value in page.acf.values_" class="list-unstyled">
          <li>
            <div class="media">
              <img :src="value.value_icon.icon" class="d-flex mr-3">
              <div class="media-body">
                <h2>{{ value.value_name }}</h2>
                <div v-html="value.value_description"></div>
              </div>
            </div>
          </li>
        </ul>
      </article>

      <article>
         <h1>Open House</h1>
         <!-- <Event></Event> -->
      </article>

      <article>
        Clients
      </article>

      <article>
        <h1>{{page.acf.team_headline}}</h1>
        <div v-html="page.acf.team_body"></div>
        <div class="row">
        <router-link :key="member.id" :to=" {name: 'team-member', params: {slug: member.headshot.name}}" v-for="member in team" class="col-md-4 card">
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
        <h1>{{ page.acf.jobs_headline }}</h1>
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
</template>
<script>
  import Axios from 'axios'
  import Event from '../components/Event.vue'

  export default {
    name: 'about',
    async asyncData ({store, query}) {
      // data.pageObject = await store.dispatch('fetch', 'wp/v2/pages?slug=about')
      // data.team = await store.dispatch('fetch', 'familiar/v1/team')

      // return data
      // // return {
      // //   pageObject: {},
      // //   team: {},
      // //   jobs: {},
      // //   clients: {},
      // //   openHouse: {}
      // // }
    },
    data () {
      return {
        pageObject: {},
        team: {},
        jobs: {},
        clients: {},
        openHouse: {}
      }
    },
    created () {
      this.fetchPage()
      this.fetchTeam()
      this.fetchJobs()
      this.fetchClients()
      // this.fetchOpenHouse()
    },
    methods: {
      fetch (path, property) {
        return Axios.get(this.$store.getters['hostname'] + path)
        .then((response) => {
          this[property] = response.data
        })
        .catch((error) => {
          console.warn(error)
        })
      },
      fetchTeam () {
        this.fetch('familiar/v1/team', 'team')
      },
      fetchPage () {
        this.fetch('wp/v2/pages?slug=about', 'pageObject')
      },
      fetchJobs () {
        this.fetch('wp/v2/bd_job', 'jobs')
      },
      fetchClients () {
        this.fetch('wp/v2/bd_client', 'clients')
      },
      fetchOpenHouse () {
        this.fetch('wp/v2/event?slug=open-house', 'openHouse')
      }
    },
    computed: {
      page () {
        return this.pageObject[0]
      }
    }
  }
</script>
