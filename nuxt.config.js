module.exports = {
  /*
  ** Headers of the page
  */
  head: {
    title: ' ',
    titleTemplate: '%s | Big Duck',
    script: [
      { src: 'http://hi.bigducknyc.com/cdnr/30/acton/bn/tracker/4852' }
    ],
    meta: [
      { charset: 'utf-8' },
      { name: 'viewport', content: 'width=device-width, initial-scale=1' },
      { hid: 'description', name: 'description', content: 'Nuxt.js project' }

    ],
    link: [
      { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' }
    ]
  },
  cache: true,
  transition: {
    name: 'page',
    mode: 'out-in',
    appear: true
  },
  plugins: [
    { src: '~plugins/vue-validate', ssr: false },
    { src: '~plugins/newfangled.js', ssr: false },
    { src: '~plugins/ga.js', ssr: false },
    { src: '~plugins/scrollspy.js', ssr: false }
  ],
  /*
  ** Customize the progress-bar color
  */
  loading: { color: '#3B8070' },
  router: {},
  css: [
     { src: '~assets/scss/styles.scss', lang: 'scss' }
  ],
  /*
  ** Build configuration
  */
  build: {
    /*
    ** Run ESLINT on save
    */
    extend (config, ctx) {
      if (ctx.isClient) {
        config.module.rules.push({
          enforce: 'pre',
          test: /\.(js|vue)$/,
          loader: 'eslint-loader',
          exclude: /(node_modules)/
        })
      }
    }
  }
}
