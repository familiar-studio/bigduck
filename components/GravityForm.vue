<template>
  <div>
        <transition name="fade" appear mode="out-in">
      <div class="error" key="error" v-if="error">
        {{ error }}
      </div>
      <div v-else-if="loading" key="loading" class="loading">
        <div class="loader loader--style3" title="2">
          <svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
             width="40px" height="40px" viewBox="0 0 50 50" style="enable-background:new 0 0 50 50;" xml:space="preserve">
          <path fill="#fff" d="M43.935,25.145c0-10.318-8.364-18.683-18.683-18.683c-10.318,0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615c8.072,0,14.615,6.543,14.615,14.615H43.935z">
            <animateTransform attributeType="xml"
              attributeName="transform"
              type="rotate"
              from="0 25 25"
              to="360 25 25"
              dur="0.6s"
              repeatCount="indefinite"/>
            </path>
          </svg>
        </div>
      </div>

      <form v-else-if="!submitted && visibleFields" key="form">

        <div v-for="field in visibleFields" class="form-group" :class="{'has-danger':errors.has(field.id.toString())}">

          <label :for="field.id" v-if="field.type != 'hidden'">{{field.label}}</label>

          <template v-if="field.type == 'select'">
            <select v-model="formData['input_'+field.id]" class="custom-select form-control">
              <option v-for="choice in field.choices" :value="choice.value">
                {{ choice.text }}
              </option>
            </select>
          </template>

          <template v-else-if="field.type == 'checkbox'">
            <div class="custom-controls-stacked">
              <label class="custom-control custom-checkbox" v-for="choice in field.choices">
                <input class="custom-control-input" type="checkbox" :name="field.id" v-model="formData['input_'+field.id]" :value="choice.value">
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description">{{ choice.text }}</span>
              </label>
            </div>
          </template>
          <template v-else-if="field.type == 'radio'">
            <div class="custom-controls-stacked">
              <label class="custom-control custom-radio" v-for="choice in field.choices">
                <input class="custom-control-input" type="radio" :name="field.id" v-model="formData['input_'+field.id]" :value="choice.value">
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description">{{ choice.text }}</span>
              </label>
            </div>
          </template>

          <template v-else-if="field.type == 'email'">
            <input v-model="formData['input_'+field.id]" type="email" :name="field.id" class="form-control" v-validate="{ rules: { required: field.isRequired, email: true } }" />
          </template>

          <template v-else-if="field.type == 'number'">
            <input v-model="formData['input_'+field.id]" type="number" :name="field.id" class="form-control" v-validate="{ rules: { required: field.isRequired, numeric: true } }" />
          </template>

          <template v-else-if="field.type == 'hidden'">
            <input v-model="formData['input_'+field.id]" :name="field.id" type="hidden" />
          </template>

          <template v-else-if="field.type == 'textarea'">
            <textarea v-model="formData['input_'+field.id]" class="form-control" :name="field.id" v-validate="{ rules: { required: field.isRequired } }" />
          </template>

          <template v-else>
            <input v-model="formData['input_'+field.id]" type="text" class="form-control" :name="field.id" v-validate="{ rules: { required: field.isRequired } }" />
          </template>

          <div class="form-control-feedback" v-show="errors.has(field.id.toString())">{{ errors.first(field.id.toString()) }}</div>

        </div>

        <div v-for="field in hiddenFields">
          <input v-model="formData['input_'+field.id]" :name="field.id" type="hidden" />
        </div>

        <button type="submit" @click.prevent="submitEntry()" class="btn" :class="'btn-' + btnType">Submit</button>
      </form>
      <div v-else :key="confirmation">
        <h1>{{confirmation}}</h1>

      </div>
      </transition>
  </div>
</template>

<script>
import axios from 'axios'
import CryptoJS from 'crypto-js'
import { mapGetters } from 'vuex'

if (process.BROWSER_BUILD) {
  var jscookie = require("js-cookie")
}

