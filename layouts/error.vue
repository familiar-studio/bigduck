<template>
  <div class="container">
    <h1 v-if="error.statusCode === 404">Oh no! This page doesn't exist. </h1>
    <h1 v-else>An error occurred</h1>
  
    <img src="~assets/images/ascii.png" />
    <p>
      You can go back to where you were or
      <nuxt-link to="/">return home</nuxt-link> to find one that does.
    </p>
  
  </div>
</template>

<script>
export default {
  props: ['error'],
  layout: 'default' // you can set a custom layout for the error page
}
</script>
