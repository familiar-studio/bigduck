<template>
  <div class="gravityforms-wrapper">

    <div v-if="heading && !submitted">
      <h2>{{ heading }}</h2>
      <div v-if="body" v-html="body"></div>
    </div>
    <div class="error" key="error" v-if="error">
      {{ error }}

    </div>

    <form v-if="!submitted && visibleFields" key="form">
      <!-- hide if optin and country is not one of the selected countries -->
      <div v-for="field in visibleFields" class="form-group" :class="{'has-danger':errors && errors.has(field.id.toString())}" v-if="(field.label == 'Opt-In' && optInCountries.includes(formData[formLabelsToIds['Country']])) ||  field.label != 'Opt-In'">

        <label :for="field.id" v-if="field.type != 'hidden'">
          {{field.label}}
        </label>

        <template v-if="field.type == 'select'">
          <select v-model="formData['input_'+field.id]" :name="field.id" class="custom-select form-control" v-validate="{ rules: { required: true } }">
            <option v-for="choice in field.choices" :value="choice.value">
              {{ choice.text }}
            </option>
          </select>
        </template>

        <template v-else-if="field.type == 'checkbox'">

          <div class="custom-controls-stacked" v-if="(field.label == 'Opt-In' && optInCountries.includes(formData[formLabelsToIds['Country']])) ||  field.label != 'Opt-In'">
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
              <input class="custom-control-input" type="radio" :name="field.id" v-model="formData['input_'+field.id]" :value="choice.value" v-validate="{ rules: { required: true } }">
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description">{{ choice.text }}</span>
            </label>
          </div>
        </template>

        <template v-else-if="field.type == 'email'">
          <input v-model="formData['input_'+field.id]" type="email" :name="field.id" class="form-control" v-validate="{ rules: { required: true, email: true } }" />
        </template>

        <template v-else-if="field.type == 'number'">
          <input v-model="formData['input_'+field.id]" type="number" :name="field.id" class="form-control" v-validate="{ rules: { required: true, numeric: true } }" />
        </template>
        <template v-else-if="field.type == 'phone'">
          <input v-model="formData['input_'+field.id]" type="text" :name="field.id" class="form-control" v-validate="{ rules: { required: true, numeric: true } }" />
        </template>

        <template v-else-if="field.type == 'hidden'">
          <input v-model="formData['input_'+field.id]" :name="field.id" type="hidden" />
        </template>

        <template v-else-if="field.type == 'textarea'">
          <textarea v-model="formData['input_'+field.id]" class="form-control" :name="field.id" v-validate="{ rules: { required: true } }" />
        </template>

        <template v-else>
          <input v-model="formData['input_'+field.id]" type="text" class="form-control" :name="field.id" v-validate="{ rules: { required: true } }" />
        </template>

        <div class="form-control-feedback" v-if="errors && errors.has(field.id.toString())">
          {{ semanticError(field.id) }}
        </div>

      </div>

      <div v-for="field in hiddenFields">
        <input v-model="formData['input_'+field.id]" :name="field.id" type="hidden" />
      </div>
      <div class="btn-loading">
        <button v-if="!loading" type="submit" @click.prevent="submitEntry()" class="btn btn-loading" :class="'btn-' + btnType">
          Submit
        </button>
        <div v-if="loading" class="loading">
          <svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 50 50" style="enable-background:new 0 0 50 50;" xml:space="preserve">
            <path fill="#fff" d="M43.935,25.145c0-10.318-8.364-18.683-18.683-18.683c-10.318,0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615c8.072,0,14.615,6.543,14.615,14.615H43.935z">
              <animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 25 25" to="360 25 25" dur="0.6s" repeatCount="indefinite" />
            </path>
          </svg>
        </div>
      </div>
    </form>
    <div v-else :key="confirmation">
      <h1 v-html="confirmation"></h1>
    </div>

  </div>
</template>

<script>
import axios from "axios";
import CryptoJS from "crypto-js";
import { mapGetters, mapMutations } from "vuex";

