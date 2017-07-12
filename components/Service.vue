<template>
  <div v-once class="block-overlap" :class="blockClass" v-if="entry.acf">
    <router-link :to="{ name: 'services-slug', params: { slug: entry.slug }}" :key="entry.id">
      <!-- <div class="row no-gutters"> -->
        <div class=" col-image">
          <div class="featured-image" v-if="entry.acf.featured_image" :style="{ 'background-image': 'url(' + entry.acf.featured_image.url+ ')' }">
          </div>
        </div>

        <div class=" col-text">
          <div class="card">
            <div class="card-block">
              <h3>
                <span v-html="entry.title.rendered" class="underlineChange"></span>
              </h3>
              <div class="card-text" v-html="entry.acf.short_description">
              </div>
            </div>
          </div>
        </div>
      <!-- </div> -->
    </router-link>
  </div>
</template>
<script>
export default {
  props: ['entry', 'index'],
  computed: {
    typeIcon() {
      this.$store.state.topics.filter((topic) => {
        return topic.name === entry.slug
      })
      return this.$store.state.topics
    },
    blockClass() {
      if (this.index % 2 === 0) {
        return 'odd-block'
      } else {
        return 'even-block'
      }
    }
  }
}
</script>
