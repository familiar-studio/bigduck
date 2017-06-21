<template>
  <div class="container">
    <h1 v-if="error.statusCode === 404">404 - Missing Duck!</h1>
    <h1 v-else>An error occurred</h1>
    <nuxt-link to="/">Back to Home page</nuxt-link>
  </div>
</template>

<script>
export default {
  props: ['error'],
  layout: 'default' // you can set a custom layout for the error page
}
</script>
