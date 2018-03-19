<template>
  <div v-if="insight">

    <div class="img-hero" v-if="insight && insight.acf.featured_image" :style=" { backgroundImage: 'url(' + insight.acf.featured_image + ')' }">
      <figcaption class="figure-caption">{{insight.acf.featured_image.caption}}</figcaption>
    </div>
    <div class="img-hero" v-else :style=" { backgroundImage: 'url(' + backupImage + ')' }">
      <figcaption class="figure-caption"></figcaption>
    </div>
    <div>
      <div class="row no-gutters">
        <div class="col-lg-1 col-xl-2 hidden-md-down">
          <Share></Share>
        </div>
        <div class="col-xl-8 col-lg-10">
          <div class="container overlap">
            <article class="main">
              <div class="badge-group">
                <nuxt-link class="badge badge-default underline-change overview-link" :to="{name: 'insights'}">
                  Insights
                </nuxt-link>
                <div class="badge badge-default" v-for="topic in insight.topic" v-if="topics && getTopicsIndexedById[topic]">
                  <img :src="getTopicsIndexedById[topic].acf.icon">
                  <div v-html="getTopicsIndexedById[topic].name"></div>
                </div>
                <div class="badge badge-default">
                  <span v-if="types && insight.type[0]">
                    <div v-if="insight.type[0] == '19'">
                      {{insight.calculated_reading_time.data}} Read
                    </div>

                  </span>
                </div>
                <div class="badge badge-default">
                  {{ date }}
                </div>
              </div>

              <h1 v-html="insight.title.rendered">
              </h1>
              <div class="author-listing" v-if="insight.acf.author.length > 0 || insight.acf.guest_author_name">
                <div class="badge badge-default mb-3" v-if="insight.author_headshots" v-for="author in insight.acf.author">
                  <img v-if="insight.author_headshots[author.user_nicename].sizes" :src="insight.author_headshots[author.user_nicename].sizes.thumbnail" class="round author-img mr-2">
                  <img v-else :src="globals.backup_author_image" class="round author-img mr-2">
                  <div>
                    <nuxt-link :to="'/about/' + author.user_nicename">{{author.display_name}}</nuxt-link>
                  </div>
                </div>

                <div>
                  <div v-if="insight.acf.guest_author_name" class="badge badge-default mb-3">
                    <img :src="globals.backup_author_image" class="round author-img mr-2">
                    <div>
                      {{insight.acf.guest_author_name}}
                    </div>
                  </div>
                </div>
              </div>
              <div v-if="!insight.acf.guest_author_name && insight.acf.author.length < 1" class="badge badge-default mb-3 author-no-img">
                <span>Big Duck</span>
              </div>

              <div v-for="block in insight.acf.body">

                <div v-if="block.acf_fc_layout == 'text'" v-html="block.text" :class="['block-' + block.acf_fc_layout]"></div>
                <div v-if="block.acf_fc_layout == 'quote'" class="block-pullquote">
                  <blockquote>
                    <div v-html="block.quote" class="text"></div>
                    <footer class="label mt-3" v-if="block.credit">
                      &mdash; {{block.credit}}
                    </footer>
                  </blockquote>
                </div>

                <div v-if="block.acf_fc_layout == 'video'" :class="['block-' + block.acf_fc_layout]" class="embed-responsive embed-responsive-16by9" v-html="block.video">
                </div>

                <div v-if="block.acf_fc_layout == 'callout'" class="block-pullquote">
                  <div v-html="block.text" class="text"></div>
                </div>

                <!-- GALLERY  -->
                <div v-if="block.acf_fc_layout == 'gallery'" class="cs-block-gallery break-container overflow-x-hidden">
                  <div class="">
                    <flickity :images="block.gallery"></flickity>
                  </div>
                </div>
                <div class="" v-if="block.acf_fc_layout == 'image'" class="mb-5">

                  <img :src="block.image" style="width: 100%;">
                  <figcaption v-if="block.caption" class="figure-caption mt-1">{{ block.caption }}</figcaption>
                </div>
              </div>

              <div class="hidden-lg-up mt-4">
                <Share></Share>
              </div>
            </article>
            <div v-if="insight && insight.acf.is_gated_content">

              <div class="form-light">

                <GravityForm v-if="!completedGate" :formId="7" :gatedContent="insight.id" :title="insight.title.rendered" :id="insight.id" @submitted="refreshContent()" storagePrefix="insight-"></GravityForm>

                <template v-if="viewGatedContent">
                  <div v-html="insight.acf.gated_content_text"></div>

                  <a :href="downloadUrl" v-if="downloadUrl" class="btn btn-primary" target="_blank">
                    {{insight.acf.gated_download_button_text}}
                  </a>
                </template>
              </div>
            </div>

            <article class="mb-5">

              <div v-if="insight.acf.author.length > 0" v-for="(author, index) in insight.acf.author">
                <div class="author-bio">
                  <div class="row">
                    <div class="col-md-2 author-bio-pic">
                      <img class="round" v-if="insight.author_headshots[author.user_nicename].sizes" :src="insight.author_headshots[author.user_nicename].sizes.thumbnail" alt="" />
                    </div>
                    <div class="col-md-10 author-bio-text" v-if="authorMetaById">
                      <h3 v-if="authorMetaById[author.ID]">
                        {{author.display_name}} is {{authorMetaById[author.ID].acf.job_title_a_an_the}} {{authorMetaById[author.ID].acf.job_title}} at Big Duck
                      </h3>

                      <nuxt-link class="btn btn-primary" :to="{name: 'about-slug', params: { slug: author.user_nicename}}">
                        More about {{author.user_firstname}}
                      </nuxt-link>
                    </div>

                  </div>
                </div>
              </div>
            </article>

            <div class="mb-5" v-if="relatedCaseStudies">
              <h2>Related Case Studies</h2>
              <div class="row">
                <div v-for="case_study in relatedCaseStudies" class="col-md-6">
                  <nuxt-link :to="{name: 'work-slug', params: {slug: case_study.slug}}" :key="case_study.ID" class="block-work-small">
                    <div v-if="case_study.acf.hero_image" class="img-wrapper">
                      <img :src="case_study.acf.hero_image.sizes.cropped_rectangle" class="img-fluid" />
                    </div>
                    <div class="card">
                      <div class="card-block">
                        <div class="badge-group" v-if="topics && types">
                          <div class="badge badge-default" v-for="topic in case_study.topic">
                            <div v-html="getTopicsIndexedById[topic].icon" class="img-fluid"></div>
                            <div v-html="getTopicsIndexedById[topic].name"></div>
                          </div>
                        </div>
                        <h3 class="card-title">
                          <span class="underline-change hover-color">{{ case_study.acf.client_name }}</span>
                        </h3>
                        <div class="card-text" v-html="case_study.acf.short_description"></div>
                      </div>
                    </div>
                  </nuxt-link>
                </div>
              </div>
            </div>

            <div class="mb-5" v-if="relatedInsights">
              <h2>Related Insights</h2>
              <div v-if="relatedInsights">
                <div v-for="(insight, index) in relatedInsights">
                  <Post :entry="insight" :index="index"></Post>
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
import dateFns from "date-fns";
import GravityForm from "~/components/GravityForm.vue";
import { mapState, mapGetters, mapActions } from "vuex";
import Post from "~/components/Post.vue";
import Share from "~/components/Share.vue";
import Chat from "~/components/Chat.vue";
import flickity from "~/components/Flickity.vue";

