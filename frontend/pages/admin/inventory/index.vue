<template>
    <InventoryTable />
</template>

<script setup>
definePageMeta({
    layout: 'admin',
    middleware: 'admin'
})
import InventoryTable from '../../components/admin/inventory/InventoryTable.vue'
</script>

<style></style>