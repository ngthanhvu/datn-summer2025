<template>
  <BlogForm />
</template>

<script setup>
definePageMeta({
  layout: 'admin',
  middleware: 'admin'
})
useHead({
  title: 'Chỉnh sửa bài viết',
  meta: [
    {
      name: 'description',
      content: 'Chỉnh sửa bài viết'
    }
  ]
})
import BlogForm from '@/components/admin/blogs/BlogForm.vue'
</script>