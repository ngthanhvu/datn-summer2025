<template>
    <BlogsForm />
</template>

<script setup>
import BlogsForm from '../../../components/admin/blogs/BlogsForm.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Tạo bài viết | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Tạo bài viết"
        }
    ]
})
</script>