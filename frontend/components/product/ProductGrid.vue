<template>
  <div class="tw-grid tw-grid-cols-2 sm:tw-grid-cols-2 md:tw-grid-cols-3 lg:tw-grid-cols-4 tw-gap-4">
    <Card v-for="i in 8" :key="i" />
  </div>
</template>

<script setup>
import Card from '~/components/home/Card.vue'
</script> 