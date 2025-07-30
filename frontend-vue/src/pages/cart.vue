<template>
    <CartContent />
</template>

<script setup>
import CartContent from '../components/cart/CartContent.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Giỏ hàng | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Giỏ hàng"
        }
    ]
})
</script>
