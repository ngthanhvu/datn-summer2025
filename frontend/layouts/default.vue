<template>
  <div class="tw-flex tw-flex-col tw-min-h-screen">
    <HeaderHome :site-logo="siteLogo" />

    <div class="tw-flex-grow tw-bg-[#F5F5FA]">
      <slot />
    </div>

    <FooterHome />

    <!-- Chat Widget -->
    <ChatWidget v-if="isAuthenticated" />
  </div>
</template>

<script setup>
useHead({
  link: [
    {
      rel: 'stylesheet',
      href: 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css'
    },
    {
      rel: 'stylesheet',
      href: 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'
    }
  ],
  script: [
    {
      src: 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js',
      integrity: 'sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO',
      crossorigin: 'anonymous'
    }
  ]
})

import HeaderHome from '~/components/home/HeaderHome.vue'
import FooterHome from '~/components/home/FooterHome.vue'
import ChatWidget from '~/components/chat/ChatWidget.vue'
import { useAuth } from '~/composables/useAuth'

const { isAuthenticated } = useAuth()

// Nhận siteLogo từ app.vue
defineProps({
  siteLogo: {
    type: String,
    default: '/logo.png'
  }
})
</script>
