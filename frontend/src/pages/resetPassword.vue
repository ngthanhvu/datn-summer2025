<template>
    <ResetPassword />
</template>

<script setup>
import ResetPassword from '../components/auth/ResetPassword.vue'
import { useHead } from '@vueuse/head'
useHead({
    title: 'Xác nhận mật khẩu - DEVGANG',
    meta: [{ name: 'description', content: 'Xác nhận mật khẩu - DEVGANG' }]
})
</script>