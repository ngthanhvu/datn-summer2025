<template>
    <BrandsEdit />
</template>

<script setup>
import BrandsEdit from '../../../components/admin/brands/BrandsEdit.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Sửa thương hiệu | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Sửa thương hiệu"
        }
    ]
})
</script>