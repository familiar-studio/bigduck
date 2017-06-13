export default function ({store}) {
  store.dispatch('fetchTopics')
  store.dispatch('fetchCallouts')
}
