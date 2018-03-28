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
        hid: "og:title",
        property: "og:title",
        content: "Big Duck"
      },
      {
        hid: "twitter:title",
        property: "twitter:title",
        content: "Big Duck"
      },
      {
        hid: "description",
        name: "description",
        content:
          "Big Duck develops the voices of nonprofit organizations by developing strong brands, campaigns, and communications teams."
      },
      {
        hid: "og:description",
        property: "og:description",
        content:
          "Big Duck develops the voices of nonprofit organizations by developing strong brands, campaigns, and communications teams."
      },
      {
        hid: "twitter:description",
        property: "twitter:description",
        content:
          "Big Duck develops the voices of nonprofit organizations by developing strong brands, campaigns, and communications teams."
      },
      {
        hid: "image",
        property: "image",
        content:
          "https://bigducknyc.com/wp-content/uploads/2017/07/28546982-bf3e1ad0-709a-11e7-9b12-3b5d1238669f.png"
      },
      {
        hid: "og:image:url",
        property: "og:image:url",
        content:
          "https://bigducknyc.com/wp-content/uploads/2017/07/28546982-bf3e1ad0-709a-11e7-9b12-3b5d1238669f.png"
      },
      {
        hid: "twitter:image",
        property: "twitter:image",
        content:
          "https://bigducknyc.com/wp-content/uploads/2017/07/28546982-bf3e1ad0-709a-11e7-9b12-3b5d1238669f.png"
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

  transition: {
    name: "page",
    mode: "out-in",
    appear: true
  },

  plugins: [
    { src: "~/plugins/vue-validate", ssr: false },
    { src: "~/plugins/newfangled.js", ssr: false },
    { src: "~/plugins/scrollto.js", ssr: false },
    { src: "~/plugins/search.js" },
    { src: "~/plugins/meta.js" }
  ],
  modules: [
    "@nuxtjs/axios",
    "@nuxtjs/proxy",
    [
      "@nuxtjs/google-analytics",
      {
        id: "UA-22713924-1"
      }
    ]
  ],
  axios: {
    credentials: true,
    proxy: true,
    retry: true,
    debug: true,
    proxyHeaders: false,
    proxyHeadersIgnore: false,

    //withCredentials: false,
    prefix: "/wp-json",
    // baseURL: "https://bigducknyc.com/wp-json/",
    //baseURL: "https://bigduck.test/wp-json",
    https:
      process.env.NODE_ENV == "production" || ctx.env.NODE_ENV == "production"
        ? true
        : false,

    errorHandler(errorReason, { error }) {
      error("Request Error: " + errorReason);
    }
  },

  proxy: process.env.PROXY_API_URL
    ? [
        ["/wp-json", { target: process.env.PROXY_API_URL }],
        ["/wp-admin", { target: process.env.PROXY_API_URL }]
      ]
    : [],
  /*

  ** Customize the progress-bar color
  */
  loading: "~/components/loading.vue",
  router: {
    middleware: ["nextCta", "redirects"],
    scrollBehavior: function(to, from, savedPosition) {
      return { x: 0, y: 0 };
    },
    beforeEach: function(to, from, next) {
      next();
    }
  },
  //mode: "spa",
  css: [{ src: "~/assets/scss/styles.scss", lang: "scss" }],
  /*
  ** Build configuration
  */
  render: {
    http2: {
      push: true
    }
  },
  build: {
    /*
    ** Run ESLINT on save
    */
    vendor: [
      "intersection-observer",
      "axios",
      "date-fns",
      "crypto-js",
      "flickity",
      "babel-polyfill",
      "~/components/GravityForm"
    ],
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
