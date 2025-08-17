<template>
    <BlogsEdit />
</template>

<script setup>
import BlogsEdit from '../../../components/admin/blogs/BlogsEdit.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Sửa bài viết | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Sửa bài viết"
        }
    ]
})
</script>