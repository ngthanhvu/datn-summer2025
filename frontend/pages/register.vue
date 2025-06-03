<template>
  <RegisterContent />
</template>

<script setup>
import RegisterContent from '~/components/auth/RegisterContent.vue'

definePageMeta({
  layout: 'default',
  middleware: 'guest'
})

useHead({
  title: 'Đăng Ký - DEVGANG',
  meta: [
    { name: 'description', content: 'Đăng Ký - DEVGANG' },
  ],
})
</script>
