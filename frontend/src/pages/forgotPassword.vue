<template>
    <ForgotPassword />
</template>

<script setup>
import ForgotPassword from '../components/auth/ForgotPassword.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Quên mật khẩu | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Quên mật khẩu"
        }
    ]
})
</script>