<template>
    <RegisterForm />
</template>

<script setup>
import { useHead } from '@vueuse/head';
import RegisterForm from '../../components/auth/RegisterForm.vue';
useHead({
    title: "Đăng ký | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Đăng ký"
        }
    ]
})
</script>
