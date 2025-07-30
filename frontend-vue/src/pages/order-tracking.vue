<template>
    <OrderTracking />
</template>

<script setup>
import OrderTracking from '../components/site/OrderTrackingContent.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Trạng thái đơn hàng | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Trạng thái đơn hàng"
        }
    ]
})
</script>

<style lang="scss" scoped></style>