<template>
    <BlogsContent />
</template>

<script setup>
import BlogsContent from '../components/site/BlogsContent.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Tin tức | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Tin tức"
        }
    ]
})
</script>

<style lang="scss" scoped></style>