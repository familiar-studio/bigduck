<template>
  <div>
    <Featured v-for="(work, index) in featured" :work="work" :index="index" :key="work.slug"></Featured>
  </div>
</template>

<script>
import Featured from "~/components/Featured.vue";

export default {
  name: "featured-work",
  head() {
    if (this.featured && this.featured[0]) {
      return {
        title: "Featured Work",
        meta: [
          ...this.$metaDescription("Learn more about what we've done."),
          ...this.$metaTitles("Featured Work"),
          ...this.$metaImages(this.featured[0].acf.hero_image.url)
        ]
      };
    }
  },
  async asyncData({ app, state, store }) {
    let data = {};
    const response = await app.$axios.$get("/wp/v2/pages/50")
    let page = response;

    data.relatedWorkIds = page.acf.featured_case_studies.map(work => {
      return work.ID;
    });
    if (data.relatedWorkIds) {
      await app.$axios
        .$get("wp/v2/bd_case_study", {
          params: { include: data.relatedWorkIds }
        })
        .then(response => {
          let orderedCaseStudies = [];
          data.relatedWorkIds.forEach((id, index) => {
            orderedCaseStudies[index] = response.find(case_study => {
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
