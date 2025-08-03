<template>
    <OrdersContent />
</template>

<script setup>
import OrdersContent from '../../../components/admin/orders/OrdersContent.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Quản lý đơn hàng | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Quản lý đơn hàng"
        }
    ]
})
</script>

<style lang="scss" scoped></style>