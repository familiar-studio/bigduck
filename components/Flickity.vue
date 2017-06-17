<template>
    <div :class="{'open':open}" @click="toggleSize()">
        <Flickity ref="flickity" :options="options" >
          <slot ></slot>
        </Flickity>
    </div>
</template>

<script>

import Vue from 'vue'
if (process.BROWSER_BUILD) {
  var Flickity = require('flickity')
}

export default {
  created () {},
  props: ['options', 'reference'],
  data () {
    return {
      open: false,
      flickity: null
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

.carousel-cell {
  width: 100%;
  height: 500px; 
  margin-right: 10px;
  img {
    margin: 0 auto;
  }
}

.open .carousel-cell {
  height: 100vh;
}


</style>
