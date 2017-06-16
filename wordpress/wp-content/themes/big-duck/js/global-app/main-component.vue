<template>
  <div>
    {{ this.message }}
  </div>
</template>

<script>

export default {
  // computed: {
  //   message () {
  //     return this.$store.state.msg
  //   }
  // },
  data() {
    return {
      message: "hello from the subcomponent"
    }
  }
}

</script>
