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
    } else {
      return redirect("/insights/" + params.page);
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
          {
            hid: "og:title",
            property: "og:title",
            content: this.title
          },
          {
            hid: "twitter:title",
            property: "twitter:title",
            content: this.title
          },
          {
            hid: "description",
            name: "description",
            content: this.content
          },
          {
            hid: "og:description",
            content: this.content
          },
          {
            hid: "twitter:description",
            property: "twitter:description",
            content: this.content
          },
          {
            hid: "image",
            property: "image",
            content: this.image
          },
          {
            hid: "og:image:url",
            property: "og:image:url",
            content: this.image
          },
          {
            hid: "twitter:image",
            property: "twitter:image",
            content: this.image
          }
        ]
      };
    }
  }
};
</script>
