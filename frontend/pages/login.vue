<template>
  <LoginContent />
</template>

<script setup>
import LoginContent from '~/components/auth/LoginContent.vue';

definePageMeta({
  layout: 'default',
  middleware: 'guest'
})

useHead({
  title: 'Đăng Nhập - DEVGANG',
  meta: [
    { name: 'description', content: 'Đăng Nhập - DEVGANG' },
  ],
})
</script>