<template>
  <div class="callout-fullwidth bg-inverse text-white" v-if="callout && showCallout">
    <div class="">
  
      <div v-if="!submittedForm">
        <h2 v-html="callout.inline_callout_headline"></h2>
        <p>
          {{ callout.inline_callout_body }}
        </p>
        <a href="#" v-if="!formVisible && callout.inline_callout_form" @click.prevent="toggleForm()" class="btn btn-primary">
          {{ callout.inline_callout_button_text }}
        </a>
      </div>
  
      <div v-if="formVisible && callout.inline_callout_form">
        <transition name="fade">
          <GravityForm :formId="callout.inline_callout_form" @submitted="hideCallout()"></GravityForm>
        </transition>
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
      submittedForm: false,
      showCallout: true
    }
  },
  props: ['callout'],
  computed: {
    ...mapState(['activeInline'])
  },
  components: {
    GravityForm
  },
  watch: {
    callout() {
      if (localStorage) {
        if (localStorage["callout-" + this.callout.post_name]) {
          this.showCallout = false;
        }
      }
    }
  },
  methods: {
    toggleForm() {
      this.formVisible = !this.formVisible
    },
    hideCallout() {
      if (localStorage) {
        localStorage["callout-" + this.callout.post_name] = "true";
      }
      this.submittedForm = true

    }
  }
}
</script>
