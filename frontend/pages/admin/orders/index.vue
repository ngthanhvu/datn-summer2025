<template>
    <div class="orders-page">
        <div class="page-header">
            <h1>Quản lý đơn hàng</h1>
            <p class="text-gray-600">Quản lý và theo dõi đơn hàng</p>
        </div>

        <OrderStats :orders="orders" />
        <OrdersTable :orders="orders" @view="handleView" />

        <Modal :show="showModal" :title="'Chi tiết đơn hàng #' + selectedOrder?.id" size="lg" @close="closeModal">
            <OrderDetails v-if="selectedOrder" :order="selectedOrder" @update-status="handleUpdateStatus" />
        </Modal>
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin'
})

import { ref } from 'vue'
import Modal from '~/components/admin/Modal.vue'
import OrderStats from '~/components/admin/orders/OrderStats.vue'
import OrdersTable from '~/components/admin/orders/OrdersTable.vue'
import OrderDetails from '~/components/admin/orders/OrderDetails.vue'

// Mock data
const orders = ref([
    {
        id: 'DH001',
        customerName: 'Nguyễn Văn A',
        customerEmail: 'nguyenvana@email.com',
        customerPhone: '0123456789',
        shippingAddress: '123 Đường ABC, Quận 1, TP.HCM',
        orderDate: '2024-01-15',
        total: 31990000,
        status: 'pending',
        paymentMethod: 'COD',
        isPaid: false,
        items: [
            {
                id: 1,
                name: 'iPhone 13 Pro Max',
                quantity: 1,
                price: 30990000
            },
            {
                id: 2,
                name: 'Ốp lưng iPhone',
                quantity: 2,
                price: 500000
            }
        ]
    },
    {
        id: 'DH002',
        customerName: 'Trần Thị B',
        customerEmail: 'tranthib@email.com',
        customerPhone: '0987654321',
        shippingAddress: '456 Đường XYZ, Quận 2, TP.HCM',
        orderDate: '2024-01-16',
        total: 25990000,
        status: 'processing',
        paymentMethod: 'Banking',
        isPaid: true,
        items: [
            {
                id: 3,
                name: 'Samsung Galaxy S21',
                quantity: 1,
                price: 25990000
            }
        ]
    },
    {
        id: 'DH003',
        customerName: 'Lê Văn C',
        customerEmail: 'levanc@email.com',
        customerPhone: '0369852147',
        shippingAddress: '789 Đường DEF, Quận 3, TP.HCM',
        orderDate: '2024-01-17',
        total: 35990000,
        status: 'completed',
        paymentMethod: 'Banking',
        isPaid: true,
        items: [
            {
                id: 4,
                name: 'MacBook Pro M1',
                quantity: 1,
                price: 35990000
            }
        ]
    },
    {
        id: 'DH004',
        customerName: 'Phạm Thị D',
        customerEmail: 'phamthid@email.com',
        customerPhone: '0741852963',
        shippingAddress: '321 Đường GHI, Quận 4, TP.HCM',
        orderDate: '2024-01-18',
        total: 23990000,
        status: 'cancelled',
        paymentMethod: 'COD',
        isPaid: false,
        items: [
            {
                id: 5,
                name: 'iPad Pro 2021',
                quantity: 1,
                price: 23990000
            }
        ]
    }
])

// Modal state
const showModal = ref(false)
const selectedOrder = ref(null)

// Handlers
const handleView = (order) => {
    selectedOrder.value = order
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    selectedOrder.value = null
}

const handleUpdateStatus = ({ id, status }) => {
    const order = orders.value.find(o => o.id === id)
    if (order) {
        order.status = status
    }
}
</script>

<style scoped>
.orders-page {
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