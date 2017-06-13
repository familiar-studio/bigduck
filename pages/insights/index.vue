 <template>
  <div class="container-fluid no-hero">
    <div class="row">
      <div class="col-lg-2">
        <div v-if="types && topics" class="filter-bar">
          <div class="label label-lg">Topics</div>
          <div class="media-list">
            <!-- <nuxt-link v-for="topic in topics" key="topic.id" :to="{name: 'Insights', query: {topic: topic.id}}" @click="flipCategory('topic', topic.id)" :class="activeCategories[topic][topic.id] == true ? 'active' : ''"> -->
            <nuxt-link v-for="topic in topics" key="topic.id" :to="{to: '/insights', query: {topic: topic.id}}">
              <div class="media">
                <div v-if="topic.icon" v-html="topic.icon"></div>
                <div class="media-body">
                  <h6 v-html="topic.name"></h6>
                </div>
              </div>
            </nuxt-link>
          </div>
          <div class="label label-lg">Types</div>
          <div class="media-list">
            <!-- <nuxt-link v-for="type in types" key="type.id" :to="{name: 'Insights', query: {type: type.id}}" @click="flipCategory('type', type.id)" :class="activeCategories[type][type.id] == true ? 'active' : ''"> -->
            <nuxt-link v-for="type in types" key="type.id" :to="{to: '/insights', query: {type: type.id}}">
              <div class="media">
                <div v-if="type.icon" v-html="type.icon"></div>
                <div class="media-body">
                  <h6 v-html="type.name"></h6>
                </div>
              </div>
            </nuxt-link>
          </div>
          <nuxt-link :to="{to: '/insights'}">
            <button class="btn btn-info">Clear All</button>
          </nuxt-link>
        </div>
      </div>
      <div class="col-lg-8">
        <div class="container">
          <div v-if="insights">
            <h1>Insights</h1>
            <Pager :totalPages="totalPages" path="/insights" ></Pager>
            <div v-if="insights">
              <div v-for="(insight, index) in insights">

                <Post :entry="insight" :index="index"></Post>


                <Subscribe v-if="callouts && callouts[0] && index % 5 == 1 && index < insights.length - 1" :entry="callouts[0]"></Subscribe>
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
      <div class="col-lg-2">

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

  export default {
    name: 'insights',
    data () {
      return {
        flipCategory: null
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
      Pager
    },
    computed: {
      ...mapState(['categories', 'callouts', 'types', 'topics']),
      nextQuery () {
        return Object.assign(this.$route.query, {page: this.nextPage})
      },
      previousQuery () {
        return Object.assign(this.$route.query, {page: this.previousPage})
      }
    },
    methods: {
      flipCategory (type, id) {
        this.activeCategories[type][id] !== this.activeCategories[type][id]
      },
      setActiveCategories () {
        let activeCategories = {}
        let categories = ['Type', 'Topic', 'Sector']
        categories.forEach((category) => {
          activeCategories[category] = this.$store.getters['get' + category + 'sIndexedById']
          console.log('get' + category + 'sIndexedById')
          // debugger
          Object.keys(activeCategories[category]).forEach((term) => {
            term = false
          })
        })
        this.activeCategories = activeCategories
      }
    }
  }
</script>
