<script>
export default {
  // if url doesnt exist try and find it in insights
  fetch({ redirect, params }) {
    return redirect('/insights/' + params.redirect)
  }
}
</script>

