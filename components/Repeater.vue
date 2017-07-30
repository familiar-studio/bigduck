<template>
  <div v-for="(block, index) in repeater">
  
    <!-- TEXT  -->
    <div v-if="block.acf_fc_layout == 'text'" class="cs-block-text">
      <h2 class="mb-3 mt-5" v-html="block.heading"></h2>
      <div v-html="block.text"></div>
    </div>
  
    <!-- GALLERY  -->
    <div v-if="block.acf_fc_layout == 'gallery'" class="cs-block-gallery break-container overflow-x-hidden">
      <div class="">
        <flickity :images="block.gallery"></flickity>
      </div>
    </div>
  
    <!-- CALLOUT  -->
    <div v-if="block.acf_fc_layout == 'callout'" class="cs-block-callout break-container" :style="{ backgroundColor: caseStudy.acf.primary_color }">
      <div class="row">
        <div class="col-md-6 col-img">
          <div :style=" { backgroundImage: 'url(' + block.image.url + ')' }" class="bg-img"></div>
        </div>
        <div class="col-md-6 col-text">
          <h2 v-html="block.headline"></h2>
          <div v-html="block.text"></div>
          <a :href="block.website" v-if="block.website" class="btn btn-info" target="_blank">Visit Site</a>
        </div>
      </div>
    </div>
  
    <!-- TESTIMONIAL -->
    <div v-if="block.acf_fc_layout == 'testimonial'" class="cs-block-testimonial testimonial break-container">
      <div class="">
        <div class="row">
          <div class="col-md-8">
            <blockquote>
              <h3 v-html="block.quote"></h3>
              <footer class="label">&mdash; {{ block.credit }}</footer>
            </blockquote>
          </div>
          <div v-if="block.image" class="col-md-4">
            <img :src="block.image.sizes.cropped_400_square" alt="block.image.name" class="img-fluid">
          </div>
        </div>
      </div>
    </div>
  
    <!-- FACTOID -->
    <div v-if="block.acf_fc_layout == 'factoid'" class="cs-block-factoid">
      <h1 class="display-1">{{block.number}}</h1>
      <h6 v-html="block.description"></h6>
    </div>
  </div>
</template>
<script>
export default {
  props: ['repeater']
}
</script>

