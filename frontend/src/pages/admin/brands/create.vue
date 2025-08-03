<template>
    <BrandsForm />
</template>

<script setup>
import BrandsForm from '../../../components/admin/brands/BrandsForm.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Tạo thương hiệu | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Tạo thương hiệu"
        }
    ]
})
</script>

<style lang="scss" scoped></style>