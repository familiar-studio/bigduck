<template>
  <div>

    <div class="jumbotron" id="hero-animation">
      <div class="container">

        <h1 class="display-2">
          <span>
            Developing
          </span>
          <br class="hidden-sm-up" />
          <span>
            the
          </span>
          <span class="color"> {{wordString}}</span>
          <div class="cursor-wrapper">
            <span class="cursor bg-change"></span>
          </div>
          <br/>
          <span> of</span>
          <span> determined</span>
          <span> nonprofits.</span>
        </h1>
        <router-link class="btn btn-primary" to="/services">See how we do it &rarr;</router-link>
      </div>
    </div>
    <div id="below-hero-animation">

      <Featured v-for="(caseStudy, index) in relatedCaseStudies" :work="caseStudy" :index="index" :key="caseStudy.slug"></Featured>
      <div class="testimonial break-container mb-5">
        <div class="container">
          <blockquote>
            <h3 v-html="page.acf.testimonial"></h3>
            <footer class="label">&mdash;
              <span v-html="page.acf.citation"></span>
            </footer>
          </blockquote>
        </div>
      </div>
      <div class="row no-gutters">

        <div class="col-lg-10 offset-lg-1 col-xl-8 offset-xl-2">
          <div class="container">
            <div v-if="upcomingEvents" class="">
              <h2 class="mb-3">Featured Events</h2>

              <div class="" v-for="(event, index) in upcomingEvents">
                <Event :entry="event" index="index"></Event>
              </div>
              <nuxt-link class="btn btn-primary" to="/events">View All Events</nuxt-link>
            </div>
          </div>

          <div class="container">
            <div v-if="latestInsights" class="my-5">
              <h2 class="mb-3">Recent Insights</h2>

              <div class="" v-for="(insight, index) in latestInsights">
                <Post :entry="insight" :index="index + latestInsights.length"></Post>
              </div>
              <nuxt-link class="btn btn-primary" to="/insights">View All Insights</nuxt-link>
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
import Featured from "~/components/Featured.vue";
import Event from "~/components/Event.vue";
import Post from "~/components/Post.vue";
import Chat from "~/components/Chat.vue";

import { mapState, mapGetters } from "vuex";

export default {
  components: {
    Event,
    Featured,
    Post,
    Chat
  },
  head() {
    return {
      title: "Big Duck",
      titleTemplate: null,
      meta: [
        {
          hid: "og:title",
          property: "og:title",
          content: "Big Duck"
        },
        {
          hid: "twitter:title",
          property: "twitter:title",
          content: "Big Duck"
        },
        {
          hid: "description",
          name: "description",
          content:
            "Big Duck develops the voices of nonprofit organizations by developing strong brands, campaigns, and communications teams."
        },
        {
          hid: "og:description",
          property: "og:description",
          content:
            "Big Duck develops the voices of nonprofit organizations by developing strong brands, campaigns, and communications teams."
        },
        {
          hid: "twitter:description",
          property: "twitter:description",
          content:
            "Big Duck develops the voices of nonprofit organizations by developing strong brands, campaigns, and communications teams."
        }
      ]
    };
  },
  async asyncData({ store }) {
    let data = {};
    let response = await Axios.get(
      store.getters["hostname"] + "wp/v2/pages/37"
    );
    let page = response.data;
    if (page && page.acf) {
      data.relatedWorkIds = page.acf.featured_case_studies.map(work => {
        return work.ID;
      });
      if (data.relatedWorkIds) {
        await Axios.get(store.getters["hostname"] + "wp/v2/bd_case_study", {
          params: { include: data.relatedWorkIds }
        }).then(response => {
          let orderedCaseStudies = [];
          data.relatedWorkIds.forEach((id, index) => {
            orderedCaseStudies[index] = response.data.find(case_study => {
              return case_study.id === id;
            });
          });
          data.relatedCaseStudies = orderedCaseStudies;
        });
      }
      return {
        page: response.data,
        ...data,
        upcomingEventIds: page.acf.upcoming_events.map(event => {
          return event.ID;
        }),
        latestInsightIds: page.acf.latest_insights.map(insight => {
          return insight.ID;
        })
      };
    }
  },
  data() {
    return {
      wordString: "voices",
      words: ["voices", "brands", "campaigns", "teams"],
      wordIndex: 0,
      letterIndex: 6,
      timeWaited: 0,
      interval: null,
      typingStatus: "waiting",
      looped: false,
      // same as color animation
      totalWordTypeTime: 5000,

      // ANIMATION OPTIONS:
      // how many milliseconds a 'frame' lasts. a letter is typed or deleted in 1 frame
      frameInterval: 100,
      // how many frames to wait after a word has been typed before starting to delete it
      // setting loop to false will stop typing after word returns to 'voices'
      loop: true,
      relatedCaseStudies: null,
      upcomingEvents: null,
      latestInsights: null
    };
  },
  computed: {
    ...mapGetters(["hostname"]),
    word() {
      return this.words[this.wordIndex];
    },
    waitingIntervalFrames() {
      // the number of letters to typed and deleted, one frame to change word, times the let of a frame
      return (
        this.totalWordTypeTime - this.frameInterval * (2 * this.word.length + 1)
      );
    }
  },
  created() {
    this.interval = setInterval(this.nextFrame, this.frameInterval);

    if (this.upcomingEventIds) {
      Axios.get(this.hostname + "wp/v2/bd_event", {
        params: { include: this.upcomingEventIds }
      }).then(response => {
        this.upcomingEvents = response.data;
      });
    }
    if (this.latestInsightIds) {
      Axios.get(this.hostname + "wp/v2/bd_insight", {
        params: { include: this.latestInsightIds }
      }).then(response => {
        this.latestInsights = response.data;
      });
    }
  },
  methods: {
    nextFrame() {
      switch (this.typingStatus) {
        case "typing":
          if (this.letterIndex < this.words[this.wordIndex].length) {
            this.letterIndex++;
          } else {
            this.typingStatus = "waiting";
            this.timeWaited = 0;
          }
          break;
        case "waiting":
          if (this.timeWaited < this.waitingIntervalFrames) {
            this.timeWaited += this.frameInterval;
          } else if (this.loop || !this.looped) {
            this.typingStatus = "deleting";
          }
          break;
        case "deleting":
          if (this.letterIndex > 0) {
            this.letterIndex--;
          } else {
            this.typingStatus = "changingWord";
          }
          break;
        default:
          this.wordIndex = (this.wordIndex + 1) % this.words.length;
          this.looped = this.wordIndex === 0;
          this.typingStatus = "typing";
          break;
      }
      this.wordString = this.word.substring(0, this.letterIndex);
    }
  }
};
</script>
