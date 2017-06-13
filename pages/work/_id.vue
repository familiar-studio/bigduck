<template>
  <div class="container-fluid">
    <div v-if="caseStudy">
      <transition name="fade" appear>
        <div class="img-hero" :style=" { backgroundImage: 'url(' + caseStudy.acf.hero_image.url + ')' }">
        </div>
      </transition>
      <div class="row" :style="'background-image: ' + caseStudy.acf.hero_image.url">
        <div class="col-md-2">
        </div>
        <div class="col-md-8">
          <share></share>
          <transition name="slideUp" appear>
            <div class="container bg-white overlap-300">
              <article class="main">


              <h4>{{ caseStudy.acf.client_name }}</h4>
              <h2 v-html="caseStudy.acf.short_description"></h2>
              <div v-if="topics">
                <div class="badge badge-default" v-for="topic in caseStudy.topic">
                    <div v-html="topicsIndexedById[topic].acf.data"></div>
                    <div v-html="topicsIndexedById[topic].name"></div>
                </div>
              </div>
              <h3 v-if="caseStudy.acf.body[0].acf_fc_layout == 'heading'" v-html="caseStudy.acf.body[0].heading"></h3>
              <div class="row">
                <div class="col-md-10">
                  <div v-for="(block, index) in caseStudy.acf.body">
                    <div v-if="index > 0">
                      <div v-if="block.acf_fc_layout == 'text'" v-html="block.text">

                      </div>
                      <div v-if="block.acf_fc_layout == 'callout'">
                        <h2>{{ block.headline }}</h2>
                        <p v-html="block.text"></p>
                        <figure class="figure">
                          <img :src="block.image.url" class="figure-img img-fluid rounded" alt="A generic square placeholder image with rounded corners in a figure.">
                          <figcaption class="figure-caption" v-if="block.image_caption">{{ block.image_caption}}</figcaption>
                        </figure>
                        <div class="gallery" v-if="block.gallery">
                          {{ block.gallery }}
                        </div>
                        <a :href="block.website" v-if="block.website">Link</a>
                      </div>

                      <div v-if="block.acf_fc_layout == 'testimonial'">
                        <img :src="block.image.url" />
                        <div class="card">
                          <div class="card-header">
                            "
                          </div>
                          <div class="card-block">
                            <blockquote class="card-blockquote">
                              <blockquote v-html="block.quote"></blockquote>
                              <footer>{{ block.credit }}</footer>
                            </blockquote>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
                <div class="col-md-2">
                  <h5>Services Provided</h5>
                  <ul>
                    <li v-for="service in caseStudy.acf.services_provided">{{ service.service }}</li>
                  </ul>
                </div>
              </div>
              </article>
              <Subscribe v-if="callouts" :entry="callouts[0]"></Subscribe>
              <div class="mt-5" v-if="relatedCaseStudies && relatedCaseStudies.length > 0">
                <h2>Similar Case Studies</h2>
                <Work :work="relatedCaseStudies"></Work>
              </div>
            </div>

          </transition>

          </div>
          <div class="col-md-2">
            {{ caseStudy.acf.cta_text }}
          </div>
        </div>
      </div>
      <div v-else>
        Loading case study...
      </div>
    </div>
  </div>
</template>
<script>
  import Axios from 'axios'
  import share from '../../components/Share.vue'
  import Subscribe from '../../components/subscribe/container.vue'
  import Work from '../../components/Work.vue'
  import { mapState } from 'vuex'

  export default {
    name: 'case_study',
    data () {
      return {
        caseStudy: null,
        relatedCaseStudies: null
      }
    },
    components: {
      share,
      Subscribe,
      Work
    },
    computed: {
      ...mapState([
        'callouts',
        'topics',
        'types'
      ]),
      id () {
        return this.$route.params.id
      },
      topicsIndexedById () {
        return this.$store.getters['getTopicsIndexedById']
      }
    },
    async created () {
      let response = await this.$store.dispatch('fetchOne', {path: 'wp/v2/bd_case_study', id: this.id})
      this.caseStudy = response
      let relatedWorkIds = this.caseStudy.acf.related_case_studies
      if (typeof relatedWorkIds !== 'undefined' && relatedWorkIds) {
        response = await Axios.get(this.$store.getters['hostname'] + 'wp/v2/bd_case_study?' + relatedWorkIds.map((obj) => 'include[]=' + obj.ID).join('&'))
        this.relatedCaseStudies = response.data
      }
    }
  }
</script>
