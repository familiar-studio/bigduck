<template>
  <transition-group name="staggered-fade" @before-enter="beforeEnter" @enter="enter" appear>
    <slot></slot>
  </transition-group>
</template>

<script>
export default {
  props: ['previous', 'current'],
  data() {
    return {
      delay: 300
    }
  },
  methods: {
    beforeEnter(el) {
      el.style.opacity = 0
    },
    enter(el, done) {
      const delay = (el.dataset.index - this.previous) * this.delay
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
