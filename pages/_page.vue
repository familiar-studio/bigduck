<template>
  <Page :title="title" :formId="formId" :content="content" :image="image">
  </Page>
</template>

<script>
import Page from "~/components/Page";

export default {
  // if url doesnt exist try and find it in insights
  async asyncData({ app, store, redirect, params }) {
    const response = await app.$axios.$get("/wp/v2/pages", {
      params: { slug: params.page }
    })
    if (response.length > 0) {
      var data = response[0];
      return {
        data: data,
        image: data.acf.featured_image,
        formId: data.acf.form,
        title: data.title.rendered,
        content: data.content.rendered
      };
    // } else {
    //   return redirect("/insights/" + params.page);
    }
  },
  components: {
    Page
  },
  head() {
    if (this.title && this.title) {
      return {
        title: this.title,
        meta: [
          ...this.$metaDescription(this.title),
          ...this.$metaTitles(this.title),
          ...this.$metaImages(this.image)
        ]
      };
    }
  }
};
</script>
