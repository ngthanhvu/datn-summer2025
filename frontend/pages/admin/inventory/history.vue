<template>
    <div class="tw-p-6">
        <StockMovementList />
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin',
    middleware: 'auth'
})
useHead({
    title: 'Hoá đơn'
})
import StockMovementList from '~/components/admin/inventory/StockMovementList.vue'
</script>
