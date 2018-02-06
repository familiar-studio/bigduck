<template>
  <div class="about">
    <div class="img-hero" :style=" { backgroundImage: 'url(' + page.acf.featured_image.url + ')' }">
      <figcaption class="figure-caption">{{page.acf.featured_image.caption}}</figcaption>
    </div>
    <div>
      <div class="row no-gutters">
        <div class="col-xl-2">
          <div class="menu subnav">
            <ul class="nav flex-column">
              <li class="nav-item">
                <a class="nav-link" :class="{ 'active': activeSection == 'we-believe' }" href="#" v-scroll-to="'#we-believe'">
                  <span>{{ page.acf.we_believe_headline}}</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#" :class="{ 'active': activeSection == 'clients' }" v-scroll-to="'#clients'">
                  <span>{{ page.acf.our_clients_headline}}</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#" :class="{ 'active': activeSection == 'values' }" v-scroll-to="'#values'">
                  <span>{{ page.acf.values_headline}}</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#" :class="{ 'active': activeSection == 'team' }" v-scroll-to="'#team'">
                  <span>{{ page.acf.team_headline}}</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#" :class="{ 'active': activeSection == 'open-house' }" v-scroll-to="'#open-house'">
                  <span>Open House</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#" :class="{ 'active': activeSection == 'jobs' }" v-scroll-to="'#jobs'">
                  <span>{{ page.acf.jobs_headline}}</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
        <div class="col-xl-8">
          <div class="container">
            <!-- <div v-scroll-spy="scrollPos" :steps="30" :time="200"> -->
            <div>
              <v-waypoint @waypoint-in="activateSection('we-believe')"></v-waypoint>
              <article id="we-believe" class="main overlap">
                <h1>{{ page.acf.we_believe_headline }}</h1>
                <div v-html="page.acf.we_believe_body"></div>
                <h1>{{ page.our_clients_headline }}</h1>
              </article>
              <v-waypoint @waypoint-in="activateSection('clients')"></v-waypoint>
              <article class="my-5" v-if="sectorsByIndex" id="clients">
                <h1 v-html="page.acf.our_clients_headline"></h1>
                <div v-html="page.acf.clients_body" class="mb-4-5"></div>

                <div class="collapse-block" v-for="(client, index) in page.acf.clients">
                  <div class="" v-if="client.client_category">
                    <div class="media" :class="{ 'active': openCategories[client.client_category] }">
                      <span class="svg" v-html="sectorsByIndex[client.client_category].icon"></span>
                      <h3>
                        <a href="#" class="underline-change" v-html="sectorsByIndex[client.client_category].name" @click.prevent="toggleClient(client.client_category)">
                        </a>
                      </h3>
                    </div>
                  </div>
                  <div class="collapse-content" :class="{'show': openCategories[client.client_category] }">
                    <ul class="list-unstyled client-list">
                      <li class="" v-for="client_list in client.c">
                        {{client_list.name}}
                      </li>
                    </ul>
                  </div>
                </div>
              </article>
              <v-waypoint @waypoint-in="activateSection('values')"></v-waypoint>
              <article class="values bg-change break-container">
                <h1 id="values">{{ page.acf.values_headline }}</h1>
                <div v-html="page.acf.values_body"></div>
                <ul class="list-unstyled">
                  <li v-for="value in page.acf.values_">
                    <div class="media">
                      <img :src="value.value_icon.url" class="d-flex mr-3" width="50px">
                      <div class="media-body">
                        <h3>{{ value.value_name }}</h3>
                        <div v-html="value.value_description"></div>
                      </div>
                    </div>
                  </li>
                </ul>
              </article>
              <v-waypoint @waypoint-in="activateSection('team')"></v-waypoint>

              <article class="break-container bg-white team py-5">
                <h1 id="team">{{page.acf.team_headline}}</h1>
                <div v-html="page.acf.team_body" class="mb-4-5"></div>

                <div class="row">
                  <div v-for="member in page.acf.team" class="col-sm-6 col-lg-4">
                    <nuxt-link :key="member.id" :to=" {name: 'about-slug', params: {slug: member.team_member.user_nicename}}" class="team-member">
                      <div class="col-image">

                        <div :style="{ 'background-image': 'url(' + teamMemberBySlug(member.team_member.user_nicename).headshot.url + ')' }" class="featured-image"></div>
                      </div>
                      <div>
                        <h4 class="mt-3 mb-1">
                          <span class="underline-change">{{teamMemberBySlug(member.team_member.user_nicename).headshot.title}}</span>
                        </h4>
                        <h6>{{ teamMemberBySlug(member.team_member.user_nicename).job_title }}</h6>
                      </div>
                    </nuxt-link>
                  </div>
                </div>
              </article>
              <v-waypoint @waypoint-in="activateSection('open-house')"></v-waypoint>

              <article v-if="openHouse" class="openHouse my-5">
                <h1 id="open-house">Open House</h1>
                <div v-html="page.acf.open_house_body" class="mb-5"></div>
                <div class="" v-for="(event, index) in openHouse">
                  <Event :entry="event" :index="index" :relatedTeamMembers="event.related_team_members.data"></Event>
                </div>
              </article>
              <v-waypoint @waypoint-in="activateSection('jobs')"></v-waypoint>

              <article id="jobs" class="mb-5">
                <h1>{{ page.acf.jobs_headline }}</h1>
                <div v-html="page.acf.jobs_body" class="mb-3"></div>
                <div v-if="jobs">
                  <div v-for="(job, index) in jobs" class="collapse-block">
                    <div :class="{'active': openJobs[job.id]}">
                      <h3>
                        <a href="#" class="underline-change" @click.prevent="toggleJob(job.id)">{{job.title.rendered}}</a>
                      </h3>
                    </div>
                    <div class="collapse-content" :class="{'show': openJobs[job.id]}">
                      <div class="job-description">
                        <h5 v-html="job.acf.job_description_heading" v-if="job.acf.job_description_heading" class="mt-3"></h5>
                        <div v-html="job.acf.job_description" v-if="job.acf.job_description"></div>
                        <h5 v-html="job.acf.requirements_heading" v-if="job.acf.requirements_heading" class="mt-3"></h5>
                        <div v-html="job.acf.requirements_body" v-if="job.acf.requirements_body"></div>
                        <h5 v-html="job.acf.how_to_apply_heading" v-if="job.acf.how_to_apply_heading" class="mt-3"></h5>
                        <div v-html="job.acf.how_to_apply_body" v-if="job.acf.how_to_apply_body"></div>
                        <a class="btn btn-primary mb-3" :href="job.acf.apply_link" v-if="job.acf.apply_link">Learn more and apply</a>
                      </div>
                    </div>
                  </div>
                </div>
              </article>

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
import Axios from "axios";  
import Event from "~/components/Event.vue";
import Chat from "~/components/Chat.vue";
import Vue from "vue";

