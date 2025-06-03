<template>
    <OrderTrackingContent />
</template>

<script setup>
import OrderTrackingContent from '~/components/order-tracking/OrderTrackingContent.vue';

definePageMeta({
    layout: 'default',
})

useHead({
    title: 'Theo dõi đơn hàng - DEVGANG',
    meta: [
        { name: 'description', content: 'Theo dõi đơn hàng - DEVGANG' },
    ],
})
</script>

<style scoped>
/* Add any component-specific styles here */
</style>