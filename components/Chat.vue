<template>
  <div class="chat-group" v-if="activeCta && activeCta.acf">
    <div class="chat-bubble">
      {{ activeCta.acf.headline }}
    </div>
    <div class="chat-bubble">
      {{ activeCta.acf.body }}
    </div>
    <div class="chat-bubble chat-response bg-change">
      <a href="#" @click.prevent="visitLink(activeCta)">
        {{ activeCta.acf.button_text }}
      </a>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { mapGetters, mapMutations } from 'vuex'

export default {
  data() {
    return {

    }
  },
  methods: {
    ...mapMutations(['nextCTA']),
    visitLink() {

      if (this.activeCta.acf.cta_type == 'Linked Content') {
        if (this.activeCta.acf.linked_content.post_type == 'bd_insight') {
          // link to the insight
          this.$router.push('/insights/' + this.activeCta.acf.linked_content.post_name);
        } else {
          // link to the event
          this.$router.push('/events/' + this.activeCta.acf.linked_content.post_name);
        }

        setTimeout(() => {
          this.nextCTA()
        }, 500);
      } else if (this.activeCta.acf.cta_type == 'Custom Link') {
        location.href = this.activeCta.acf.custom_link;
        // go to the external link
        setTimeout(() => {
          this.nextCTA()
        }, 500);

      } else {
        // scroll down to the form

        this.$scrollTo('#footer-callout', 500, { offset: 50 })
      }





    }
  },
  computed: {
    ...mapGetters(['activeCta'])
  },

}
</script>

<style>

</style>
