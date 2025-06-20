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

import { ref, onMounted } from 'vue'
import Modal from '~/components/admin/Modal.vue'
import OrderStats from '~/components/admin/orders/OrderStats.vue'
import OrdersTable from '~/components/admin/orders/OrdersTable.vue'
import OrderDetails from '~/components/admin/orders/OrderDetails.vue'
import { useOrder } from '~/composables/useOrder'

// Sử dụng composable để lấy danh sách đơn hàng thực tế
const { orders, getOrders, updateOrderStatus } = useOrder()

onMounted(() => {
    getOrders()
})

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

const handleUpdateStatus = async ({ id, status, payment_status }) => {
    await updateOrderStatus(id, status, payment_status)
    await getOrders() // reload lại danh sách sau khi cập nhật trạng thái
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