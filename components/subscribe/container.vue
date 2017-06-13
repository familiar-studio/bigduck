<template>
  <div class="callout-fullwidth bg-inverse text-white">
    <div class="callout-content">
      <h2>{{ entry.title.rendered }}</h2>
      <div v-html="entry.content.rendered"></div>

      <SubscribeForm placeholder="" button="subscribe" :id="entry.id"></SubscribeForm>
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
