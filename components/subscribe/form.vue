<template>
  <div>

    <form @submit.prevent="onSubmit">


      <div class="form-group collapse" :id="'collapse-' + id">
        <label for="exampleInputEmail1">Email</label>
        <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" :placeholder="placeholder" v-model="email">
        <!-- <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small> -->
      </div>
    <button type="button" class="btn btn-primary" data-toggle="collapse" :data-target="'#collapse-' + id" @click="collapse">{{ button }}</button>
  </form>
</div>
</template>
<script>
  export default {
    name: 'subscribe-form',
    props: ['placeholder', 'button', 'label', 'id'],
    data () {
      return {
        email: ''
      }
    },
    methods: {
      onSubmit () {
        // this.$store.dispatch('subscribe', this.email)
      },
      collapse () {
      }
    }
  }
</script>
