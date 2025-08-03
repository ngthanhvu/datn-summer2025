<template>
    <BrandsContent />
</template>

<script setup>
import BrandsContent from '../../../components/admin/brands/BrandsContent.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Quản lý thương hiệu | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Quản lý thương hiệu"
        }
    ]
})
</script>
