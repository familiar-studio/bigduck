<template>
  <div>
    <ul class="nav nav-pills">
      <li class="nav-item">
        <router-link :to="{ name: 'work'}" class="nav-link">Featured Work</router-link>
      </li>
      <li class="nav-item">
        <router-link :to="{ name: 'work-all'}" class="nav-link">All Projects</router-link>
      </li>
    </ul>
    <nuxt-child/>
  </div>
</template>
