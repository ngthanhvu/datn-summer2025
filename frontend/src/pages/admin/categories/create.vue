<template>
    <CategoriesForm />
</template>

<script setup>
import CategoriesForm from '../../../components/admin/categories/CategoriesForm.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Tạo danh mục | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Tạo danh mục"
        }
    ]
})
</script>
