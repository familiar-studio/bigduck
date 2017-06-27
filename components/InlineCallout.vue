<template>
  <div class="callout-fullwidth bg-inverse text-white" v-if="inline">
    <div class="callout-content">
  
      <h2>{{ inline.title }}</h2>
      <p>
        {{ inline.description }}
      </p>
  
      <a href="#" v-if="!formVisible" @click.prevent="toggleForm()" class="btn btn-primary">
        Sign up
      </a>
  
      <div v-if="formVisible && inline.formId">
        <GravityForm :formId="inline.formId"></GravityForm>
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
    ...mapState(['inline'])
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
