<template>
    <BlogForm />
</template>

<script setup>
definePageMeta({
    layout: 'admin',
    middleware: 'admin'
});
useHead({
    title: 'Thêm bài viết',
    meta: [
        {
            name: 'description',
            content: 'Thêm bài viết'
        }
    ]
})
import BlogForm from '../../components/admin/blogs/BlogForm.vue'
</script>

<style></style>