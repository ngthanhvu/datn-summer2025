<template>
    <AbouteContent />
</template>

<script setup>
import AbouteContent from '../components/site/AbouteContent.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Giới thiệu | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Gioi thieu"
        }
    ]
})
</script>

<style lang="scss" scoped></style>