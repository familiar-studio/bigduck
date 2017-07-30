<script>
export default {
  // if url doesnt exist try and find it in insights
  fetch({ redirect, route }) {
    return redirect('/insights' + route.path)
  }
}
</script>

