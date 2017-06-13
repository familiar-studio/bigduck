<template>

  <router-link :to="{ name: 'Service', params: { slug: entry.slug }}" :key="entry.id">
  <div v-if="entry.acf && entry.acf.featured_image">
  <div class="row">
  <div class="col-image col-md-6 " >
    <div class="featured-image" :style="{ 'background-image': 'url(' + entry.acf.featured_image.url+ ')' }">

    </div>

  </div>
  <div class="col-text col-md-6">

      <div class="card">
        <div class="card-header badge-group">

          <h2 v-html="entry.title.rendered"></h2>
        </div>
        <div class="card-block" v-html="entry.acf.short_description">
          {{entry.acf.short_description}}
        </div>

        </div>
      </div>
    </div>
  </div>
</div>
  </router-link>
</template>
<script>
  export default {
    props: ['entry'],
    computed: {
      typeIcon () {
        this.$store.state.topics.filter((topic) => {
          return topic.name === entry.slug
        })
        return this.$store.state.topics
      }
    }
  }
</script>
<style>
  .card-block {
    font-size: 18px;	line-height: 26px;
  }
</style>
