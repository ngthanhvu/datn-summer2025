<template>
    <BlogsContent />
</template>

<script setup>
import BlogsContent from '../../../components/admin/blogs/BlogsContent.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Quản lý bài viết | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Quản lý bài viết"
        }
    ]
})
</script>