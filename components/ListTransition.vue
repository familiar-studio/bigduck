<template>
  <transition-group
    name="staggered-fade"
    @before-enter="beforeEnter"
    @enter="enter"
    appear>
    <slot></slot>
  </transition-group>
</template>

<script>
  export default {
    props: ['previous', 'current'],
    methods: {
      beforeEnter (el) {
        el.style.opacity = 0
      },
      enter (el, done) {
        const delay = (el.dataset.index - this.previous) * 150
        setTimeout(function () {
          Velocity(
            el,
            { opacity: 1 },
            { complete: done }
          )
        }, delay)
      }
    }
  }
</script>
