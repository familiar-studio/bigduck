<template>
  <div class="share" id="share">
    <h6>Share</h6>
    <div>
      <a :href="'https://www.facebook.com/sharer/sharer.php?u=' + path" target="_blank" class="fillChange">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M18.768 7.465h-4.268v-1.905c0-.896.594-1.105 1.012-1.105h2.988v-3.942l-4.329-.013c-3.927 0-4.671 2.938-4.671 4.82v2.145h-3v4h3v12h5v-12h3.851l.417-4z"/></svg>
      </a>
      <a :href="'https://twitter.com/intent/tweet?text=' + path" target="_blank" class="fillChange">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M23.444 4.834c-.814.363-1.5.375-2.228.016.938-.562.981-.957 1.32-2.019-.878.521-1.851.9-2.886 1.104-.827-.882-2.008-1.435-3.315-1.435-2.51 0-4.544 2.036-4.544 4.544 0 .356.04.703.117 1.036-3.776-.189-7.125-1.998-9.366-4.748-.391.671-.615 1.452-.615 2.285 0 1.577.803 2.967 2.021 3.782-.745-.024-1.445-.228-2.057-.568l-.001.057c0 2.202 1.566 4.038 3.646 4.456-.666.181-1.368.209-2.053.079.579 1.804 2.257 3.118 4.245 3.155-1.945 1.524-4.356 2.159-6.728 1.881 2.012 1.289 4.399 2.041 6.966 2.041 8.358 0 12.928-6.924 12.928-12.929l-.012-.588c.887-.64 1.953-1.237 2.562-2.149z"/></svg>
      </a>
      <a :href="'http://www.linkedin.com/shareArticle?mini=true&url=' + path" class="fillChange">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M6.527 21.5h-5v-13h5v13zm-2.509-15h-.03c-1.51 0-2.488-1.182-2.488-2.481 0-1.329 1.008-2.412 2.547-2.412 1.541 0 2.488 1.118 2.519 2.447-.001 1.3-.978 2.446-2.548 2.446zm11.509 6c-1.105 0-2 .896-2 2v7h-5s.059-12 0-13h5v1.485s1.548-1.443 3.938-1.443c2.962 0 5.062 2.144 5.062 6.304v6.654h-5v-7c0-1.104-.895-2-2-2z"/></svg>
      </a>
    </div>
  </div>
</template>
<script>
  export default {
    computed: {
      path () {
        console.log(this.$route.fullPath)
        return this.$route.fullPath
      }
    }
  }
</script>
