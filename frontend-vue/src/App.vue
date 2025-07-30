<template>
  <router-view />
  <Notivue v-slot="item">
    <Notification :item="item" :theme="pastelTheme" />
  </Notivue>
</template>
<script setup>
import { Notivue, Notification, pastelTheme } from 'notivue'
</script>