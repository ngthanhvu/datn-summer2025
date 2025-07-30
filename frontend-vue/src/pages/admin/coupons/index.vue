<template>
    <CouponContent />
</template>

<script setup>
import CouponContent from '../../../components/admin/coupons/CouponsContent.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Quản lý khuyến mãi | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Quản lý khuyến mãi"
        }
    ]
})
</script>