<template>
    <InventoryImport />
</template>

<script setup>
import InventoryImport from '../../../components/admin/inventory/InventoryImport.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Nhập kho | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Nhập kho"
        }
    ]
})
</script>