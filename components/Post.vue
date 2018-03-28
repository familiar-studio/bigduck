<template>
  <div v-once v-if="entry" class="block-overlap" :class="blockClass" :type="types && entry.type && firstType ? getTypesIndexedById[firstType].slug : ''">
    <nuxt-link :to="{ name: 'insights-slug', params: { slug: slug }}" :key="entry.id">
      <div class="col-image">
        <div v-if="entry.acf.featured_image" :style="{ 'background-image': 'url(' + entry.acf.featured_image+ ')' }" class="featured-image"></div>
        <div class="featured-image" v-else :style="{ 'background-image': 'url(' + backupImage + ')' }">

        </div>
      </div>
      <div class="col-text">
        <div class="card">
          <div class="card-block" v-if="entry.type">
            <div class="badge-group" v-if="entry.type">
              <div class="badge badge-default" v-for="(topic, index) in entry.topic" v-if="getTopicsIndexedById">
                <div v-if="entry['topic'][index] && entry['topic'][index]['term_id']" v-html="getTopicsIndexedById[entry['topic'][index]['term_id']].icon"></div>
                <div v-else v-html="getTopicsIndexedById[entry['topic'][index]].icon"></div>
                <div v-if="entry['topic'][index] && entry['topic'][index]['term_id']" v-html="getTopicsIndexedById[entry['topic'][index]['term_id']].name"></div>
                <div v-else v-html="getTopicsIndexedById[entry['topic'][index]].name"></div>
              </div>
              <div class="badge badge-default badge-type" v-for="(type, index) in entry.type" v-if="getTypesIndexedById">
                <div v-if="entry['type'][index] && entry['type'][index]['term_id']" v-html="getTypesIndexedById[entry['type'][index]['term_id']].icon"></div>
                <div v-else v-html="getTypesIndexedById[entry['type'][index]].icon"></div>
                <div v-if="entry['type'][index] && entry['type'][index]['term_id']" v-html="getTypesIndexedById[entry['type'][index]['term_id']].name"></div>
                <div v-else v-html="getTypesIndexedById[entry['type'][index]].name"></div>
              </div>

              <div class="badge badge-default">
                <span v-if="types && firstType">
                  <span v-if="entry['type'] == '19'">
                    {{entry.calculated_reading_time.data}} Read
                  </span>

                </span>
              </div>
            </div>

            <h3 class="card-title">
              <span class="underline-change" v-if="entry.title.rendered" v-html="entry.title.rendered"></span>
              <span class="underline-change" v-else v-html="entry.title"></span>
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
              <div class="author-listing" v-if="entry.authors">

                <div v-for="author in entry.authors" v-if="entry.authors.length > 0" class="media">
                  <img v-if="author.meta.headshot.sizes" :src="author.meta.headshot.sizes.thumbnail" class="round author-img mr-2">
                  <img v-else-if="globals" :src="globals.backup_author_image" class="round author-img mr-2">
                  <h6 class="align-self-center mb-0">
                    <span v-html="author.display_name"></span>
                  </h6>
                </div>
                <div v-if="entry.acf.guest_author_name" class="media ">
                  <img v-if="globals" :src="globals.backup_author_image" class="round author-img mr-2">
                  <h6 class="align-self-center mb-0">
                    <span>{{entry.acf.guest_author_name}}</span>
                  </h6>
                </div>
              </div>
              <div v-else class="author-listing">
                <div v-if="entry.acf.author.length > 0" class="media" v-for="author in entry.acf.author">
                  <img v-if="entry.author_headshots && entry.author_headshots[author['user_nicename']] && entry.author_headshots[author['user_nicename']].sizes" :src="entry.author_headshots[author['user_nicename']].sizes.thumbnail" class="round author-img mr-2">
                  <img v-else-if="globals" :src="globals.backup_author_image" class="round author-img mr-2">
                  <h6 class="align-self-center mb-0">
                    <span v-html="author.display_name"></span>
                  </h6>
                </div>
                <div v-if="entry.acf.guest_author_name" class="media">
                  <img v-if="globals" :src="globals.backup_author_image" class="round author-img mr-2">
                  <h6 class="align-self-center mb-0">
                    <span>{{entry.acf.guest_author_name}}</span>
                  </h6>
                </div>
              </div>
              <div v-if="!entry.acf.guest_author_name && entry.acf.author.length < 1" class="media">
                <img v-if="globals" :src="globals.backup_author_image" class="round author-img mr-2">
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
import { mapState, mapGetters } from "vuex";

export default {
  name: "post",
  props: ["entry", "categories", "index", "firstBlock"],
  computed: {
    ...mapState(["globals", "types", "topics"]),
    ...mapGetters(["getTopicsIndexedById", "getTypesIndexedById"]),
    backupImage() {
      if (this.globals) {
        let images = this.globals.backup_insights_images;
        let id = this.entry.ID || this.entry.id;
        return images[id % images.length].backup_insight_image;
      } else return null;
    },
    firstType() {
      return this.entry.type[0] && this.entry.type[0].term_id
        ? parseInt(this.entry.type[0].term_id)
        : this.entry.type[0];
    },
    blockClass() {
      if (this.index === 0 && this.firstBlock) {
        return "first-block";
      } else if (this.index % 2 === 0) {
        return "odd-block";
      } else {
        return "even-block";
      }
    },
    slug() {
      return this.entry.slug ? this.entry.slug : this.entry.post_name;
    }
  }
};
</script>
