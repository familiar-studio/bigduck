<template>
<div class="container  no-hero">

  <article class="main">
    <section  class="my-0 py-5 ">

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
    },
    async created () {
      let content = await Axios.get(this.$store.getters['hostname'] + 'wp/v2/pages?slug=newfangled-testing-gated-content')
      console.log('content', content)
    }
  }
</script>
