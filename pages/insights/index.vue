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
        <div class="container" id="content" v-if="insights">
          <h1>Insights</h1>
          <Pager :totalPages="totalPages" path="/insights" ></Pager>
    
          <div v-for="(insight, index) in insights">
            <Post :entry="insight" :index="index"></Post>
            <Subscribe v-if="callouts && callouts[0] && index % 5 == 1 && index < insights.length - 1" :entry="callouts[0]" class="mb-5"></Subscribe>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>

  import Post from '~components/Post.vue'
  import Subscribe from '~components/subscribe/container.vue'
  import { mapState } from 'vuex'
  import Pager from '~components/Pager.vue'
  import axios from 'axios'
  import FilterList from '~components/FilterList.vue'

  export default {
    name: 'insights',
    data () {
      return {
        selected: {
          insights: null,
          totalPages: null,
          totalRecords: null
        }
      }
    },
    async asyncData ({state, query, store}) {
      try {
        const response = await store.dispatch('fetchByQuery', {query: query, path: 'wp/v2/bd_insight'})
        return {
          insights: response.data,
          totalPages: response.totalPages,
          totalRecords: response.totalRecords
        }
      } catch (e) {
        console.error(e)
      }
    },
    head () {
      return {
        title: 'Insights'
      }
    },
    components: {
      Post,
      Subscribe,
      Pager,
      FilterList
    },
    computed: {
      ...mapState(['callouts', 'types', 'topics']),
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
  
      // given a taxonomy type and id
      // async setActiveTaxonomy (event) {
      //   const type = event.taxonomy
      //   const id = event.id
      //   // if the selected taxonomy is equal to the passed id, set it to null, otherwise set it
      //   this.selected[type] = this.selected[type] === id.toString() ? null : id.toString()
      //   let query = {}
      //   // build a query to send to the store
      //   Object.keys(this.selected).forEach((key) => {
      //     // if a property is null, skip over it
      //     if (this.selected[key]) {
      //       query[key] = this.selected[key]
      //     }
      //   })
      //   this.$store.commit('setFilterQuery', query)
      //   let response = await this.$store.dispatch('fetchByQuery', {path: 'wp/v2/bd_insight', query: this.$store.state.query})
      //   this.insights = response.data
      // },
      toggleTaxonomy (event) {
        // make a copy of the curren tquery string
        var query = Object.assign({}, this.$route.query)

        // toggle filters
        if (query[event.taxonomy] === event.id) {
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
        const response = await this.$store.dispatch('fetchByQuery', { path: 'wp/v2/bd_insight', query: this.$route.query })

        this.insights = response.data
        this.totalPages = response.totalPages
        this.totalRecords = response.totalRecords
      }
    }
  }
</script>
