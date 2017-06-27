<template>
  <div v-once class="block-overlap" :class="blockClass" :type="types && entry.type && firstType ? getTypesIndexedById[firstType].slug : ''">
    <nuxt-link :to="{ name: 'insights-slug', params: { slug: entry.slug }}" :key="entry.id">
      <div class="col-image">
        <div :style="{ 'background-image': 'url(' + entry.acf.featured_image+ ')' }" class="featured-image"></div>
      </div>
      <div class="col-text">
        <div class="card">
          <div class="card-block" v-if="entry.type">
            <div class="badge-group" v-if="entry.type">
              <div class="badge badge-default" v-for="topic in entry.topic">
                <div v-if="entry['topic'][0]['term_id']" v-html="getTopicsIndexedById[entry['topic'][0]['term_id']].icon"></div><div v-else v-html="getTopicsIndexedById[entry['topic'][0]].icon"></div>
                <div v-if="entry['topic'][0]['term_id']" v-html="getTopicsIndexedById[entry['topic'][0]['term_id']].name"></div><div v-else v-html="getTopicsIndexedById[entry['topic'][0]].name"></div>
              </div>
              <div class="badge badge-default badge-type" v-for="type in entry.type">
                <div v-if="entry['type'][0]['term_id']" v-html="getTypesIndexedById[entry['type'][0]['term_id']].icon"></div><div v-else v-html="getTypesIndexedById[entry['type'][0]].icon"></div>
                <div v-if="entry['type'][0]['term_id']" v-html="getTypesIndexedById[entry['type'][0]['term_id']].name"></div><div v-else v-html="getTypesIndexedById[entry['type'][0]].name"></div>
              </div>

              <div class="badge badge-default">
                <span v-if="types && firstType && entry.acf.time">
                  <span v-if="getTypesIndexedById[firstType].verb == 'Read' && entry.calculated_reading_time">
                    {{entry.calculated_reading_time.data}} {{ getTypesIndexedById[firstType].verb }}
                  </span>
                  <span v-else-if="entry.acf.time && entry.acf.time_interval">
                    {{ entry.acf.time }} {{ entry.acf.time_interval }} {{ getTypesIndexedById[firstType].verb }}
                  </span>

                </span>
              </div>
            </div>

            <h3 class="card-title">
              <span class="underlineChange" v-if="entry.title.rendered" v-html="entry.title.rendered"></span><span class="underlineChange" v-else v-html="entry.title"></span>
            </h3>
            <div class="card-text" v-html="entry.acf.short_description"></div>
            <div class="card-footer">
              <div class="chat-bubble">
                <span v-if="types && firstType">
                  {{ getTypesIndexedById[firstType].verb }} Now
                </span>
                <span v-else>
                  Read More
                </span>
              </div>
              <div class="media" v-if="entry.authors">
                  <div v-if="entry.authors.length > 0" v-for="author in entry.authors" class="media">
                  <img v-if="author.meta.headshot.sizes" :src="author.meta.headshot.sizes.thumbnail" class="round author-img mr-2">
                  <h6 class="align-self-center mb-0">
                    <span v-html="author.display_name"></span>
                  </h6>
                </div>
              </div>
              <div v-else>
                  <div v-if="entry.acf.author.length > 0" v-for="author in entry.acf.author" class="media">
                  <img v-if="entry.author_headshots && entry.author_headshots[author['ID']] && entry.author_headshots[author['ID']].sizes" :src="entry.author_headshots[author['ID']].sizes.thumbnail" class="round author-img mr-2">
                  <h6 class="align-self-center mb-0">
                    <span v-html="author.display_name"></span>
                  </h6>
                </div>
              </div>
              <div v-if="entry.acf.guest_author_name" class="media author-no-img">
                <h6 class="align-self-center mb-0">
                  <span>{{entry.acf.guest_author_name}}</span>
                </h6>
              </div>
              <div v-if="!entry.acf.guest_author_name && entry.acf.author.length < 1" class="media author-no-img">
                <h6 class="align-self-center mb-0">
                  <span>Big Duck</span>
                </h6>
              </div>

            </div>
          </div>
        </div>
      </div>
    </nuxt-link>
  </div>
</template>

<script>

import { mapState, mapGetters } from 'vuex'

export default {
  name: 'post',
  props: ['entry', 'categories', 'index', 'firstBlock'],
  computed: {
    ...mapState(['types', 'topics']),
    ...mapGetters(['getTopicsIndexedById', 'getTypesIndexedById']),
    firstType () {
      return this.entry.type[0].term_id ? parseInt(this.entry.type[0].term_id) : this.entry.type[0]
    },
    blockClass() {
      if (this.index === 0 && this.firstBlock) {
        return 'first-block'
      } else if (this.index % 2 === 0) {
        return 'odd-block'
      } else {
        return 'even-block'
      }
    }
  }
}
</script>
