module.exports = {
  /*
  ** Headers of the page
  */
  head: {
    title: " ",
    titleTemplate: "%s | Big Duck",
    meta: [
      { charset: "utf-8" },
      { name: "viewport", content: "width=device-width, initial-scale=1" },
      {
        name: "google-site-verification",
        content: "q5-HejTljPFYvH9amyqStrGWAKuL_GUrkR1MhDRVRjE"
      },
      {
        property: "og:title",
        content: "Big Duck"
      },
      {
        property: "twitter:title",
        content: "Big Duck"
      },
      {
        hid: "description",
        property: "description",
        content:
          "Big Duck develops the voices of nonprofit organizations by developing strong brands, campaigns, and communications teams."
      },
      {
        property: "og:description",
        content:
          "Big Duck develops the voices of nonprofit organizations by developing strong brands, campaigns, and communications teams."
      },
      {
        property: "twitter:description",
        content:
          "Big Duck develops the voices of nonprofit organizations by developing strong brands, campaigns, and communications teams."
      },
      {
        property: "image",
        content:
          "/wp-content/uploads/2017/07/28546982-bf3e1ad0-709a-11e7-9b12-3b5d1238669f.png"
      },
      {
        property: "og:image:url",
        content:
          "/wp-content/uploads/2017/07/28546982-bf3e1ad0-709a-11e7-9b12-3b5d1238669f.png"
      },
      {
        property: "twitter:image",
        content:
          "/wp-content/uploads/2017/07/28546982-bf3e1ad0-709a-11e7-9b12-3b5d1238669f.png"
      }
    ],
    link: [
      { rel: "icon", type: "image/png", href: "/favicon.png", sizes: "16x16" }
    ],
    script: [
      { src: "https://hi.bigducknyc.com/cdnr/30/acton/bn/tracker/4852" }
    ],
    __dangerouslyDisableSanitizers: ["title", "description"]
  },
  cache: true,
  transition: {
    name: "page",
    mode: "out-in",
    appear: true
  },
  plugins: [
    { src: "~/plugins/vue-validate", ssr: false },
    { src: "~/plugins/newfangled.js", ssr: false },
    { src: "~/plugins/ga.js", ssr: false },
    { src: "~/plugins/scrollto.js", ssr: false },
    { src: "~/plugins/waypoint.js", ssr: false }
  ],
  modules: [
    // Simple usage
    "@nuxtjs/axios"
  ],
  axios: {
    credentials: false,
    baseURL: "https://bigducknyc.com/wp-json/"
  },
  /*

  ** Customize the progress-bar color
  */
  loading: "~/components/loading.vue",
  router: {
    middleware: ["nextCta", "redirects"],
    scrollBehavior: function(to, from, savedPosition) {
      return { x: 0, y: 0 };
    }
  },
  css: [{ src: "~/assets/scss/styles.scss", lang: "scss" }],
  /*
  ** Build configuration
  */

  build: {
    /*
    ** Run ESLINT on save
    */
    vendor: ["axios", "date-fns", "crypto-js", "flickity"],
    extend(config, ctx) {
      if (ctx.isClient) {
        config.module.rules.push({
          enforce: "pre",
          test: /\.(js|vue)$/,
          loader: "eslint-loader",
          exclude: /(node_modules)/
        });
      }
    }
  }
};
