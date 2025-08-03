<template>
    <div class="p-6">
        <InventoryHistory />
    </div>
</template>

<script setup>
import InventoryHistory from '../../../components/admin/inventory/InventoryHistory.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Quản lý hoá đơn | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Quản lý hoá đơn"
        }
    ]
})
</script>