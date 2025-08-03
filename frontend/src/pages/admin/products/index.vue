<template>
    <ProductsContent />
</template>

<script setup>
import ProductsContent from '../../../components/admin/products/ProductsContent.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Quản lý sản phẩm | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Quản lý sản phẩm"
        }
    ]
})
</script>

<style lang="scss" scoped></style>