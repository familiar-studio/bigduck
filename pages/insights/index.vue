 <template>
  <div class="container-fluid no-hero">
    <div class="row">
      <div class="col-lg-2">
        <div v-if="types && topics" class="filter-bar menu">
          <FilterList label="Topics" taxonomy="topic" :terms="topics" :selected="selectedTopic" v-on:clicked="toggleTaxonomy($event)"></FilterList>
          <FilterList label="Types" taxonomy="type" :terms="types" :selected="selectedType" v-on:clicked="toggleTaxonomy($event)"></FilterList>
          <a v-if="selectedTopic || selectedType" href="#" @click.prevent="resetFilters" class="btn btn-primary">Clear All</a>
        </div>
      </div>
      <div class="col-xl-8 col-lg-9">
        <div class="container" id="content" v-if="insights.length > 0">
          <h1>Insights</h1>

          <div v-for="(insight, index) in insights">
            <Post :entry="insight" :index="index"></Post>
            <Subscribe v-if="callouts && callouts[0] && index % 5 == 1 && index < insights.length - 1" :entry="callouts[0]" class="mb-5"></Subscribe>
          </div>
          <div class="pager" v-if="insights.length < totalRecords">
            <a href="#" @click.prevent="nextPage">Next Page</a>
          </div>
        </div>
        <div v-else>
          No insights found in <span v-if="selectedTopic">Topic {{getTopicsIndexedById[selectedTopic].name}}</span><span v-if="selectedTopic && selectedType"> and </span><div v-if="selectedType">Type {{getTypesIndexedById[selectedType].name}}</div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>

  import Post from '~components/Post.vue'
  import Subscribe from '~components/subscribe/container.vue'
  import { mapState, mapGetters } from 'vuex'
  import axios from 'axios'
  import FilterList from '~components/FilterList.vue'

  export default {
    name: 'insights',
    async asyncData ({store, query}) {
      try {
        store.commit('resetPage')
        const response = await store.dispatch('fetchByQuery', {isPaged: true, query: query, path: 'wp/v2/bd_insight'})
        return {
          insights: response.data,
          totalPages: response.headers['x-wp-totalpages'],
          totalRecords: response.headers['x-wp-total']
        }
      } catch (e) {
        console.error(e)
      }
    },
    head: {
      title: 'Insights'
    },
    components: {
      Post,
      Subscribe,
      FilterList
    },
    computed: {
      ...mapState(['callouts', 'types', 'topics']),
      ...mapGetters(['getTopicsIndexedById', 'getTypesIndexedById']),
      selectedType () {
        return this.$route.query.type
      },
      selectedTopic () {
        return this.$route.query.topic
      }
    },
    watch: {
      '$route.query': 'filterResults'
    },
    methods: {
      toggleTaxonomy (event) {
        // make a copy of the curren tquery string
        let query = Object.assign({}, this.$route.query)

        // toggle filters
        if (parseInt(query[event.taxonomy]) === event.id) {
          delete query[event.taxonomy]
        } else {
          query[event.taxonomy] = event.id
        }
        this.$router.push({ name: 'insights', query: query })
      },
      resetFilters () {
        this.$router.push({ name: 'insights', query: null })
      },
      async filterResults () {
        this.$store.commit('resetPage')
        const response = await this.$store.dispatch('fetchByQuery', { isPaged: true, path: 'wp/v2/bd_insight', query: this.$route.query })

        this.insights = response.data
        this.totalPages = response.headers['x-wp-totalpages']
        this.totalRecords = response.headers['x-wp-total']
      },
      async nextPage () {
        this.$store.commit('nextPage')
        let query = Object.assign({}, this.$route.query)
        const response = await this.$store.dispatch('fetchByQuery', { isPaged: true, query: query, path: 'wp/v2/bd_insight' })
        this.insights = this.insights.concat(response.data)
      }
    }
  }
</script>
