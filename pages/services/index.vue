<template>
  <div>

    <div class="img-hero" :style=" { backgroundImage: 'url(' + servicesPage.acf.featured_image.url + ')' }">
      <figcaption class="figure-caption">{{servicesPage.acf.featured_image.caption}}</figcaption>
    </div>
    <div class="row no-gutters">
      <div class="col-lg-1 col-xl-2">
      </div>
      <div class="col-lg-10 col-xl-8">
        <div class="container">
          <div id="content">
            <article class='main bg-white overlap' v-html="servicesPage.acf.text">
            </article>
            <div class="pt-5">
              <h2 v-html="servicesPage.acf.services_heading" class="mb-3"></h2>
              <Service v-for="(service, index) in otherServices" :entry="service" :index="index" :key="service"></Service>
              <h2 v-html="servicesPage.acf.brandraising_benchmark_heading" class="mt-5 mb-3"></h2>
              <Service :entry="brandraisingBenchmark" :index="otherServices.length"></Service>
            </div>
          </div>
          <div class="testimonial break-container">
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
      <div class="col-xl-2">

      </div>
    </div>
  </div>
</template>
<script>
import Service from '~components/Service.vue'


export default {
  name: 'services',
  components: {  Service },
  head() {
    if (this.servicesPage) {
      return {
        title: 'Services',
        meta: [
          {
            'property': 'og:title',
            'content': 'Services'
          },
          {
            'property': 'twitter:title',
            'content': 'Services'
          },
          {
            'property': 'description',
            'content': this.servicesPage.acf.text
          },
          {
            'property': 'og:description',
            'content': this.servicesPage.acf.text
          },
          {
            'property': 'twitter:description',
            'content': this.servicesPage.acf.text
          },
          {
            'property': 'image',
            'content': 'http://bigduck.familiar.studio/wordpress/wp-content/uploads/2017/07/28546982-bf3e1ad0-709a-11e7-9b12-3b5d1238669f.png'
          },
          {
            'property': 'og:image:url',
            'content': 'http://bigduck.familiar.studio/wordpress/wp-content/uploads/2017/07/28546982-bf3e1ad0-709a-11e7-9b12-3b5d1238669f.png'
          },
          {
            'property': 'twitter:image',
            'content': 'http://bigduck.familiar.studio/wordpress/wp-content/uploads/2017/07/28546982-bf3e1ad0-709a-11e7-9b12-3b5d1238669f.png'
          }
        ]
      }
    }
  },
  computed: {
    brandraisingBenchmark() {
      return this.services.filter((service) => { return service.slug === 'brandraising-benchmark' })[0]
    },

    otherServices() {
      return this.services.filter((service) => { return service.slug !== 'brandraising-benchmark' })
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
  }
}
</script>
