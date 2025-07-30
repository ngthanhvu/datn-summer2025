<template>
    <CommentsContent />
</template>

<script setup>
import CommentsContent from '../../../components/admin/comments/CommentsContent.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Quản lý đánh giá | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Quản lý đánh giá"
        }
    ]
})
</script>