export default {
  data() {
    return {
      publicKey: '30d2b543ba',
      privateKey: '6cb1fab7a60e11a',
      baseUrl: 'http://bigduck-wordpress.familiar.studio/gravityformsapi/',
      allFields: [],
      formData: {},
      totalProfilingFields: 2,
      submitted: false,
      confirmation: 'Thanks, your the greatest!',
      hiddenFields: [],
      loading: false,
      error: null
    }
  },
  props: {
    formId: {
      type: Number,
      required: true
    },
    showAll: {
      type: Boolean,
      default: false
    },
    gatedContent: {
      type: Number,
      default: null
    },
    btnType: {
      type: String,
      default: 'primary'
    },
    cookiePrefix: {
      type: String
    },
    actonId: {
      type: String
    },
    id: {
      type: Number
    },
    title: {
      type: String
    }
  },
  computed: {
    ...mapGetters(['hostname']),
    expires() {
      var d = new Date()
      var expiration = 3600
      var unixtime = parseInt(d.getTime() / 1000)
      return unixtime + expiration
    },
    visibleFields() {
      let fieldCount = 0
      if (this.allFields) {

        return this.allFields.filter((field, index) => {
          // if checkboxes and not already has data initalize as array to make multi-select work properly
          if (field.type === 'checkbox' && (!this.formData[field.id] || !Array.isArray(this.formData[field.id]))) {
            this.formData['input_' + field.id] = []
          }
          // always include the first three
          if (index < 3) {
            return field
          } else {
            if ((!this.formData[field.id] && fieldCount < this.totalProfilingFields) || this.showAll) {
              fieldCount++
              return field
            }
          }

          this.hiddenFields.push(field)
        })
      }
    }
  },
  methods: {
    CalculateSig(route, method) {
      var stringToSign = this.publicKey + ':' + method + ':' + route + ':' + this.expires
      var hash = CryptoJS.HmacSHA1(stringToSign, this.privateKey)
      var base64 = hash.toString(CryptoJS.enc.Base64)
      return encodeURIComponent(base64)
    },
    async submitEntry() {
      this.loading = true
      this.error = null
      var signature = this.CalculateSig('entries', 'POST')
      localStorage.formData = JSON.stringify(this.formData)
      //this.formData['form_id'] = this.formId
      // this.formData['title'] = this.


      var endpoint = this.baseUrl + 'forms/' + this.formId + '/submissions';
      console.log('endpoint', endpoint)


      // fill in prefilled data!
      if (this.actonId) {
        this.formData.input_19 = this.title
        this.formData.input_20 = this.id
        this.formData.input_22 = this.actonId
        this.formData.input_23 = this.actonId
      }

      if (this.gatedContent) {

        this.formData.input_19 = this.title
        this.formData.input_20 = this.gatedContent

      }


      try {

        var response = await axios.post(this.baseUrl + 'forms/' + this.formId + '/submissions',
          { "input_values": this.formData },
          { params: { api_key: this.publicKey, signature: signature, expires: this.expires } })
        if (this.gatedContent || this.actonId) {

          var cookieId = this.gatedContent ? this.gatedContent : this.actionId
          if (process.BROWSER_BUILD) {
            jscookie.set(this.cookiePrefix + cookieId, "true", {
              expires: 7
            });
          }
        }

        this.$emit('submitted')
        this.submitted = true
        this.loading = false
      } catch(e) {
        this.error = "An error occurred. Please try again later."
        this.loading = false
      }



  }
},
  created() {
    var signature = this.CalculateSig('forms/' + this.formId, 'GET')

    if (process.BROWSER_BUILD && localStorage.formData) {
      this.formData = JSON.parse(localStorage.formData)
    }

    axios.get(this.baseUrl + 'forms/' + this.formId + '/', { params: { api_key: this.publicKey, signature: signature, expires: this.expires } }).then(
      (response) => {
        if (response.status === 200) {
          if (response.data.response.confirmations) {
            var confirmations = response.data.response.confirmations
            this.confirmation = confirmations[Object.keys(confirmations)[0]].message
          }

          this.allFields = response.data.response.fields
        }
      }
    )
  }
}
</script>
<style>
.loading, .error {
  display: flex;
  justify-content: center;
}
</style>
