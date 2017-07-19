<template>
  <div class="callout-fullwidth bg-inverse text-white" v-if="callout">
    <div class="">
  
      <div v-if="!submittedForm">
        <h2 v-html="callout.inline_callout_headline"></h2>
        <p>
          {{ callout.inline_callout_body }}
        </p>
        <a href="#" v-if="!formVisible && callout.inline_callout_form" @click.prevent="toggleForm()" class="btn btn-primary">
          Sign up
        </a>
      </div>
  
      <div v-if="formVisible && callout.inline_callout_form">
        <GravityForm :formId="callout.inline_callout_form" @submitted="hideCallout()"></GravityForm>
  
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
      formVisible: false,
      submittedForm: false
    }
  },
  props: ['callout'],
  computed: {
    ...mapState(['activeInline'])
  },
  components: {
    GravityForm
  },
  methods: {
    toggleForm() {
      this.formVisible = !this.formVisible
    },
    hideCallout() {
      this.submittedForm = true
    }
  }
}
</script>
