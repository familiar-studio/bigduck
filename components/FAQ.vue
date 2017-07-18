<template lang="html">
<div class="mb-5">


<div v-if="questions">
  <div v-for="(question, index) in questions" class="collapse-block">
    <div :class="{'active': index === openQuestion}">
      <h3>
        <a href="#" class="underline-change" @click.prevent="toggleQuestion(index)">{{question.question}}</a>
      </h3>
    </div>
    <div class="collapse-content" :class="{'show': index === openQuestion}">
      <div class="job-description" v-html="question.answer">
      </div>
    </div>
  </div>
</div>
</div>
</template>

<script>
export default {
  data () {
    return {
      openQuestion: null
    }
  },
  props: ['questions'],
  methods: {
    toggleQuestion(questionId) {
      this.openQuestion = this.openQuestion === questionId ? null : questionId
    }
  }
}
</script>

<style lang="css">
</style>
