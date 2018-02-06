<template>
  <div>
    <Featured v-for="(work, index) in featured" :work="work" :index="index" :key="work.slug"></Featured>
  </div>
</template>

<script>
import Axios from "axios"; import WPAPI from 'wpapi'; let wp = new WPAPI({ endpoint: '//bigducknyc.com/wp-json'});
import Featured from "~/components/Featured.vue";

export default {
  name: "featured-work",
  head() {
    if (this.featured && this.featured[0]) {
      return {
        title: "Featured Work",
        meta: [
          {
            hid: "og:title",
            content: "Featured Work"
          },
          {
            hid: "twitter:title",
            content: "Featured Work"
          },
          {
            hid: "description",
            content: "Learn more about what we've done."
          },
          {
            hid: "og:description",
            content: "Learn more about what we've done."
          },
          {
            hid: "twitter:description",
            content: "Learn more about what we've done."
          },
          {
            hid: "image",
            content: this.featured[0].acf.hero_image.url
          },
          {
            hid: "og:image:url",
            content: this.featured[0].acf.hero_image.url
          },
          {
            hid: "twitter:image",
            content: this.featured[0].acf.hero_image.url
          }
        ]
      };
    }
  },
  async asyncData({ state, store }) {
    let data = {};
    let response = await axios.get(
      store.getters["hostname"] + "wp/v2/pages/50"
    );
    let page = response.data;
    // let response = await axios.get(store.getters.hostname + 'familiar/v1/featured-work')
    // data.featured = response.data
    data.relatedWorkIds = page.acf.featured_case_studies.map(work => {
      return work.ID;
    });
    if (data.relatedWorkIds) {
      await axios
        .get(store.getters["hostname"] + "wp/v2/bd_case_study", {
          params: { include: data.relatedWorkIds }
        })
        .then(response => {
          let orderedCaseStudies = [];
          data.relatedWorkIds.forEach((id, index) => {
            orderedCaseStudies[index] = response.data.find(case_study => {
              return case_study.id === id;
            });
          });
          data.featured = orderedCaseStudies;
        });
    }
    return data;
  },
  components: {
    Featured
  }
};
</script>
