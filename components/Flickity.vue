<template>
  <div class="carousel" :class="{'is-expanded':open, 'first-slide':isFirstSlide}">
      Slide {{index}} of {{length}}
      <div class="carousel-cell" v-for="image in images"> <img :src="image.sizes.large" :alt="image.title" class="img-fluid" @click="toggleSize()">
        <figcaption class="figure-caption">{{image.caption}}</figcaption>
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
        percentPosition: false,
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
      this.flickity.resize()
    }
  },
  computed: {
    isFirstSlide () {
      return this.index === 1
    },
    index () {
      if (this.flickity) {
        return this.flickity.selectedIndex + 1
      }
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
.carousel.is-expanded { height: 100vh; }
.carousel.is-expanded img {max-width: 100%; height: auto;}
.carousel.is-expanded .carousel-cell {
  background-size: cover;
  background-position: center;
  width: 100%;
  height: 100%;
  margin: 0;
  padding: 0;

}

</style>
