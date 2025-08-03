<template>
    <InventoryContent />
</template>

<script setup>
import InventoryContent from '../../../components/admin/inventory/InventoryContent.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Quản lý kho | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Quản lý kho"
        }
    ]
})
</script>