export default {
  name: "insight",
  components: {
    Share,
    GravityForm,
    Post,
    Chat,
    flickity
  },
  data() {
    return {
      relatedCaseStudies: null,
      relatedInsights: null,
      authors: null,
      contentRefreshed: false,
      completedGate: false
    };
  },
  async asyncData({ app, state, params, store, error, query }) {
    let data = {};
    try {
      let requestParams = { slug: params.slug }

      if (query.preview === "true") {
        requestParams['status'] = 'draft'
      }

      let response = await app.$axios.$get("/wp/v2/bd_insight",
        {
          params: requestParams,
          withCredentials: query.preview === "true"
         }
      );
      data.insight = response[0];
      if (data.insight.acf.related_case_studies) {
        data.relatedWorkIds = data.insight.acf.related_case_studies.map(
          caseStudy => {
            return caseStudy.ID;
          }
        );
      }
      if (data.insight.acf.related_insights) {
        data.relatedInsightIds = data.insight.acf.related_insights.map(
          insight => {
            return insight.ID;
          }
        );
      }
      if (data.insight.acf.author.length > 0) {
        data.authorIds = data.insight.acf.author.map(author => {
          return author.ID;
        });
      }
      return data;
    } catch (e) {
      error({ statusCode: 404, message: "Post not found" });
    }
  },
  head() {
    if (this.insight && this.insight.acf) {
      return {
        title: this.title,
        meta: [
          {
            hid: "og:title",
            property: "og:title",
            content: this.title + " | Big Duck"
          },
          {
            hid: "twitter:title",
            property: "twitter:title",
            content: this.title + " | Big Duck"
          },
          {
            hid: "description",
            name: "description",
            content: this.description
          },
          {
            hid: "og:description",
            property: "og:description",
            content: this.description
          },
          {
            hid: "twitter:description",
            property: "twitter:description",
            content: this.description
          },
          {
            hid: "image",
            property: "image",
            content: this.insight.acf.featured_image
          },
          {
            hid: "og:image:url",
            property: "og:image:url",
            content: this.insight.acf.featured_image
          },
          {
            hid: "twitter:image",
            property: "twitter:image",
            content: this.insight.acf.featured_image
          },
          {
            name: "og:type",
            property: "og:type",
            content: "article"
          },
          {
            name: "twitter:card",
            property: "twitter:card",
            content: "summary"
          }
        ]
      };
    }
  },
  computed: {
    ...mapState(["types", "topics", "globals"]),
    ...mapGetters(["hostname", "getTopicsIndexedById", "getTypesIndexedById"]),
    backupImage() {
      let images = this.globals.backup_insights_images;
      return images[this.insight.id % images.length].backup_insight_image;
    },
    date() {
      return dateFns.format(this.insight.date, "MMM D, YYYY");
    },
    formId() {
      if (process.browser) {
        var parser = new DOMParser();
        var doc = parser.parseFromString(
          this.insight.content.rendered,
          "text/html"
        );
        var formId = doc.getElementById("form-id");
        if (formId) {
          return Number(formId.innerHTML);
        } else {
          return false;
        }
      }
      return null;
    },
    authorsById() {
      if (this.insight) {
        let authors = {};
        this.insight.acf.author.forEach(author => {
          authors[author.ID] = author;
        });
      }
      return authors;
    },
    authorMetaById() {
      if (this.authors) {
        let authorsById = {};
        this.authors.forEach(author => {
          authorsById[author.id] = author;
        });
        return authorsById;
      }
    },
    description() {
      return this.insight.acf.meta_description
        ? this.insight.acf.meta_description.slice(0, 160)
        : this.insight.acf.short_description.slice(0, 160);
    },
    title() {
      return this.insight.acf.meta_title
        ? this.insight.acf.meta_title
        : this.insight.title.rendered;
    },
    downloadUrl() {
      if (this.insight.acf.gated_content_download_url) {
        return this.insight.acf.gated_content_download_url;
      } else {
        if (this.insight.acf.gated_download) {
          return this.insight.acf.gated_download.url;
        }
      }

      return null;
    },
    viewGatedContent() {
      if (this.completedGate || this.contentRefreshed) {
        return true;
      }

      return false;
    }
  },
  created() {
    // get related case studies
    if (this.relatedWorkIds) {
      this.$axios.$get("/wp/v2/bd_case_study", {
        params: { include: this.relatedWorkIds }
      }).then(response => {
        this.relatedCaseStudies = response;
      });
    }
    if (this.relatedInsightIds) {
      this.$axios.$get("/wp/v2/bd_insight", {
        params: { include: this.relatedInsightIds }
      }).then(response => {
        this.relatedInsights = response;
      });
    }
    if (this.authorIds) {
      this.$axios.$get("/acf/v3/users", {
        params: { include: this.authorIds }
      }).then(response => {
        this.authors = response;
      });
    }

    // }
  },
  mounted() {
    if (
      process.browser &&
      this.insight &&
      typeof localStorage !== "undefined"
    ) {
      //figure out whether the user has filled out the form from the localstorage
      if (localStorage["insight-" + this.insight.id]) {
        this.completedGate = true;
      }
    }
  },
  methods: {
    ...mapActions(["fetch"]),
    prependIndefiniteArticle(word) {
      if (word) {
        if ("aeiou".indexOf(word.split("")[0].toLowerCase()) > -1) {
          return "an " + word;
        } else {
          return "a " + word;
        }
      }
      return null;
    },
    refreshContent() {
      this.contentRefreshed = true;
    }
  }
};
</script>
