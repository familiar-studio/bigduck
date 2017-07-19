<template>
  <div v-once class="event">
    <!-- <div> -->
    <div class="block-overlap block-event" :class="blockClass">
      <nuxt-link :to="{ name: 'events-slug', params: { slug: entry.slug }}" :key="entry.id">
        <div class="col-image" v-if="entry.acf.featured_image">
          <div :style="{ 'background-image': 'url(' + entry.acf.featured_image.url + ')' }" class="featured-image"></div>
        </div>
        <div class="col-text">
          <div class="card">
            <div class="card-block">
              <div class="badge-group" v-if="topics && eventCategories">
                <div class="badge badge-default" v-if="topics && entry['topic'].length > 0" v-for="topic in entry.topic">
                  <div v-if="entry['topic'][0]['term_id']" v-html="getTopicsIndexedById[entry['topic'][0]['term_id']].icon"></div><div v-else v-html="getTopicsIndexedById[entry['topic'][0]].icon"></div>
                  <div v-if="entry['topic'][0]['term_id']" v-html="getTopicsIndexedById[entry['topic'][0]['term_id']].name"></div><div v-else v-html="getTopicsIndexedById[entry['topic'][0]].name"></div>
                </div>
                <div class="badge badge-default badge-type" v-if="eventCategories && entry['event_category'].length > 0">
                  <div v-if="entry['event_category'][0]['term_id']" v-html="getEventCategoriesIndexedById[entry['event_category'][0]['term_id']].icon"></div><div v-else v-html="getEventCategoriesIndexedById[entry['event_category'][0]].icon"></div>
                  <div v-if="entry['event_category'][0]['term_id']" v-html="getEventCategoriesIndexedById[entry['event_category'][0]['term_id']].name"></div><div v-else v-html="getEventCategoriesIndexedById[entry['event_category'][0]].name"></div>
                </div>
              </div>
              <h3 class="card-title">
                <span v-if="entry.title" class="underline-change" v-html="entry.title.rendered"></span><span class="underline-change" v-else v-html="entry.post_title"></span>
              </h3>
              <div class="card-text" v-html="entry.acf.subtitle"></div>
              <div class="card-footer">
                <div class="chat-bubble">
                  <span>
                    Learn More
                  </span>
                </div>
                <div class="author-listing">

                <div v-for="team_member in relatedTeamMembers" class="media">
                  <!-- <div class="media" > -->
                    <img v-if="team_member.headshot" :src="team_member.headshot.sizes.thumbnail" class="round author-img mr-2">
                    <img v-else :src="backupAuthorImage" class="round author-img mr-2">
                    <h6 v-if="team_member.member" class="align-self-center mb-0">{{ team_member.member.display_name}}</h6>
                    <h6 v-else-if="team_member.display_name" class="align-self-center mb-0">{{ team_member.display_name }}</h6>
                  <!-- </div> -->
                </div>
                <div class="media" v-for="guest in entry.acf.guest_speakers">
                  <img :src="backupAuthorImage" class="round author-img mr-2">
                  <h6 class="align-self-center mb-0">{{guest.speaker_name}}</h6>
                </div>
              </div>
              </div>
            </div>
            <div class="">
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
  props: ['entry', 'index', 'relatedTeamMembers', 'firstBlock'],
  computed: {
    ...mapState(['backupImages', 'topics', 'eventCategories']),
    ...mapGetters(['getTopicsIndexedById', 'getEventCategoriesIndexedById']),
    blockClass() {
      if (this.index === 0 && this.firstBlock) {
        return 'first-block'
      } else if (this.index % 2 === 0) {
        return 'odd-block'
      } else {
        return 'even-block'
      }
    },
    times() {
      return {
        start: this.entry.acf.start_time,
        end: this.entry.acf.end_time
      }
    },
    displayDate() {
      return dateFns.format(this.entry.acf.start_time, 'MMM D, YYYY')
    },
    month() {
      return dateFns.format(this.entry.acf.start_time, 'MMM')
    },
    date() {
      return dateFns.format(this.entry.acf.start_time, 'D')
    },
    backupAuthorImage() {
      return this.backupImages['author']
    }
  }
}
</script>

<style lang="scss">
img.featured-image {
  height: 450px;
}
</style>
