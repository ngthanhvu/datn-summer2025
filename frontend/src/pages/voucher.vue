<template>
    <VoucherContent />
</template>

<script setup>
import VoucherContent from '../components/vouchers/VouchersContent.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Kho voucher | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Kho voucher"
        }
    ]
})
</script>
