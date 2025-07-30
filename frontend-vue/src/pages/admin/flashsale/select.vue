<template>
    <FlashsaleProductSelect />
</template>

<script setup>
import FlashsaleProductSelect from '../../../components/admin/flashsale/FlashsaleProductSelect.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Chọn sản phẩm flash sale | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Chọn sản phẩm flash sale"
        }
    ]
})
</script>