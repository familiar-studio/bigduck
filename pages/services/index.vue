<template>
  <div>
    <div v-if="servicesPage">
      <div class="img-hero" :style=" { backgroundImage: 'url(' + servicesPage.acf.featured_image.url + ')' }">
        <figcaption class="figure-caption">{{servicesPage.acf.featured_image.caption}}</figcaption>
      </div>
  
      <div id="content">
        <div class="container">
          <article class='main bg-white overlap' v-html="servicesPage.acf.text">
          </article>
          <div class="pt-5">
            <h2 v-html="servicesPage.acf.services_heading"></h2>
            <div v-for="service in services" v-if="service.slug !== 'brandraising-benchmark'">
              <Service :entry="service"></Service>
            </div>
            <h2 v-html="servicesPage.acf.brandraising_benchmark_heading" class="mt-5"></h2>
            <div v-for="service in services" v-if="service.slug === 'brandraising-benchmark'">
              <Service :entry="service"></Service>
            </div>
          </div>
          <Subscribe v-if="callouts" :entry="callouts[0]" class="mt-5"></Subscribe>
        </div>
        <div class="testimonial">
          <div class="container">
            <div class="row">
              <div class="col-md-8">
                <blockquote>
                  <h3 v-html="servicesPage.acf.quote"></h3>
                  <footer class="label">&mdash;
                    <span v-html="servicesPage.acf.credit"></span>
                  </footer>
                </blockquote>
              </div>
              <div v-if="servicesPage.acf.image" class="col-md-4">
                <img :src="servicesPage.acf.image.sizes.cropped_400_square" alt="block.image.name" class="img-fluid">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import Service from '~components/Service.vue'
import Subscribe from '~components/subscribe/container.vue'

export default {
  name: 'services',
  head() {
    return {
      title: 'Services',
      meta: [
        { description: this.servicesPage.acf.services_heading },
        { 'og:image': this.servicesPage.acf.featured_image.url }
      ]
    }
  },
  async asyncData({ store, query, state }) {
    const [page, services] = await Promise.all([
      store.dispatch('fetchByQuery', { query: query, path: 'wp/v2/pages?slug=services' }),
      store.dispatch('fetchByQuery', { query: query, path: 'wp/v2/bd_service' })
    ])
    let data = {}
    data['servicesPage'] = page.data[0]
    data['services'] = services.data.reverse()
    return data
  },
  components: { Service, Subscribe },
  computed: {
    callouts() {
      return this.$store.state.callouts
    }
  }
}
</script>
