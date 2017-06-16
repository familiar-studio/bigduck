<template>
    <div>
        <Flickity ref="reference" :options="options">
          <slot></slot>
        </Flickity>
    </div>
</template>

<script>
// import Flickity from 'vue-flickity'
// import Vue from 'vue'

export default {
  // created () {
  //   if (process.BROWSER_BUILD) {
  //     console.log('browser build')
  //     const Flickity = require('vue-flickity')
  //     Vue.use('vue-flickity')
  //   }
  // },
  // components: {Flickity},
  props: ['options', 'reference']
}
</script>
