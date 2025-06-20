<template>
    <InventoryImport />
</template>

<script setup>
definePageMeta({
    layout: 'admin',
    middleware: 'auth'
})
useHead({
    title: 'Nhập kho'
})
import InventoryImport from '../../components/admin/inventory/InventoryImport.vue';
</script>

<style></style>