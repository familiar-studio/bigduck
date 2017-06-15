<template>
  <div>
    <ul class="nav nav-pills" v-if="isListView">
      <li class="nav-item">
        <router-link :to="{ name: 'work'}" class="nav-link">Featured Work</router-link>
      </li>
      <li class="nav-item">
        <router-link :to="{ name: 'work-all'}" class="nav-link">All Projects</router-link>
      </li>
    </ul>
    <nuxt-child/>
  </div>
</template>
<script>
export default {
  computed: {
    isListView () {
      return this.$route.name !== 'work-id'
    }
  }
}
</script>
