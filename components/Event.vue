<template>
  <div>
    <!-- <div v-if="types && topics"> -->


  <div class="block-insights" :class="blockClass" :type="types ? typesIndexedById[entry.type[0]].slug : ''">
    <router-link :to="{ to: '/event', params: { id: entry.id }}" :key="entry.id">
    <div class="col-image" v-if="entry.acf.featured_image">
      <div :style="{ 'background-image': 'url(' + entry.acf.featured_image.url + ')' }" class="featured-image"></div>
    </div>
    <div class="col-text">
        <div class="card" >
          <div class="card-header badge-group">
            <div class="badge badge-secondary badge-type">
              {{displayDate}}
            </div>
            <div class="badge badge-default badge-type" v-if="types" v-for="type in entry.type" >
                <div v-html="typesIndexedById[type].icon"></div>
                <div v-html="typesIndexedById[type].name"></div>
            </div>
            <div class="badge badge-default"  v-if="topics" v-for="topic in entry.topic">
                <div v-html="topicsIndexedById[topic].icon"></div>
                <div v-html="topicsIndexedById[topic].name"></div>
            </div>
          </div>
          <div class="card-block">

            <h3 class="card-title"><span class="color-underline" v-html="entry.title.rendered"></span></h3>
            <div class="card-text" v-html="entry.acf.subtitle"></div>
          </div>
          <div class="card-footer">
            <div class="chat-bubble">
              <span v-if="entry.type[0] && types">
                {{ typesIndexedById[entry.type[0]].verb }} Now
              </span>
              <span v-else>
                Read More
              </span>
            </div>
            <div class="media" v-for="team_member in entry.related_team_members.data">
              <img v-if="team_member.headshot" :src="team_member.headshot.sizes.thumbnail" class="round author-img mr-2">
              <h6 class="align-self-center mb-0">{{ team_member.member.display_name}}</h6>
            </div>
          </div>
        </div>
      </div>
    </router-link>
  </div>
<!-- </div> -->
</div>
</template>

<script>
  import moment from 'moment'

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
      months () {
        return this.$store.state.events.months
      },
      blockClass () {
        if (this.index === 0) {
          return 'first-block'
        } else if (this.index % 2 === 0) {
          return 'odd-block'
        } else {
          return 'even-block'
        }
      },
      times () {
        return {
          start: this.entry.acf.start_time,
          end: this.entry.acf.end_time
        }
      },
      displayDate () {
        return moment(this.entry.acf.start_time).format('MMM Do YYYY')
      },
      types () {
        return this.$store.state.types
      },
      topics () {
        return this.$store.state.topics
      }
    }
  }
</script>

<style>
  img.featured-image {
    height: 450px;
  }
</style>
