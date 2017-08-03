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



      this.nextCTA(this.activeCta)

      if (this.activeCta.acf.cta_type == 'Linked Content') {
        if (this.activeCta.acf.linked_content.post_type == 'bd_insight') {
          // link to the insight
          this.$router.push('/insights/' + this.activeCta.acf.linked_content.post_name);
        } else {
          // link to the event
          this.$router.push('/events/' + this.activeCta.acf.linked_content.post_name);
        }
      } else if (this.activeCta.acf.cta_type == 'Custom Link') {
        location.href = this.activeCta.acf.custom_link;
      } else {
        // scroll down to the form
        this.$scrollTo('#footer-callout', 500, { offset: 50 })
      }

      if (typeof ga !== 'undefined') {
        //console.log('ga', ga)
        ga('send', {
          hitType: 'event',
          eventCategory: 'Smart CTA',
          eventAction: 'click',
          eventLabel: this.activeCta.title.rendered
        });
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
