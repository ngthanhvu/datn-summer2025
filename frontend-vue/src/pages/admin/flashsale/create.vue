<template>
    <FlashsaleForm />
</template>

<script setup>
import FlashsaleForm from '../../../components/admin/flashsale/FlashsaleForm.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Tạo flash sale | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Tạo flash sale"
        }
    ]
})
</script>