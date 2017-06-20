<template>
  <div>
    <!-- <div> -->
  <div class="block-overlap block-event" :class="blockClass" >
    <nuxt-link :to="{ name: 'events-slug', params: { slug: entry.slug }}" :key="entry.id">
      <div class="col-image" v-if="entry.acf.featured_image">
        <div :style="{ 'background-image': 'url(' + entry.acf.featured_image.url + ')' }" class="featured-image"></div>
      </div>
      <div class="col-text">
        <div class="card" >
          <div class="card-block">
            <div class="badge-group" v-if="topics && eventCategories">
              <div class="badge badge-default badge-type" v-if="eventCategories && entry['event_category'].length > 0">
                  <div v-html="getEventCategoriesIndexedById[entry['event_category'][0]].icon"></div>
                  <div v-html="getEventCategoriesIndexedById[entry['event_category'][0]].name"></div>
              </div>
              <div class="badge badge-default"  v-if="topics && entry['topic'].length > 0" v-for="topic in entry.topic">
                  <div v-html="getTopicsIndexedById[entry['topic'][0]].icon"></div>
                  <div v-html="getTopicsIndexedById[entry['topic'][0]].name"></div>
              </div>
            </div>
            <h3 class="card-title"><span class="underlineChange" v-html="entry.title.rendered"></span></h3>
            <div class="card-text" v-html="entry.acf.subtitle"></div>
            <div class="card-footer">
              <div class="chat-bubble">
                <span>
                  Learn More
                </span>
              </div>
              <div class="media" v-for="team_member in relatedTeamMembers">
                <img v-if="team_member.headshot" :src="team_member.headshot.sizes.thumbnail" class="round author-img mr-2">
                <h6 class="align-self-center mb-0">{{ team_member.member.display_name}}</h6>
              </div>
            </div>
          </div>
          <div>
            <div class="event-date">
              <h6>{{month}}</h6>
              <h2>{{date}}</h2>
            </div>
          </div>
        </div>
      </div>
    </nuxt-link>
  </div>
<!-- </div> -->
</div>
</template>

<script>
  import dateFns from 'date-fns'
  import { mapState, mapGetters } from 'vuex'

  export default {
    name: 'featured',
    props: ['entry', 'index', 'relatedTeamMembers'],
    computed: {
      ...mapState(['topics', 'eventCategories']),
      ...mapGetters(['getTopicsIndexedById', 'getEventCategoriesIndexedById']),
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
        return dateFns.format(this.entry.acf.start_time, 'MMM D, YYYY')
      },
      month () {
        return dateFns.format(this.entry.acf.start_time, 'MMM')
      },
      date () {
        return dateFns.format(this.entry.acf.start_time, 'D')
      }
    }
  }
</script>

<style>
  img.featured-image {
    height: 450px;
  }
</style>
