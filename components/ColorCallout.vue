<template>
  <div class="callout-fullwidth text-white color">
    <div class="container">
      <slot></slot>
    </div>
  </div>
</template>
<script>
import SubscribeForm from '~/components/subscribe/form.vue'
export default {
  props: ['entry'],
  components: {
    SubscribeForm
  }
}
</script>
