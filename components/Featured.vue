<template>

  <div class="block-insights" :class="blockClass" v-if="entry.type">
    <router-link :to="{ name: 'Insight', params: { id: entry.id }}" :key="entry.id">
    <div>
        <div class="container">
          <!-- <div class="card-header badge-group"> -->
            <div class="badge badge-default badge-type" v-for="type in entry.type" v-if="typesIndexedById">
                <div v-html="typesIndexedById[type].icon"></div>
                <div v-html="typesIndexedById[type].name"></div>
            </div>
            <div class="badge badge-default" v-for="topic in entry.topic" v-if="topicsIndexedById">
                <div v-html="topicsIndexedById[topic].icon"></div>
                <div v-html="topicsIndexedById[topic].name"></div>
            </div>
            <div class="badge badge-default">


              <span v-if="entry.type[0]">
                  <span v-if="typesIndexedById[entry.type[0]].verb == 'read'">
                    {{entry.acf.calculated_reading_time.data}}
                  </span>
                    <span v-else>
                      <span>{{ entry.acf.time }}
                       {{ entry.acf.time_interval }}</span>
                     </span>
                  &nbsp;{{ typesIndexedById[entry.type[0]].verb }}
                </span>
            </div>
          <div class="card-block">

            <h3 class="card-title"><span class="color-underline" v-html="entry.title.rendered"></span></h3>
            <div class="card-text" v-html="entry.acf.short_description"></div>
          </div>
          <!-- </div> -->
          <div class="card-footer">
            <div class="chat-bubble">
              <span v-if="entry.type[0]">
                {{ typesIndexedById[entry.type[0]].verb }} Now
              </span>
              <span v-else>
                Read More
              </span>
            </div>
            <div class="media">
              <img v-if="entry.author_headshot" :src="entry.author_headshot.sizes.thumbnail" class="round author-img mr-2">
              <!-- <h6 class="align-self-center mb-0">{{ entry.acf.author.display_name }}</h6> -->
            </div>
          </div>
        </div>
      </div>
    </router-link>
  </div>
</template>

<script>
  export default {
    name: 'featured',
    props: ['entry', 'categories', 'index'],
    computed: {
      topicsIndexedById () {
        return this.$store.getters['getTopicsIndexedById']
      },
      typesIndexedById () {
        return this.$store.getters['getTypesIndexedById']
      },
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
