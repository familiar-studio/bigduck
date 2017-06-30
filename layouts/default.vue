<template>
  <div id="full-wrapper">
    <div class="page-wrapper" :class="page">
      <header id="header" :class="{ 'small-nav': haveScrolled, 'nav-open':navVisible, 'hovering':hovering }">
        <div class="container-fluid">
          <nav class="navbar navbar-toggleable-md navbar-light">
            <button @click="toggleNav()" class="navbar-toggler navbar-toggler-right collapsed" type="button" data-toggle="collapse" data-target="#main-menu" aria-controls="main-menu" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>

            <nuxt-link class="navbar-brand" to="/" @click="hideNav()">
              <Logo></Logo>
              <div class="label tagline hidden-xs-down">Smart communications for nonprofits</div>
            </nuxt-link>

            <div class="navbar-collapse" id="main-menu">
              <div class="label tagline hidden-sm-up">Smart communications for nonprofits</div>
              <ul class="navbar-nav ml-auto">
                <li class="nav-item" @mouseover="hover('services')" @mouseleave="hovering = false">
                  <nuxt-link @click="hideNav()" class="nav-link" to="/services">
                    <span>Services</span>
                  </nuxt-link>
                  <div class="nav-text" :class="{ 'show': currentText === 'services'}" v-html="menuCallouts['services']"></div>
                </li>
                <li class="nav-item" @mouseover="hover('work')" @mouseleave="hovering = false">
                  <nuxt-link @click="hideNav()" class="nav-link" to="/work">
                    <span>Work</span>
                  </nuxt-link>
                  <div class="nav-text" :class="{ 'show': currentText === 'work'}" v-html="menuCallouts['work']"></div>
                </li>
                <li class="nav-item" @mouseover="hover('events')" @mouseleave="hovering = false">
                  <nuxt-link @click="hideNav()" class="nav-link" to="/events">
                    <span>Events</span>
                  </nuxt-link>
                  <div class="nav-text" :class="{ 'show': currentText === 'events'}" v-html="menuCallouts['events']"></div>
                </li>
                <li class="nav-item" @mouseover="hover('insights')" @mouseleave="hovering = false">
                  <nuxt-link @click="hideNav()" class="nav-link" to="/insights">
                    <span>Insights</span>
                  </nuxt-link>
                  <div class="nav-text" :class="{ 'show': currentText === 'insights'}" v-html="menuCallouts['insights']"></div>
                </li>
                <li class="nav-item" @mouseover="hover('about')" @mouseleave="hovering = false">
                  <nuxt-link @click="hideNav()" class="nav-link" to="/about">
                    <span>About</span>
                  </nuxt-link>
                  <div class="nav-text" :class="{ 'show': currentText === 'about'}" v-html="menuCallouts['about']"></div>
                </li>
                <li class="nav-item" @mouseover="hover('contact')" @mouseleave="hovering = false">
                  <nuxt-link class="nav-link" to="/contact">
                    <span>Contact</span>
                  </nuxt-link>
                  <div class="nav-text" :class="{ 'show': currentText === 'contact'}" v-html="menuCallouts['contact']"></div>
                </li>
                <li class="nav-item">
                  <a class="nav-link float-left" href="#" @click.prevent="showSearch()">
                    <svg width="26px" height="26px" viewBox="0 0 26 26" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" id="search-icon">
                      <path d="M25.5611068,23.4388932 C26.146029,24.0268104 26.146029,24.9731896 25.5574713,25.5647236 C25.2731412,25.8461524 24.8928615,26 24.5,26 C24.1053281,26 23.7235341,25.8447477 23.4388932,25.5601068 L15.0643004,17.185514 C13.4604395,18.3533221 11.5289078,19 9.5,19 C4.26071525,19 0,14.7392847 0,9.5 C0,4.26071525 4.26071525,0 9.5,0 C14.7392847,0 19,4.26071525 19,9.5 C19,11.528581 18.353522,13.4600566 17.1863031,15.0640896 L25.5611068,23.4388932 Z M3,9.5 C3,13.0837153 5.91628475,16 9.5,16 C13.0837153,16 16,13.0837153 16,9.5 C16,5.91628475 13.0837153,3 9.5,3 C5.91628475,3 3,5.91628475 3,9.5 Z" fill="#1E1E1E"></path>
                    </svg>
                  </a>
                </li>
              </ul>
            </div>
          </nav>
        </div>
      </header>

      <main id="main" :class="{ 'no-top-padding': noTopPadding }">

        <nuxt/>

      </main>

      <footer id="footer">
        <div>
          <nuxt-link to="/" class="navbar-brand">
            <Logo></Logo>
            <div class="label tagline">Smart communications for nonprofits</div>
          </nuxt-link>
          <div class="contact-info">
            <ul class="list-unstyled social-media">
              <a href="https://www.facebook.com/bigduck" target="_blank">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                  <path d="M18.768 7.465h-4.268v-1.905c0-.896.594-1.105 1.012-1.105h2.988v-3.942l-4.329-.013c-3.927 0-4.671 2.938-4.671 4.82v2.145h-3v4h3v12h5v-12h3.851l.417-4z" />
                </svg>
              </a>
              <a href="https://twitter.com/@bigduck" target="_blank">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                  <path d="M23.444 4.834c-.814.363-1.5.375-2.228.016.938-.562.981-.957 1.32-2.019-.878.521-1.851.9-2.886 1.104-.827-.882-2.008-1.435-3.315-1.435-2.51 0-4.544 2.036-4.544 4.544 0 .356.04.703.117 1.036-3.776-.189-7.125-1.998-9.366-4.748-.391.671-.615 1.452-.615 2.285 0 1.577.803 2.967 2.021 3.782-.745-.024-1.445-.228-2.057-.568l-.001.057c0 2.202 1.566 4.038 3.646 4.456-.666.181-1.368.209-2.053.079.579 1.804 2.257 3.118 4.245 3.155-1.945 1.524-4.356 2.159-6.728 1.881 2.012 1.289 4.399 2.041 6.966 2.041 8.358 0 12.928-6.924 12.928-12.929l-.012-.588c.887-.64 1.953-1.237 2.562-2.149z" />
                </svg>
              </a>
              <a href="#">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                  <path d="M0.000218181818,13.0570909 C6.03342342,13.0570909 10.9442182,17.9675917 10.9442182,24 L15.3078545,24 C15.3078545,15.5582468 8.44230597,8.69345455 0.000218181818,8.69345455 L0.000218181818,13.0570909 Z"></path>
                  <path d="M0,17.9712 L0,23.9995636 L6,23.9995636 C6,20.2762909 3.71890909,17.9712 0,17.9712"></path>
                  <path d="M0,4.90909091 C10.5257159,4.90909091 19.0909091,13.4742841 19.0909091,24 L23.4545455,24 C23.4545455,11.0657159 12.9342841,0.545454545 0,0.545454545 L0,4.90909091 Z"></path>
                </svg>
              </a>
            </ul>
            <div>
              <h4>
                <a href="mailto:hello@bigducknyc.com">hello@bigducknyc.com</a>
              </h4>
              <h4>
                <a href="tel:1-718-237-9551">1 718-237-9551</a>
              </h4>
              <h4>
                <address class="">
                  20 Jay Street, Suite 524
                  <br> Brooklyn, NY 11201

                </address>
              </h4>
            </div>
          </div>
        </div>
      </footer>

      <SearchOverlay v-if="searchVisible" @hide="hideSearch()"></SearchOverlay>

    </div>
    <section id="footer-callout" v-if="chat && showFooter" class="bgChange text-white my-0 py-5">
      <div class="container">
        <div class="row">
          <div class="col-lg-8 offset-lg-2">
            <div class="footer-content">
              <h2>{{ chat.title }}</h2>
              <p>
                {{ chat.description }}
              </p>

              <GravityForm v-if="chat.formId" :formId="chat.formId" btnType="tertiary"></GravityForm>

            </div>
          </div>
        </div>
      </div>
      <nuxt-link to="/privacy" class="link-privacy">Privacy Policy</nuxt-link>
    </section>
    <script src="http://hi.bigducknyc.com/cdnr/30/acton/bn/tracker/4852"></script>
  </div>
