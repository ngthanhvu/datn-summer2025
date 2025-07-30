<template>
    <CustomersContent />
</template>

<script setup>
import CustomersContent from '../../../components/admin/customers/CustomersContent.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Quản lý khách hàng | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Quản lý khách hàng"
        }
    ]
})
</script>

<style lang="scss" scoped></style>