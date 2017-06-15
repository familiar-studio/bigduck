 <template>
  <div class="container-fluid no-hero">
    <div class="row">
      <div class="col-lg-2">
        <div v-if="types && topics" class="filter-bar">
          <FilterList label="Topics" taxonomy="topic" :terms="topics" :selected="selected.topic" v-on:clicked="setActiveTaxonomy($event)"></FilterList>
          <FilterList label="Types" taxonomy="type" :terms="types" :selected="selected.type" v-on:clicked="setActiveTaxonomy($event)"></FilterList>
          <a href="#" @click.prevent="resetFilters" class="btn btn-primary">Clear All</a>
        </div>
      </div>
      <div class="col-xl-8 col-lg-9">
        <div class="container" id="content">
          <div v-if="insights">
            <h1>Insights</h1>
            <Pager :totalPages="totalPages" path="/insights" ></Pager>
            <div v-if="insights.length > 0">
              <div v-for="(insight, index) in insights">

                <Post :entry="insight" :index="index"></Post>


                <Subscribe v-if="callouts && callouts[0] && index % 5 == 1 && index < insights.length - 1" :entry="callouts[0]" class="mb-5"></Subscribe>
              </div>
            </div>
            <div v-else>
              No insights found.
            </div>
          </div>
          <div v-else>
            Loading insights...
          </div>
        </div>
        <div class="share">

        </div>
      </div>

    </div>
  </div>
  </div>
</template>
<script>

  import Post from '~components/Post.vue'
  import Subscribe from '~components/subscribe/container.vue'
  import { mapActions, mapState } from 'vuex'
  import Pager from '~components/Pager.vue'
  import axios from 'axios'
  import FilterList from '~components/FilterList.vue'

  export default {
    name: 'insights',
    data () {
      return {
        selected: {
          type: null,
          topic: null
        }
      }
    },
    async asyncData ({state, query, store}) {
      let data = {}
      try {
        const response = await store.dispatch('fetchByQuery', {query: query, path: 'wp/v2/bd_insight'})
        data['insights'] = response.data
        data['totalPages'] = response.totalPages
        data['totalRecords'] = response.totalRecords
        return data
      } catch (e) {
        console.log('error')
        console.log(error)
      }
    },
    head () {
      return {
        title: 'Insights | Big Duck'
      }
    },
    components: {
      Post,
      Subscribe,
      Pager,
      FilterList
    },
    computed: {
      ...mapState(['callouts', 'types', 'topics'])
    },
    methods: {
      // given a taxonomy type and id
      async setActiveTaxonomy (event) {
        const type = event.taxonomy
        const id = event.id
        // if the selected taxonomy is equal to the passed id, set it to null, otherwise set it
        this.selected[type] = this.selected[type] === id.toString() ? null : id.toString()
        let query = {}
        // build a query to send to the store
        Object.keys(this.selected).forEach((key) => {
          // if a property is null, skip over it
          if (this.selected[key]) {
            query[key] = this.selected[key]
          }
        })
        this.$store.commit('setFilterQuery', query)
        let response = await this.$store.dispatch('fetchByQuery', {path: 'wp/v2/bd_insight', query: this.$store.state.query})
        this.insights = response.data
      },
      async resetFilters () {
        this.$store.commit('setFilterQuery', {})
        let response = await this.$store.dispatch('fetchByQuery', {path: 'wp/v2/bd_insight', query: this.$store.state.query})
        this.insights = response.data
      }
    }
  }
</script>
