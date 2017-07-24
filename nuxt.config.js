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
        hid: "description",

        name: "description",
        content: "Effective branding, fundraising, and marketing for nonprofits"
      },
      {
        hid: "twittersite",
        name: "twitter:site",
        content: "@bigduck"
      }
    ],
    link: [{ rel: "apple-touch-icon", sizes:"152x152", href:"/favicon.png" }]
  },
  cache: false,
  transition: {
    name: "page",
    mode: "out-in",
    appear: true
  },
  plugins: [
    { src: "~plugins/vue-validate", ssr: false },
    { src: "~plugins/newfangled.js", ssr: false },
    //{ src: "~plugins/ga.js", ssr: false },
    { src: "~plugins/scrollto.js", ssr: false },
    { src: "~plugins/waypoint.js", ssr: false }
  ],
  /*
  ** Customize the progress-bar color
  */
  loading: "components/loading.vue",
  router: {
    middleware: ["nextCta", "redirects"],
    scrollBehavior: function(to, from, savedPosition) {
      return { x: 0, y: 0 };
    }
  },
  css: [{ src: "~assets/scss/styles.scss", lang: "scss" }],
  /*
  ** Build configuration
  */

  build: {
    /*
    ** Run ESLINT on save
    */
    vendor: ["axios", "date-fns"],
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
