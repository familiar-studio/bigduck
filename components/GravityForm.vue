<template>
  <form>

    <div v-for="field in fields" class="form-group">

      <label>{{field.label}}</label>
      <input v-model="field.value" class="form-control"/>

    </div>

    <button type="submit" @click.prevent="submitEntry()" class="btn btn-secondary">Subscribe</button>
  </form>
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
      fields: []
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

      var entry = {
        form_id: '5',
        date_created: '2016-07-29 21:38:23',
        is_starred: 0,
        is_read: 1,
        ip: '::1',
        source_url: 'http://localhost/wpdev/?gf_page=preview&id=1',
        currency: 'USD',
        created_by: 1,
        user_agent: 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0',
        status: 'active',
        2.3: 'Testing',
        2.6: 'Tester',
        1: 'The Crowsxxxxxxxxxxxxx'
      }
      axios.post(this.baseUrl + 'entries', entry, { method: 'POST', params: { api_key: this.publicKey, signature: signature, expires: this.expires } })
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