export default {
  name: "about",
  components: {
    Event,
    Chat
  },
  head() {
    if (this.page) {
      return {
        title: this.page.acf.we_believe_headline,
        meta: [
          {
            hid: "og:title",
            property: "og:title",
            content: this.page.acf.we_believe_headline
          },
          {
            hid: "twitter:title",
            property: "twitter:title",
            content: this.page.acf.we_believe_headline
          },
          {
            hid: "description",
            name: "description",
            content: this.page.acf.we_believe_body
          },
          {
            hid: "og:description",
            property: "og:description",
            content: this.page.acf.we_believe_body
          },
          {
            hid: "twitter:description",
            property: "twitter:description",
            content: this.page.acf.we_believe_body
          },
          {
            hid: "image",
            property: "image",
            content: this.page.acf.featured_image.url
          },
          {
            hid: "og:image:url",
            property: "og:image:url",
            content: this.page.acf.featured_image.url
          },
          {
            hid: "twitter:image",
            property: "twitter:image",
            content: this.page.acf.featured_image.url
          }
        ]
      };
    }
  },
  data() {
    return {
      scrollPos: 0,
      activeSection: "we-believe"
    };
  },
  async asyncData({ store, query, dispatch }) {
    let data = {};
    let page = await Axios.get(
      store.getters["hostname"] + "wp/v2/pages?slug=about"
    );
    let openCategories = {};

    page.data[0].acf.clients.forEach(client => {
      openCategories[client.client_category] = false;
    });
    data["openCategories"] = openCategories;
    data["pageObject"] = page.data;
    let [team, jobs, openHouse] = await Promise.all([
      store.dispatch("fetch", "familiar/v1/team"),
      store.dispatch("fetch", "wp/v2/bd_job"),
      store.dispatch("fetch", "wp/v2/bd_event?event_category=31")
    ]);
    let openJobs = {};
    jobs.data.forEach(job => {
      openJobs[job.id] = false;
    });
    data["openJobs"] = openJobs;
    data["team"] = team.data;
    data["jobs"] = jobs.data;

    // arrange clients into sectors:
    const sectors = store.state.sectors.sort();
    data["openHouse"] = openHouse.data;
    return data;
  },
  computed: {
    page() {
      return this.pageObject[0];
    },
    sectorsByIndex() {
      return this.$store.getters["getSectorsIndexedById"];
    }
  },
  methods: {
    activateSection(sectionName) {
      this.activeSection = sectionName;
    },
    toggleClient(categoryId) {
      this.openCategories[categoryId] = !this.openCategories[categoryId];
    },
    toggleJob(jobId) {
      this.openJobs[jobId] = !this.openJobs[jobId];
    },
    teamMemberBySlug(slug) {
      return this.team.filter(teamMember => {
        return teamMember.slug === slug;
      })[0];
    }
  }
};
</script>
