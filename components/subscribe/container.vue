<template>
  <div class="callout-fullwidth bg-inverse text-white">
    <div class="callout-content">
      <slot>
        <h2>{{ entry.title.rendered }}</h2>
        <div v-html="entry.content.rendered"></div>

        <SubscribeForm placeholder="" button="subscribe" :id="entry.id"></SubscribeForm>
      </slot>
    </div>
  </div>
</template>
<script>
  import SubscribeForm from '~components/subscribe/form.vue'
  export default {
    props: ['entry'],
    components: {
      SubscribeForm
    }
  }
</script>
