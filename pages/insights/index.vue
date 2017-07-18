 <template>
  <div class="">
    <div class="row">
      <div class="col-lg-3 col-xl-2">
        <div v-if="types && topics" class="filter-bar menu">
          <FilterList label="Topics" taxonomy="topic" :terms="topics" :selected="selectedTopic" v-on:clicked="toggleTaxonomy($event)"></FilterList>
          <FilterList label="Types" taxonomy="type" :terms="types" :selected="selectedType" v-on:clicked="toggleTaxonomy($event)"></FilterList>
          <a v-if="selectedTopic || selectedType" href="#" @click.prevent="resetFilters" class="btn btn-primary">Clear All</a>
        </div>
      </div>
      <div class="col-xl-8 col-lg-9">
        <div class="container" id="content">
          <h1>Insights</h1>
          <div v-if="insights && insights.length > 0">
          <!-- <ListTransition :previous="previouslyLoadedInsights" :current="insights.length"> -->
          <!-- <transition-group name="fade" appear> -->
            <div v-for="(insight, index) in insights" :key="insight" :data-index="index">
              <Post :entry="insight" :firstBlock="true" :index="index"></Post>
              <transition name="list" appear>
                <InlineCallout class="mb-5" v-if="index % 5 == 1 && index < insights.length - 1"></InlineCallout>
              </transition>
            </div>
          <!-- </transition-group> -->
          <!-- </ListTransition> -->
            <div class="pager" v-if="insights.length < totalRecords">
              <a class="btn btn-primary my-4" href="#" @click.prevent="nextPage">Load more</a>
            </div>
          </div>
          <div v-else>
            <div class="">
              There are currently no
              <span v-if="selectedTopic">{{getTopicsIndexedById[selectedTopic].name}}</span>
              <span v-if="selectedTopic && selectedType"> or </span>
              <span v-if="selectedType">{{getTypesIndexedById[selectedType].name}}</span> insights.
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-2">
        <Chat></Chat>
      </div>
    </div>
  </div>
</template>
<script>

import axios from 'axios'
import Chat from '~components/Chat.vue'
import FilterList from '~components/FilterList.vue'
import InlineCallout from '~components/InlineCallout.vue'
import { mapState, mapGetters } from 'vuex'
import Post from '~components/Post.vue'

export default {
  name: 'insights',
  async asyncData({ store, query }) {
    try {
      store.commit('resetPage')
      const response = await store.dispatch('fetchByQuery', { isPaged: true, query: query, path: 'wp/v2/bd_insight' })
      return {
        insights: response.data,
        totalPages: response.headers['x-wp-totalpages'],
        totalRecords: response.headers['x-wp-total']
      }
    } catch (e) {
      console.error(e)
    }
  },
  data() {
    return {
      previouslyLoadedInsights: 0
    }
  },
  head () {
    if (this.insights){
    return {
      title: 'Insights',
      meta: [
        {
          'property': 'og:title',
          'content': 'Insights'
        },
        {
          'property': 'twitter:title',
          'content': 'Insights'
        },
        {
          'property': 'description',
          'content': 'Read more about the results of our work.'
        },
        {
          'property': 'og:description',
          'content': 'Read more about the results of our work.'
        },
        {
          'property': 'twitter:description',
          'content': 'Read more about the results of our work.'
        },
        {
          'property': 'image',
          'content': this.insights[0].acf.featured_image.url
        },
        {
          'property': 'og:image:url',
          'content': this.insights[0].acf.featured_image.url
        },
        {
          'property': 'twitter:image',
          'content': this.insights[0].acf.featured_image.url
        }
      ]
    }
    }
  },
  components: {
    Chat,
    FilterList,
    InlineCallout,
    Post
  },
  computed: {
    ...mapState(['types', 'topics']),
    ...mapGetters(['getTopicsIndexedById', 'getTypesIndexedById']),
    selectedType() {
      return this.$route.query.type
    },
    selectedTopic() {
      return this.$route.query.topic
    }
  },
  watch: {
    '$route.query': 'filterResults'
  },
  methods: {
    toggleTaxonomy(event) {
      // make a copy of the current query string
      let query = Object.assign({}, this.$route.query)

      // toggle filters
      if (parseInt(query[event.taxonomy]) === event.id) {
        delete query[event.taxonomy]
      } else {
        query[event.taxonomy] = event.id
      }
      this.$router.push({ name: 'insights', query: query })
    },
    resetFilters() {
      this.$router.push({ name: 'insights', query: null })
    },
    async filterResults() {
      this.$store.commit('resetPage')
      const response = await this.$store.dispatch('fetchByQuery', { isPaged: true, path: 'wp/v2/bd_insight', query: this.$route.query })
      this.insights = response.data
      this.totalPages = response.headers['x-wp-totalpages']
      this.totalRecords = response.headers['x-wp-total']
    },
    async nextPage() {
      this.$store.commit('nextPage')
      this.previouslyLoadedInsights = this.insights.length
      let query = Object.assign({}, this.$route.query)
      const response = await this.$store.dispatch('fetchByQuery', { isPaged: true, query: query, path: 'wp/v2/bd_insight' })
      this.insights = this.insights.concat(response.data)
    }
  }
}
</script>
