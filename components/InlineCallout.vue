<template>
  <div class="callout-fullwidth bg-inverse text-white" v-if="activeInline && activeInline.acf">
    <div class="container">
  
      <h2 v-html="activeInline.acf.intro"></h2>
      <p>
        {{ activeInline.acf.body }}
      </p>
  
      <a href="#" v-if="!formVisible && activeInline.acf.cta_form" @click.prevent="toggleForm()" class="btn btn-primary">
        Sign up
      </a>
  
      <div v-if="formVisible && activeInline.acf.cta_form">
        <GravityForm :formId="activeInline.acf.cta_form"></GravityForm>
      </div>
  
    </div>
  </div>
</template>
<script>

import { mapState } from 'vuex'
import GravityForm from '~components/GravityForm.vue'

export default {
  data() {
    return {
      formVisible: false
    }
  },
  computed: {
    ...mapState(['activeInline'])
  },
  components: {
    GravityForm
  },
  methods: {
    toggleForm() {
      this.formVisible = !this.formVisible
    }
  }
}
</script>
