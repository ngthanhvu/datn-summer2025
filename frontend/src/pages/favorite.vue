<template>
    <WishlistContent />
</template>

<script setup>
import WishlistContent from '../components/wishlist/WishlistContent.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Sản phẩm yêu thích | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Sản phẩm yêu thích"
        }
    ]
})
</script>