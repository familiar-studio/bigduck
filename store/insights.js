export const state = () => ({
  insights: []
})

export const mutations = {
  load (state, insights) {
    state.insights = insights
  }
}
  
