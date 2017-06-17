<template>
  <section id="search-overlay"  >
    <a href="#" class="close" @click.prevent="closeSearch()" >&times;</a>
    <h1 class="display-1">Search</h1>

    <form @submit.prevent="search()">
      <input type="text" v-model="query" v-focus/>
      <button type="submit">Search</button>
    </form>
  </section>
</template>

<script>
export default {
  props: ['searchVisible'],
  data () {
    return {
      query: null
    }
  },
  directives: {
    focus: {
      inserted (el) {
        el.focus()
      }
    }
  },
  methods: {
    closeSearch () {
      this.$emit('hide')
    },
    search () {
      this.searchVisible = false
      this.$router.push({ name: 'search', query: { query: this.query } })
      this.$emit('hide')
    }
  }
}
</script>

