<template>

  <div class="block-insights" :class="blockClass" :type="types && entry.type[0] ? getTypesIndexedById[entry.type[0]].slug : ''" v-if="entry.type">
    <router-link :to="{ name: 'insights-id', params: { id: entry.id }}" :key="entry.id">
    <div class="col-image">
      <div :style="{ 'background-image': 'url(' + entry.acf.featured_image+ ')' }" class="featured-image"></div>
    </div>
    <div class="col-text">
        <div class="card">
          <div class="card-block">
            <div class="badge-group">
              <div class="badge badge-default">
                  <div>Badges TK</div>
              </div>
              <div class="badge badge-default badge-type" v-for="type in entry.type">
                  <div v-html="getTypesIndexedById[type].icon"></div>
                  <div v-html="getTypesIndexedById[type].name"></div>
              </div>
              <div class="badge badge-default" v-for="topic in entry.topic">
                  <div v-html="getTopicsIndexedById[topic].icon"></div>
                  <div v-html="getTopicsIndexedById[topic].name"></div>
              </div>
              <div class="badge badge-default">


                <span v-if="types && entry.type[0]">
                    <span v-if="getTypesIndexedById[entry.type[0]].verb == 'read'">
                      {{entry.acf.calculated_reading_time.data}}
                    </span>
                      <span v-else>
                        <span>{{ entry.acf.time }}
                         {{ entry.acf.time_interval }}</span>
                       </span>
                    &nbsp;{{ getTypesIndexedById[entry.type[0]].verb }}
                  </span>
              </div>
            </div>

            <h3 class="card-title"><span class="underlineChange" v-html="entry.title.rendered"></span></h3>
            <div class="card-text" v-html="entry.acf.short_description"></div>
            <div class="card-footer">
              <div class="chat-bubble">
                <span v-if="types && entry.type[0]">
                  {{ getTypesIndexedById[entry.type[0]].verb }} Now
                </span>
                <span v-else>
                  Read More
                </span>
              </div>
              <div class="media">
                <img v-if="entry.author_headshot" :src="entry.author_headshot.sizes.thumbnail" class="round author-img mr-2">
                <h6 class="align-self-center mb-0">{{ entry.acf.author.display_name }}</h6>
              </div>
            </div>
          </div>
        </div>
      </div>
    </router-link>
  </div>
</template>

<script>

  import { mapState, mapGetters } from 'vuex'

  export default {
    name: 'post',
    props: ['entry', 'categories', 'index'],
    computed: {
      ...mapState(['types', 'topics']),
      ...mapGetters(['getTopicsIndexedById', 'getTypesIndexedById']),
      blockClass () {
        if (this.index === 0) {
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
