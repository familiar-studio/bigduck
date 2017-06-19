<template>
    <div :class="{'open':open}" @click="toggleSize()" class="gallery-wrapper">
      <slot ></slot>
    </div>
</template>

<script>

import Vue from 'vue'
if (process.BROWSER_BUILD) {
  var Flickity = require('flickity')
}

export default {
  created () {},
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
      this.open = !this.open
      this.flickity.resize()
    }
  }
}
</script>
<style>


</style>
