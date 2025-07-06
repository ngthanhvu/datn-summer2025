<template>
    <InventoryTable />
</template>

<script setup>
definePageMeta({
    layout: 'admin',
    middleware: 'admin'
})
useHead({
    title: "Quản lý kho"
})
import InventoryTable from '../../components/admin/inventory/InventoryTable.vue'
</script>

<style></style>