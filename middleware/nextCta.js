export default function({ store }) {
  if (store.state.nextCTAFlag) {
    setTimeout(() => {
      store.commit("incrementCTA");
    }, 500);
  }
}
