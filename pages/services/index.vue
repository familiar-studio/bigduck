<template>
  <div>
    <div v-if="servicesPage">
      <transition name="fade" appear>
        <div class="img-hero" :style=" { backgroundImage: 'url(' + servicesPage.acf.featured_image.url + ')' }">

        </div>
      </transition>

      <transition name="slideUp" appear>
        <div class="container bg-white overlap">
          <article class='main' v-html="servicesPage.acf.text">
          </article>
        </div>
      </transition>
      <div class="container">
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
      <div class="testimonial">
        <div class="container">
          <div class="row">
            <blockquote class="col-md-9">
              <h3 v-html="servicesPage.acf.quote"></h3>
              <footer class="label">&mdash;<span v-html="servicesPage.acf.credit"></span></footer>
            </blockquote>
            <img :src="servicesPage.acf.image.url" class="col-md-3 testimonialImage" v-if="servicesPage.acf.image"></img>
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
    async asyncData ({store, query, state}) {
      const [page, services] = await Promise.all([
        store.dispatch('fetchByQuery', {query: query, path: 'wp/v2/pages?slug=services'}),
        store.dispatch('fetchByQuery', {query: query, path: 'wp/v2/bd_service'})
      ])
      let data = {}
      data['servicesPage'] = page.data[0]
      data['services'] = services.data
      return data
    },
    components: {Service, Subscribe},
    computed: {
      callouts () {
        return this.$store.state.callouts
      }
    }
  }
</script>
<style>
  .testimonialImage {
    height: 100%;
  }
</style>
