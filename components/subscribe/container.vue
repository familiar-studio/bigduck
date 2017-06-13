<template>
  <div class="callout-fullwidth bg-inverse text-white">
    <div class="callout-content">
      <h2>{{ entry.title.rendered }}</h2>
      <p v-html="entry.content.rendered"></p>
      
      <SubscribeForm placeholder="" button="subscribe" :id="entry.id"></SubscribeForm>
    </div>
  </div>
</template>
<script>
  import SubscribeForm from './form.vue'
  export default {
    props: ['entry'],
    components: {
      SubscribeForm
    }
  }
</script>
