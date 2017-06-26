<template>
  <div class="chat-group" v-if="callout && callout.chat">
    <div class="chat-bubble">
      {{ callout.chat.title }}
    </div>
    <div class="chat-bubble">
      {{ callout.chat.description }}
    </div>
    <div class="chat-bubble chat-response bgChange">
      <a :href="callout.chat_link" v-if="callout.chat_link">
        {{ callout.chat.chat_button_text }}
      </a>
      <a href="#" v-else v-scroll-to="{ el:'#footer-callout', offset:50}">
        Learn More
      </a>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { mapState } from 'vuex'

export default {
  data() {
    return {

    }
  },
  computed: {
    ...mapState(['callout'])
  },

}
</script>

<style>

</style>
