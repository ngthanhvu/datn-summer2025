<template>
    <LoginForm />
</template>

<script setup>
import { useHead } from '@vueuse/head';
import LoginForm from '../../components/auth/LoginForm.vue';
useHead({
    title: "Đăng nhập | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Đăng nhập"
        }
    ]
});
</script>
