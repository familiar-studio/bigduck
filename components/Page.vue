<template>
  <div>
    <div v-if="image" class="img-hero" :style=" { backgroundImage: 'url(' + image  + ')' }">
      <figcaption class="figure-caption">{{image.caption}}</figcaption>
    </div>

    <div class="container" id="content">
      <article class="main" :class="{ 'overlap': image }">
        <h1>{{title}}</h1>
        <div v-if="content" v-html="content">
        </div>
      </article>
      <div class="form-light mb-5" v-if="formId">
        <GravityForm v-if="formId" :formId="formId" :showAll="true"></GravityForm>
      </div>
    </div>

  </div>
</template>
<script>
import GravityForm from '~/components/GravityForm'

export default {
  props: ['title', 'formId', 'content', 'image'],
  components: {
    GravityForm
  }
}
</script>
