<template>
  <div class="container error-page">
    <h1 v-if="error.statusCode === 404">Oh no! This page doesn&rsquo;t&nbsp;exist. </h1>
    <h1 v-else>An error occurred</h1>
    <div class="ascii-duck">
      <h1>
        <span class="bill-wrapper">
          <span>></span>
        </span>(-)__,</h1>
      <h1>(___/</h1>
    </div>
  
    <p class="block-text">
      You can
      <a href="#" @click="goback()">go back</a> to where you were, or
      <nuxt-link to="/">return home</nuxt-link> to find one that does.
    </p>
  
  </div>
</template>

<script>
export default {
  props: ['error'],
  layout: 'default', // you can set a custom layout for the error page
  methods: {
    goback() {
      this.$router.go(-1)
    }
  },
  created() {
    //if not an insights url redirect to insight url with same slug
  }
}
</script>