export default {
  data() {
    return {
      publicKey: "30d2b543ba",
      privateKey: "6cb1fab7a60e11a",
      baseUrl: "https://bigducknyc.com/gravityformsapi/",
      gravityFormData: null,
      formData: {},
      profileData: {},
      totalProfilingFields: 2,
      submitted: false,
      confirmation: "Thanks!",
      hiddenFields: [],
      visibleFields: [],
      formIdsToLabels: {},
      formLabelsToIds: {},
      loading: false,
      error: null,
      showOptIn: false,
      optInCountries: [
        "Canada",
        "Netherlands",
        "United Kingdom",
        "Spain",
        "France",
        "Germany",
        "Italy",
        "Australia"
      ]
    };
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
      default: "primary"
    },
    storagePrefix: {
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
    },
    heading: {
      type: String
    },
    body: {
      type: String
    }
  },
  computed: {
    ...mapGetters(["hostname"]),
    expires() {
      var d = new Date();
      var expiration = 3600;
      var unixtime = parseInt(d.getTime() / 1000);
      return unixtime + expiration;
    }
  },
  methods: {
    semanticError(id) {
      let fieldTitle = this.formIdsToLabels[
        "input_" + this.errors.errors[0].field
      ];
      let badErrorMsg = this.errors.errors[0].msg;
      return badErrorMsg.split(id + " field").join(fieldTitle);
    },
    CalculateSig(route, method) {
      var stringToSign =
        this.publicKey + ":" + method + ":" + route + ":" + this.expires;
      var hash = CryptoJS.HmacSHA1(stringToSign, this.privateKey);
      var base64 = hash.toString(CryptoJS.enc.Base64);
      return encodeURIComponent(base64);
    },
    async initializeForm() {
      let fieldCount = 0;

      //console.log('setup all fields')
      if (this.gravityFormData && this.gravityFormData.fields) {
        // setup some data for use later!
        this.gravityFormData.fields.forEach(field => {
          this.formIdsToLabels["input_" + field.id] = field.label;
          this.formLabelsToIds[field.label] = "input_" + field.id;

          // check if country is blank or one of the opt in countries to show both on the form

          if (process.browser && localStorage.formData) {
            this.profileData = JSON.parse(localStorage.formData);

            Object.keys(this.profileData).forEach(key => {
              var id = this.formLabelsToIds[key];
              if (id) {
                this.formData[id] = this.profileData[key];
              }
            });
          }
          if (field.label == "Opt-In") {
            console.log("optin", field);
            let optIn = false;
            if (Array.isArray(this.formData["input_" + field.id])) {
              optIn = this.formData["input_" + field.id][0] == 1 ? true : false;
            }

            if (!this.formData[this.formLabelsToIds["Country"]]) {
              this.showOptIn = true;
            } else if (
              this.optInCountries.includes(
                this.formData[this.formLabelsToIds["Country"]]
              ) &&
              !optIn
            ) {
              this.showOptIn = true;
            }
          }
        });

        this.visibleFields = this.gravityFormData.fields.filter(
          (field, index) => {
            if (field.type === "checkbox") {
              if (!this.formData["input_" + field.id]) {
                this.formData["input_" + field.id] = [];
              }
              // } else {
              //   this.formData['input_' + field.id] = this.formData[field.id]
              // }
            }

            // added support for default values
            if (field.defaultValue) {
              this.formData["input_" + field.id] = field.defaultValue;
            }

            // always include the first three
            if (index < 3) {
              return field;
            } else {
              if (this.showAll) {
                return field;
              }
              if (
                !this.formData["input_" + field.id] &&
                fieldCount < this.totalProfilingFields
              ) {
                fieldCount++;
                return field;
              }

              // if show opt in show both country and optin no matter what
              if (
                (field.label == "Country" || field.label == "Opt-In") &&
                this.showOptIn
              ) {
                return field;
              }
            }

            this.hiddenFields.push(field);
          }
        );
      }

      // get the data from local storage
    },
    async submitEntry() {
      let withCredentials = false;
      if (process.env.NODE_ENV === "production") {
        withCredentials = true;
      }

      this.$validator.validateAll().then(async result => {
        if (result) {
          this.loading = true;
          this.error = null;
          var signature = this.CalculateSig("entries", "POST");

          var endpoint = this.baseUrl + "forms/" + this.formId + "/submissions";
          console.log("endpoint", endpoint);

          // fill in prefilled data!
          if (this.actonId) {
            this.formData.input_36 = this.title;
            this.formData.input_32 = this.id;
            this.formData.input_34 = this.actonId;
            this.formData.input_35 = this.actonId;
          }

          if (this.gatedContent) {
            this.formData.input_19 = this.title;
            this.formData.input_20 = this.gatedContent;
          }
          var response = await axios.post(
            this.baseUrl + "forms/" + this.formId + "/submissions",
            { input_values: this.formData },
            {
              withCredentials: withCredentials,
              params: {
                api_key: this.publicKey,
                signature: signature,
                expires: this.expires
              }
            }
          );

          console.log(response);
          if (!response.data.response.is_valid) {
            if (response.data.response) {
              var errors = response.data.response.validation_messages;
            }
            var first = Object.keys(errors)[0];
            this.error = "Field " + first + ": " + errors[first];
            //debugger
            this.loading = false;
          } else {
            if (this.id) {
              if (process.browser) {
                localStorage[this.storagePrefix + this.id] = "true";
              }
            }

            // should loop through formdata and save it as by its label

            Object.keys(this.formData).forEach(key => {
              var label = this.formIdsToLabels[key];
              if (label) {
                this.profileData[label] = this.formData[key];
              }
            });

            localStorage.formData = JSON.stringify(this.profileData);

            if (typeof ga !== "undefined") {
              ga("send", {
                hitType: "event",
                eventCategory: "Form",
                eventAction: "submit",
                eventLabel: this.gravityFormData.title
              });
            }

            //this.updateProfile(this.formData)

            this.$emit("submitted");
            this.submitted = true;
          }
          this.loading = false;
        }
      });
    },
    ...mapMutations(["updateProfile"])
  },
  async created() {
    var signature = this.CalculateSig("forms/" + this.formId, "GET");

    const response = await axios.get(
      this.baseUrl + "forms/" + this.formId + "/",
      {
        params: {
          api_key: this.publicKey,
          signature: signature,
          expires: this.expires
        }
      }
    );
    if (response.status === 200) {
      if (response.data.response.confirmations) {
        var confirmations = response.data.response.confirmations;
        this.confirmation =
          confirmations[Object.keys(confirmations)[0]].message;
      }
      this.gravityFormData = response.data.response;
      this.initializeForm();
    }
  }
};
</script>
<style lang="scss">
.error {
  display: flex;
  justify-content: center;
}
</style>
