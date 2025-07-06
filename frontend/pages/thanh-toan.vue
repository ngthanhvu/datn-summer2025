<template>
    <CheckoutContent />
</template>

<script setup>
definePageMeta({
    middleware: "auth"
})
import CheckoutContent from '~/components/checkout/CheckoutContent.vue';

useHead({
    title: 'Thanh toán - DEVGANG',
    meta: [
        { name: 'description', content: 'Thanh toán - DEVGANG' },
    ],
})
</script>

<style scoped></style>