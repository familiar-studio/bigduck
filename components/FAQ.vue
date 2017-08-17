<template lang="html">
<div class="mb-5">


<div v-if="questions">
  <div v-for="(question, index) in questions" class="collapse-block">
    <div :class="{'active': openQuestions[index]}">
      <h3>
        <a href="#" class="underline-change" @click.prevent="toggleQuestion(index)">{{question.question}}</a>
      </h3>
    </div>
    <div class="collapse-content" :class="{'show': openQuestions[index]}">
      <div class="job-description" v-html="question.answer">
      </div>
    </div>
  </div>
</div>
</div>
</template>

<script>
import Vue from 'vue'
export default {
  data () {
    return {
      openQuestions: {}
    }
  },
  props: ['questions'],
  methods: {
    toggleQuestion(questionId) {
      this.$set(this.openQuestions, questionId, !this.openQuestions[questionId])
    }
  }
}
</script>

<style lang="css">
</style>
