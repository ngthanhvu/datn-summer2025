<template>
    <div class="promotions-page">
        <div class="page-header">
            <h1>Quản lý khuyến mãi</h1>
            <p class="text-gray-600">Quản lý chương trình khuyến mãi của bạn</p>
        </div>

        <Table :columns="columns" :data="promotions" :create-route="'/admin/promotions/create'"
            :edit-route="'/admin/promotions'" @delete="handleDelete" />
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin'
})

import { ref } from 'vue'
import Table from '~/components/admin/Table.vue'

// Table columns configuration
const columns = [
    { key: 'id', label: 'ID' },
    { key: 'name', label: 'Tên chương trình' },
    { key: 'code', label: 'Mã giảm giá' },
    { key: 'type', label: 'Loại' },
    { key: 'value', label: 'Giá trị' },
    { key: 'minSpend', label: 'Đơn tối thiểu', type: 'price' },
    { key: 'maxDiscount', label: 'Giảm tối đa', type: 'price' },
    { key: 'usageLimit', label: 'Giới hạn' },
    { key: 'usageCount', label: 'Đã dùng' },
    { key: 'startDate', label: 'Ngày bắt đầu' },
    { key: 'endDate', label: 'Ngày kết thúc' },
    { key: 'status', label: 'Trạng thái', type: 'status' }
]

// Mock data
const promotions = ref([
    {
        id: 1,
        name: 'Giảm giá mùa hè',
        code: 'SUMMER2024',
        type: 'percentage',
        value: '20%',
        minSpend: 1000000,
        maxDiscount: 500000,
        usageLimit: 100,
        usageCount: 45,
        startDate: '2024-06-01',
        endDate: '2024-08-31',
        status: true
    },
    {
        id: 2,
        name: 'Giảm giá sinh nhật',
        code: 'BIRTHDAY',
        type: 'fixed',
        value: '200.000đ',
        minSpend: 500000,
        maxDiscount: 200000,
        usageLimit: 0,
        usageCount: 156,
        startDate: '2024-01-01',
        endDate: '2024-12-31',
        status: true
    },
    {
        id: 3,
        name: 'Flash sale cuối tuần',
        code: 'WEEKEND',
        type: 'percentage',
        value: '15%',
        minSpend: 300000,
        maxDiscount: 300000,
        usageLimit: 50,
        usageCount: 50,
        startDate: '2024-03-01',
        endDate: '2024-03-31',
        status: false
    }
])

// Handlers
const handleDelete = (promotion) => {
    if (confirm('Bạn có chắc chắn muốn xóa chương trình khuyến mãi này?')) {
        const index = promotions.value.findIndex(p => p.id === promotion.id)
        if (index !== -1) {
            promotions.value.splice(index, 1)
        }
    }
}
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