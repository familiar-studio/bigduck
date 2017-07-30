<template>
  <div class="container">
    <article class="main">
      <div class="" v-html="data.content.rendered"></div>
    </article>
  
  </div>
</template>

<script>
export default {

  async asyncData({ store }) {
    let response = await Axios.get(store.getters['hostname'] + 'wp/v2/pages?slug=bye')
    var data = response.data[0]
    return {
      data: data
    }
  },
  // if url doesnt exist try and find it in insights
  fetch({ redirect, params }) {
    //return redirect('/insights/' + params.page)
  }
}
</script>

