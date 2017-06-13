<template>

  <div>
  <header id="header">
    <div class="container-fluid">
      <nav class="navbar navbar-toggleable-md navbar-light">
        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#main-menu" aria-controls="main-menu" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <router-link class="navbar-brand" to="/">
          <Logo></Logo>
          <div class="label">Smart communications for nonprofits</div>
        </router-link>

        <div class="collapse navbar-collapse" id="main-menu">
          <ul class="navbar-nav ml-auto">
            <li class="nav-item">
              <router-link class="nav-link" to="/services"><span>Services</span></router-link>
              <div class="nav-text">What we can do for you and your project</div>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" to="/work"><span>Work</span></router-link>
              <div class="nav-text">Examples of what we've done and for whom</div>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" to="/events"><span>Events</span></router-link>
              <div class="nav-text">Join us online and in-person</div>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" to="/insights"><span>Insights</span></router-link>
              <div class="nav-text">Thoughts and resources from our team</div>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" to="/about"><span>About</span></router-link>
              <div class="nav-text">Learn more about us</div>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" to="/contact"><span>Contact</span></router-link>
              <div class="nav-text">Reach out to us</div>
            </li>
            <li class="nav-item">
              <a class="nav-link float-left" href="/search"><span>Search</span></a>
            </li>
          </ul>
        </div>
      </nav>
    </div>
  </header>
  <div class="page-wrapper">
    <div id="app">
      <div>
        <main id="main">
          <transition name="slideRight" appear>
            <nuxt/>
          </transition>
        </main>
      </div>
    </div>
  </div>

  <footer id="footer">
      <div class="container-fluid">
        <router-link to="/" class="navbar-brand">
          <Logo></Logo>
          <h4>Smart communications for nonprofits</h4>
        </router-link>
        <div class="contact-info">
          <h4><a href="mailto:hello@bigducknyc.com">hello@bigducknyc.com</a></h4>
          <h4><a href="tel:1-718-237-9551">1 718-237-9551</a></h4>
          <h4>
            <address class="">
              20 Jay Street, Suite 524<br>
              Brooklyn, NY 11201
            </address>
          </h4>
        </div>
      </div>
    </footer>
  </div>
</template>

<script>
  import Logo from '~components/Logo.vue'
  export default {
    components: [Logo]
  }
</script>

<style lang="scss">

 @import '../scss/styles.scss';


</style>
