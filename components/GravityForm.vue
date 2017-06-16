<template>
  <div>
    <form v-if="!submitted">
      <h2>Want to stay in the loop?</h2>
      <p>Subscribe to our newsletter and get the latest nonprofit communications tips and tools delivered monthly to your inbox.</p>

      <div v-for="field in fields" class="form-group">

        <label>{{field.label}}</label>
        <input v-model="formData[field.id]" class="form-control"/>

      </div>

      <button type="submit" @click.prevent="submitEntry()" class="btn btn-secondary">Subscribe</button>
    </form>
    <div v-else>
      <h2>Thanks</h2>
      <p>You are the greatest!</p>
    </div>
  </div>
</template>

<script>
import CryptoJS from 'crypto-js'
import axios from 'axios'

export default {
  data () {
    return {
      publicKey: '30d2b543ba',
      privateKey: '6cb1fab7a60e11a',
      baseUrl: 'http://bigduck-wordpress.familiar.studio/gravityformsapi/',
      fields: [],
      formData: {},
      submitted: false
    }
  },
  props: ['formId'],
  computed: {
    expires () {
      var d = new Date()
      var expiration = 3600
      var unixtime = parseInt(d.getTime() / 1000)
      return unixtime + expiration
    }
  },
  methods: {
    CalculateSig (route, method) {
      var stringToSign = this.publicKey + ':' + method + ':' + route + ':' + this.expires
      var hash = CryptoJS.HmacSHA1(stringToSign, this.privateKey)
      var base64 = hash.toString(CryptoJS.enc.Base64)
      return encodeURIComponent(base64)
    },
    submitEntry () {
      var signature = this.CalculateSig('entries', 'POST')
      this.formData['form_id'] = this.formId

      axios.post(this.baseUrl + 'entries', [this.formData], { params: { api_key: this.publicKey, signature: signature, expires: this.expires } })
      this.submitted = true
    }
  },
  created () {
    var signature = this.CalculateSig('forms/' + this.formId, 'GET')
    console.log(signature)

    axios.get(this.baseUrl + 'forms/' + this.formId + '/', { params: { api_key: this.publicKey, signature: signature, expires: this.expires } }).then(
      (response) => {
        if (response.status === 200) {
          this.fields = response.data.response.fields
        }
      }

    )
  }
}
</script>

