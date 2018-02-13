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
              <Service v-for="(service, index) in otherServices" :entry="service" :index="index" :key="service.slug"></Service>
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
import Service from "~/components/Service.vue";

export default {
  name: "services",
  components: { Service },
  head() {
    if (this.servicesPage) {
      return {
        title: "Services",
        meta: [
          ...this.$metaDescription(this.servicesPage.acf.text),
          ...this.$metaTitles("Services"),
          ...this.$metaImages()
        ]
      };
    }
  },
  computed: {
    brandraisingBenchmark() {
      return this.services.filter(service => {
        return service.slug === "brandraising-benchmark";
      })[0];
    },

    otherServices() {
      return this.services.filter(service => {
        return service.slug !== "brandraising-benchmark";
      });
    }
  },
  async asyncData({ app, store, query, state }) {
    const [page, services] = await Promise.all([
      app.$axios.$get("/wp/v2/pages?slug=services", {
        params: query
      }),
      app.$axios.$get("/wp/v2/bd_service", {
        params: query
      })
    ]);
    let data = {};
    data["servicesPage"] = page[0];
    data["services"] = services.reverse();
    return data;
  }
};
</script>
