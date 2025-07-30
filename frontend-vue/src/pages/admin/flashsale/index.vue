<template>
    <FlashsaleContent />
</template>

<script setup>
import FlashsaleContent from '../../../components/admin/flashsale/FlashsaleContent.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Quản lý flash sale | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Quản lý flash sale"
        }
    ]
})
</script>