</template>

<script>
import Logo from '~components/Logo.vue'
import GravityForm from '~components/GravityForm.vue'
import SearchOverlay from '~components/SearchOverlay.vue'


import { mapState, mapMutations } from 'vuex'

export default {
  components: {
    Logo,
    GravityForm,
    SearchOverlay

  },
  data() {
    return {
      navVisible: false,
      searchVisible: false,
      haveScrolled: false,
      totalColors: 7,
      currentColor: 1,
      query: null,
      currentText: null,
      hovering: false
    }
  },
  head() {
    return {
      htmlAttrs: {
        class: 'color-' + this.currentColor
      }
    }
  },
  computed: {
    ...mapState(['chat', 'menuCallouts']),
    page() {
      if (this.$route.name === 'index') {
        return 'homepage'
      } else if (this.$route.name) {
        return this.$route.name
      } else {
        return ''
      }
    },
    showFooter() {
      if (this.page === 'work-slug' || this.page === 'contact' || this.page === 'services-slug' || this.page === 'events-speaking') {
        return false
      }
      return true
    },
    showChat() {
      if (this.page === 'homepage' || this.page === 'work-all' || this.page === 'services' || this.page === 'insights') {
        return false
      }
      return true
    },
    noTopPadding() {
      if (this.page === 'services-slug') {
        return true
      }
      return false
    }
  },
  watch: {
    '$route': function () {
      this.navVisible = false
    }
  },
  methods: {
    toggleNav() {
      this.navVisible = !this.navVisible
    },
    showSearch() {
      this.searchVisible = true
    },
    hideSearch() {
      this.searchVisible = false
    },
    handleScroll() {
      this.haveScrolled = window.scrollY > 20
    },
    changeColor() {
      if (this.currentColor < this.totalColors) {
        this.currentColor++
      } else {
        this.currentColor = 1
      }
    },
    hover(id) {
      this.hovering = true
      this.currentText = id
    }

  },
  created() {
    if (process.BROWSER_BUILD) {
      setInterval(this.changeColor, 5000)
      window.addEventListener('scroll', this.handleScroll)

      this.$store.dispatch('fetchPageCallouts', 'chat')
      this.$store.dispatch('fetchPageCallouts', 'inline')


    }
  }
}
</script>
