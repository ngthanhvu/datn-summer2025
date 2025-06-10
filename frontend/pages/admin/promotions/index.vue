<template>
    <div class="promotions-page">
        <div class="page-header">
            <h1>Quản lý khuyến mãi</h1>
            <p class="text-gray-600">Quản lý chương trình khuyến mãi của bạn</p>
        </div>

        <PromotionTable />
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin'
})

import PromotionTable from '~/components/admin/promotion/PromotionTable.vue'
</script>

<style scoped>
.promotions-page {
    padding: 1.5rem;
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    font-size: 1.875rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.5rem;
}
</style>