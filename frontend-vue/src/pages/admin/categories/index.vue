<template>
    <CategoriesContent />
</template>

<script setup>
import CategoriesContent from '../../../components/admin/categories/CategoriesContent.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Quản lý danh mục | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Quản lý danh mục"
        }
    ]
})
</script>

<style lang="scss" scoped></style>