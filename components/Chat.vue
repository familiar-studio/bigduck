<template>
  <div class="chat-group" v-if="chat">
    <div class="chat-bubble">
      {{ chat.title }}
    </div>
    <div class="chat-bubble">
      {{ chat.description }}
    </div>
    <div class="chat-bubble chat-response bg-change">
      <a :href="chat.chat_link" v-if="chat.chat_link">
        {{ chat.chat_button_text }}
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
    ...mapState(['chat'])
  },

}
</script>

<style>

</style>
