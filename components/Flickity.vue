<template>
  <div class="carousel" :class="{'is-expanded':open, 'first-slide':isFirstSlide}">
    <div class="carousel-cell" v-for="image,index in images">
      <img :src="image.sizes.large" :alt="image.title" class="img-fluid" @click="toggleSize()">
      <figcaption class="figure-caption">{{image.caption}}</figcaption>
      <div class="slide-counter label">{{index + 1}}/{{images.length}}</div>
    </div>
  </div>
</template>

<script>

import Vue from 'vue'
if (process.BROWSER_BUILD) {
  var Flickity = require('flickity')
}

export default {
  props: ['images'],
  data () {
    return {
      open: false,
      flickity: null,
      options: {
        cellAlign: 'left',
        prevNextButtons: true,
        pageDots: false,
        setGallerySize: false,
        // percentPosition: false,
        imagesLoaded: true,
        selectedAttraction: 0.01,
        friction: 0.15
      }
    }
  },
  mounted () {
    this.flickity = new Flickity(this.$el, this.options)
  },
  methods: {
    toggleSize () {
      console.log('toggle size')
      this.open = !this.open
      setTimeout(() => {
        this.flickity.resize()
        // this.flickity.reposition()
      }, 50
      )
    }
  },
  computed: {
    isFirstSlide () {
      return this.index === 1
    },

    length () {
      if (this.flickity) {
        return this.flickity.slides.length
      }
    }
  }
}
</script>
<style>

</style>
