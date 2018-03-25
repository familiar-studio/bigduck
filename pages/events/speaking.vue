<template>
  <Page :title="title" :formId="formId" :content="content" :image="image">
  </Page>
</template>
<script>
import Page from "~/components/Page";

export default {
  name: "contact",
  head() {
    if (this.title && this.image) {
      return {
        title: this.title,
        meta: [
          ...this.$metaDescription("Interested in having Big Duck speak at your organization?"),
          ...this.$metaTitles(this.title),
          ...this.$metaImages(this.image)
        ]
      };
    }
  },
  async asyncData({ app, store }) {
    let response = await app.$axios.$get("/wp/v2/pages?slug=speaking-engagements");
    var data = response[0];
    return {
      data: data,
      image: data.acf.featured_image,
      formId: data.acf.form,
      title: data.title.rendered,
      content: data.content.rendered
    };
  },
  components: {
    Page
  }
};
</script>
