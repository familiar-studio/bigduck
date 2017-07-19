<template>
  <div class="chat-group" v-if="activeChat && activeChat.acf">
    <div class="chat-bubble">
      {{ activeChat.acf.intro }}
    </div>
    <div class="chat-bubble">
      {{ activeChat.acf.body }}
    </div>
    <div class="chat-bubble chat-response bg-change">
      <a :href="activeChat.acf.link" v-if="activeChat.acf.link">
        {{ activeChat.acf.button_text }}
      </a>
      <a href="#" v-else v-scroll-to="{ el:'#footer-callout', offset:50}">
        {{ activeChat.acf.button_text }}
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
    ...mapState(['activeChat'])
  },

}
</script>

<style>

</style>
