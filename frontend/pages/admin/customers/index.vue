<template>
    <div class="customers-page">
        <div class="page-header">
            <h1>Quản lý khách hàng</h1>
            <p class="text-gray-600">Quản lý danh sách khách hàng của bạn</p>
        </div>

        <Table :columns="columns" :data="customers" :create-route="'/admin/customers/create'"
            :edit-route="'/admin/customers'" @delete="handleDelete" />
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
    { key: 'avatar', label: 'Ảnh', type: 'image' },
    { key: 'name', label: 'Họ tên' },
    { key: 'email', label: 'Email' },
    { key: 'phone', label: 'Số điện thoại' },
    { key: 'totalOrders', label: 'Số đơn hàng' },
    { key: 'totalSpent', label: 'Tổng chi tiêu', type: 'price' },
    { key: 'status', label: 'Trạng thái', type: 'status' }
]

// Mock data
const customers = ref([
    {
        id: 1,
        avatar: 'https://via.placeholder.com/150',
        name: 'Nguyễn Văn A',
        email: 'nguyenvana@email.com',
        phone: '0123456789',
        totalOrders: 5,
        totalSpent: 15990000,
        status: true,
        address: '123 Đường ABC, Quận 1, TP.HCM'
    },
    {
        id: 2,
        avatar: 'https://via.placeholder.com/150',
        name: 'Trần Thị B',
        email: 'tranthib@email.com',
        phone: '0987654321',
        totalOrders: 3,
        totalSpent: 8990000,
        status: true,
        address: '456 Đường XYZ, Quận 2, TP.HCM'
    },
    {
        id: 3,
        avatar: 'https://via.placeholder.com/150',
        name: 'Lê Văn C',
        email: 'levanc@email.com',
        phone: '0369852147',
        totalOrders: 0,
        totalSpent: 0,
        status: false,
        address: '789 Đường DEF, Quận 3, TP.HCM'
    }
])

// Handlers
const handleDelete = (customer) => {
    if (confirm('Bạn có chắc chắn muốn xóa khách hàng này?')) {
        const index = customers.value.findIndex(c => c.id === customer.id)
        if (index !== -1) {
            customers.value.splice(index, 1)
        }
    }
}
</script>

<style scoped>
.customers-page {
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