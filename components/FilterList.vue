<template>
<div>
  <div class="label label-lg">{{ label }}</div>
  <div class="media-list">
  <a href="#" v-for="term in terms" :key="term.id" :class="{ active: term.id == selected }" @click.prevent="clicked(taxonomy, term.id)">
    <div class="media">
      <div v-html="term.icon"></div>
      <div class="media-body">
        <h6 v-html="term.name"></h6>
      </div>
    </div>
  </a>
</div>
</div>
</template>

<script>
  import { mapGetters } from 'vuex'
  export default {
    props: ['taxonomy', 'terms', 'selected', 'label'],
    computed: {
      ...mapGetters(['getTopicsIndexedById'])
    },
    methods: {
      clicked (taxonomy, id) {
        this.$emit('clicked', {taxonomy, id})
      }
    }
  }
</script>
