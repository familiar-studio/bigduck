<template>
  <div>
    <div class="mb-3">
      <button @click="prevSlide" class="flickity-prev-next-button previous" type="button" :disabled="isFirstSlide" aria-label="previous"><svg viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 70,90 L 30,50  L 70,10 L 60,0 Z" class="arrow"></path></svg></button>
      <button @click="nextSlide" class="flickity-prev-next-button next" type="button" :disabled="isLastSlide" aria-label="next"><svg viewBox="0 0 100 100"><path d="M 10,50 L 60,100 L 70,90 L 30,50  L 70,10 L 60,0 Z" class="arrow" transform="translate(100, 100) rotate(180) "></path></svg></button>
    </div>
    <div class="flickity">
      <div class="carousel" :class="{'is-expanded':open, 'first-slide':isFirstSlide}">
        <div class="carousel-cell" v-for="image,index in images">
          <img :src="image.sizes.large" :alt="image.title">
          <figcaption class="figure-caption">{{image.caption}}</figcaption>
          <div class="slide-counter label">{{index + 1}}/{{images.length}}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>

import Vue from 'vue'
if (process.BROWSER_BUILD) {
  var Flickity = require('flickity')
  require('flickity-imagesloaded')
}

export default {
  props: ['images'],
  data() {
    return {
      open: false,
      flickity: null,
      slide: 1,
      options: {
        cellAlign: 'left',
        prevNextButtons: false,
        pageDots: false,
        setGallerySize: false,

        // percentPosition: false,
        imagesLoaded: true,
        selectedAttraction: 0.01,
        friction: 0.15
      }
    }
  },
  mounted() {
    this.flickity = new Flickity(this.$el.children[1].children[0], this.options)
    this.flickity.on('staticClick', () => {
      this.toggleSize()
    })
  },
  methods: {
    toggleSize() {
      console.log('toggle size')
      this.open = !this.open
      setTimeout(() => {
        this.flickity.resize()
        this.flickity.reposition()
      }, 50
      )
    },
    prevSlide () {
      this.flickity.previous()
      this.slide--
    },
    nextSlide () {
      this.flickity.next()
      this.slide++
    }
  },
  computed: {
    isFirstSlide() {
      return this.slide === 1
    },
    isLastSlide() {
      return this.slide === this.length
    },
    length() {
      if (this.flickity) {
        return this.flickity.slides.length
      }
    }
  }
}
</script>
<style>

</style>
