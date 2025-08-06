<template>
  <PagesContent />
</template>

<script setup>
import PagesContent from '../../../components/admin/newpage/PagesContent.vue'
import { useHead } from '@vueuse/head';
useHead({
    title: "Quản lý trang tĩnh | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Quản lý page"
        }
    ]
})
</script> 