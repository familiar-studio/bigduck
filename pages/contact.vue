<template>
<div class="container no-hero">
  <h1>Contact Us</h1>

  <article>
    <section>

      <div class="" v-if="gatedContent" v-html="gatedContent">

      </div>
    </section>
  </article>

</div>
</template>
<script>
  import Axios from 'axios'
  export default {
    name: 'contact',
    async asyncData ({store}) {
      let gatedContent = await Axios.get(store.getters['hostname'] + 'wp/v2/pages?slug=newfangled-testing-gated-content')
      let data = {
        gatedContent: gatedContent.data[0].content.rendered
      }
      return data
    }
  }
</script>
