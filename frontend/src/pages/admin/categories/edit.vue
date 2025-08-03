<template>
    <CategoriesEdit />
</template>

<script setup>
import CategoriesEdit from '../../../components/admin/categories/CategoriesEdit.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Sửa danh mục | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Sửa danh mục"
        }
    ]
})
</script>