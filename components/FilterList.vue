<template>
<div>
  <div class="label label-lg">{{ label }}</div>
  <div class="media-list">
  <a href="#" v-for="term in terms" :key="term.id" :class="{ activeFilter: term.id == selected }" @click.prevent="clicked(taxonomy, term.id)">
    <div class="media">
      <img :src="term.acf.icon">
      <div class="media-body">
        <h6 v-html="term.name"></h6>
      </div>
    </div>
  </a>
</div>
</div>
</template>

<script>
  export default {
    props: ['taxonomy', 'terms', 'selected', 'label'],
    methods: {
      clicked (taxonomy, id) {
        this.$emit('clicked', {taxonomy, id})
      }
    }
  }
</script>
