<template>
  <Page :title="title" :formId="formId" :content="content" :image="image">
  </Page>
</template>
<script>
import Axios from "axios";
import Page from "~/components/Page";

export default {
  name: "contact",
  head() {
    if (this.title && this.image) {
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
            content: "Interested in having Big Duck speak at your organization?"
          },
          {
            hid: "og:description",
            property: "og:description",
            content: "Interested in having Big Duck speak at your organization?"
          },
          {
            hid: "twitter:description",
            property: "twitter:description",
            content: "Interested in having Big Duck speak at your organization?"
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
  },
  async asyncData({ store }) {
    let response = await Axios.get(
      store.getters["hostname"] + "wp/v2/pages?slug=speaking-engagements"
    );
    var data = response.data[0];
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
