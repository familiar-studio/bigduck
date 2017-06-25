<template lang="html">
  <div class="pager">
    <div v-if="totalPages > 1">
      <a href="#" @click.prevent="changePage(previousPage)" v-if="page > 1">Previous</a>
      <a href="#" @click.prevent="changePage(nextPage)" v-if="page < totalPages">Next</a>
      Page {{page}} of {{totalPages}}
    </div>
  </div>
</template>

<script>
export default {
  props: ['totalPages', 'path'],
  computed: {
    page() {
      return this.$route.query.page ? parseInt(this.$route.query.page) : 1
    },
    nextPage() {
      let page = this.page + 1
      return page
    },
    previousPage() {
      return this.page - 1
    }
  },
  methods: {
    changePage(page) {
      let data = Object.assign({}, this.$route.query)
      data['page'] = page
      this.$router.push({ path: this.path, query: data })
    }
  }
}
</script>

<style lang="css">

</style>
