<template>
  <transition name="down" appear>
    <section id="search-overlay"  >
    <a href="#" class="close" @click.prevent="closeSearch()" >&times;</a>

      <div class="container">
        <div class="row">
          <div class="col-md-8 offset-md-2">
            <h1 class="display-1">Search</h1>

            <form @submit.prevent="search()">
              <input type="text" v-model="query" v-focus class="form-control"/>
              <button type="submit" class="btn btn-primary">Search</button>
            </form>
          </div>
        </div>
      </div>
    </section>
  </transition>
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
      this.$router.push({ name: 'search', query: { query: this.query } })
      this.$emit('hide')
    }
  }
}
</script>

