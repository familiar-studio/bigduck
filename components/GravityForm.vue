<template>
  <div>
    <form v-if="!submitted">
 
      <div v-for="field in fields" class="form-group" :class="{'has-danger':errors.has(field.id.toString())}">

        <label :for="field.id" v-if="field.type != 'hidden'">{{field.label}}</label>

        <template v-if="field.type == 'select'">
          <select v-model="formData[field.id]" class="custom-select form-control">
            <option v-for="choice in field.choices" :value="choice.value">
              {{ choice.text }}
            </option>
          </select>
        </template>

        <template v-else-if="field.type == 'checkbox'">
          <div class="custom-controls-stacked">
            <label class="custom-control custom-checkbox" v-for="choice in field.choices">
              <input class="custom-control-input" type="checkbox" :name="field.id" v-model="formData[field.id]" :value="choice.value">
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description">{{ choice.text }}</span>
            </label>
          </div>
        </template>
        <template v-else-if="field.type == 'radio'">
          <div class="custom-controls-stacked">
            <label class="custom-control custom-radio" v-for="choice in field.choices">
              <input class="custom-control-input" type="radio" :name="field.id" v-model="formData[field.id]" :value="choice.value">
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description">{{ choice.text }}</span>
            </label>
          </div>
        </template>

        <template v-else-if="field.type == 'email'">
          <input v-model="formData[field.id]" type="email" :name="field.id" class="form-control" v-validate="{ rules: { required: field.isRequired, email: true } }" />
        </template>

        <template v-else-if="field.type == 'number'">
          <input v-model="formData[field.id]" type="number" :name="field.id" class="form-control" v-validate="{ rules: { required: field.isRequired, numeric: true } }" />
        </template>

        <template v-else-if="field.type == 'hidden'">
          <input v-model="formData[field.id]" :name="field.id" type="hidden"  />
        </template>

        <template v-else-if="field.type == 'textarea'">
          <textarea v-model="formData[field.id]" class="form-control"  n:name="field.id" v-validate="{ rules: { required: field.isRequired } }" />
        </template>

        <template v-else>
          <input v-model="formData[field.id]" type="text" class="form-control" :name="field.id" v-validate="{ rules: { required: field.isRequired } }" />
        </template>

        <div class="form-control-feedback" v-show="errors.has(field.id.toString())">{{ errors.first(field.id.toString()) }}</div>


      </div>

      <button type="submit" @click.prevent="submitEntry()" class="btn btn-secondary">Submit</button>
    </form>
    <div v-else>
      <h1 class="display-3">{{confirmation}}</h1>
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
      totalProfilingFields: 2,
      submitted: false,
      confirmation: 'Thanks, your the greatest!'

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
      type: Boolean,
      default: false
    }
  },
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
      localStorage.formData = JSON.stringify(this.formData)
      this.formData['form_id'] = this.formId

      axios.post(this.baseUrl + 'entries', [this.formData], { withCredentials: true, params: { api_key: this.publicKey, signature: signature, expires: this.expires } })
      this.submitted = true
    }
  },
  created () {
    var signature = this.CalculateSig('forms/' + this.formId, 'GET')

    if (process.BROWSER_BUILD && localStorage.formData) {
      this.formData = JSON.parse(localStorage.formData)
    }

    axios.get(this.baseUrl + 'forms/' + this.formId + '/', { params: { api_key: this.publicKey, signature: signature, expires: this.expires } }).then(
      (response) => {
        if (response.status === 200) {
          var profilingFieldCount = 0

          if (response.data.response.confirmations) {
            var confirmations = response.data.response.confirmations
            this.confirmation = confirmations[Object.keys(confirmations)[0]].message
          }

          this.fields = response.data.response.fields.filter((field, index) => {
            // always include the first three
            if (index < 3) {
              return field
            } else {
              if ((!this.formData[field.id] && profilingFieldCount < this.totalProfilingFields) || this.showAll) {
                profilingFieldCount++
                // if checkboxes and not already has data initalize as array to make multi-select work properly
                if (field.type === 'checkbox') {
                  this.formData[field.id] = []
                }
                return field
              }
            }
          })
        }
      }
    )
  }
}
</script>

