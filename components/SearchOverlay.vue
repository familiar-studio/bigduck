<template>
  <transition name="down" appear>
    <section id="search-overlay">
      <div class="search-bar">
        <a href="#" class="close" @click.prevent="closeSearch()">&times;</a>
        <div class="container">
          <form @submit.prevent="search()" class="form-inline">
            <input type="text" placeholder="Search" v-model="query" v-focus class="form-control form-control-lg" />
            <button type="submit" class="btn btn-secondary">
              <svg width="26px" height="26px" viewBox="0 0 26 26" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" id="search-icon">
                <path d="M25.5611068,23.4388932 C26.146029,24.0268104 26.146029,24.9731896 25.5574713,25.5647236 C25.2731412,25.8461524 24.8928615,26 24.5,26 C24.1053281,26 23.7235341,25.8447477 23.4388932,25.5601068 L15.0643004,17.185514 C13.4604395,18.3533221 11.5289078,19 9.5,19 C4.26071525,19 0,14.7392847 0,9.5 C0,4.26071525 4.26071525,0 9.5,0 C14.7392847,0 19,4.26071525 19,9.5 C19,11.528581 18.353522,13.4600566 17.1863031,15.0640896 L25.5611068,23.4388932 Z M3,9.5 C3,13.0837153 5.91628475,16 9.5,16 C13.0837153,16 16,13.0837153 16,9.5 C16,5.91628475 13.0837153,3 9.5,3 C5.91628475,3 3,5.91628475 3,9.5 Z"></path>
              </svg>
            </button>
          </form>
        </div>
      </div>
    </section>
  </transition>
</template>

<script>
export default {
  props: ['searchVisible'],
  data() {
    return {
      query: null
    }
  },
  directives: {
    focus: {
      inserted(el) {
        el.focus()
      }
    }
  },
  methods: {
    closeSearch() {
      this.$emit('hide')
    },
    search() {
      this.$router.push({ name: 'search', query: { query: this.query } })
      this.$emit('hide')
    }
  }
}
</script>
