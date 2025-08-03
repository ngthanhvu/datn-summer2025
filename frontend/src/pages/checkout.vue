<template>
    <CheckoutContent />
</template>

<script setup>
import CheckoutContent from '../components/checkout/CheckoutContent.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Thanh toán | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Thanh toán"
        }
    ]
})
</script>