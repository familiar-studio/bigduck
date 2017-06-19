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
        imagesLoaded: true
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
.carousel.is-expanded { height: 100vh; width: 100vh; position: fixed; top: 0; left: 0;}
.carousel.is-expanded img {max-width: 100%; height: auto;}
/*.carousel .carousel-cell {width: auto; height: auto;}*/
.carousel.is-expanded .carousel-cell {
  width: 100%;
  height: 100%;
  margin: auto;
  /*padding: 0;*/

}

</style>
