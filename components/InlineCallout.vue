<template>
  <div class="callout-fullwidth bg-inverse text-white" v-if="callout">
    <div class="callout-content">
  
      <h2>{{ callout.inline_callout_headline }}</h2>
      <p>
        {{ callout.inline_callout_body }}
      </p>
  
      <a href="#" v-if="!formVisible" @click.prevent="toggleForm()" class="btn btn-primary">
        {{ callout.inline_callout_button_text }}
      </a>
  
      <div v-if="formVisible && callout.inline_callout_form">
        <GravityForm :formId="callout.inline_callout_form"></GravityForm>
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
    ...mapState(['callout'])
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
