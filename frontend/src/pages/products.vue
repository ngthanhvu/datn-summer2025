<template>
    <ProductsPage />
</template>

<script setup>
import ProductsPage from '../components/products/ProductsPage.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Sản phẩm | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Sản phẩm"
        }
    ]
})
</script>
