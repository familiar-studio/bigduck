<template>
  <div class="chat-group" v-if="callout" :key="callout">
    <div class="chat-bubble">
      {{ callout.chat_headline }}
    </div>
    <div class="chat-bubble">
      {{ callout.chat_body }}
    </div>
    <div class="chat-bubble chat-response bgChange">
      <a href="#" v-scroll-to="{ el:'#footer-callout', offset:50}">
        {{ callout.chat_button_text }}
      </a>
    </div>
  </div>
</template>

<script>

import { mapState } from 'vuex'

export default {
  computed: {
    ...mapState(['callout'])
  }
}
</script>

<style>

</style>
