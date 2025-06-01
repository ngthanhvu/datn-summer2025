<template>
    <ForgotPasswordContent />
</template>

<script setup>
import ForgotPasswordContent from '~/components/auth/ForgotPasswordContent.vue';

useHead({
    title: 'Quên mật khẩu - DEVGANG',
    meta: [
        { name: 'description', content: 'Quên mật khẩu - DEVGANG' },
    ]
})
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@600&display=swap');

.tw-font-inter {
    font-family: 'Inter', sans-serif;
}
</style>