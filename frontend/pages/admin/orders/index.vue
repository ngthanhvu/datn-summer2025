<template>
    <div class="orders-page">
        <div class="page-header">
            <div class="tw-flex tw-justify-between tw-items-center">
                <div>
                    <h1>Quản lý đơn hàng</h1>
                    <p class="text-gray-600">Quản lý và theo dõi đơn hàng</p>
                </div>
                <button @click="handleReload" :disabled="isLoading"
                    class="tw-bg-blue-500 hover:tw-bg-blue-600 disabled:tw-bg-gray-400 tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-flex tw-items-center tw-gap-2 tw-transition-colors">
                    <i class="fas fa-sync-alt" :class="{ 'tw-animate-spin': isLoading }"></i>
                    {{ isLoading ? 'Đang tải...' : 'Làm mới' }}
                </button>
            </div>
        </div>

        <OrderStats :orders="orders" />
        <OrdersTable :orders="orders" :isLoading="isLoading" @view="handleView" />

        <Modal :show="showModal" :title="'Chi tiết đơn hàng #' + selectedOrder?.id" size="lg" @close="closeModal">
            <OrderDetails v-if="selectedOrder" :order="selectedOrder" @update-status="handleUpdateStatus" />
        </Modal>
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin'
})
useHead({
    title: "Quản lý đơn hàng"
})

import { ref, onMounted } from 'vue'
import Modal from '~/components/admin/Modal.vue'
import OrderStats from '~/components/admin/orders/OrderStats.vue'
import OrdersTable from '~/components/admin/orders/OrdersTable.vue'
import OrderDetails from '~/components/admin/orders/OrderDetails.vue'
import { useOrder } from '~/composables/useOrder'

const { orders, getAllOrders, updateOrderStatus } = useOrder()
const isLoading = ref(true)

onMounted(async () => {
    isLoading.value = true
    await getAllOrders()
    isLoading.value = false
})

const showModal = ref(false)
const selectedOrder = ref(null)

const handleView = (order) => {
    selectedOrder.value = order
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    selectedOrder.value = null
}

const handleUpdateStatus = async ({ id, status, payment_status }) => {
    isLoading.value = true
    await updateOrderStatus(id, status, payment_status)
    await getAllOrders() // reload lại danh sách sau khi cập nhật trạng thái
    isLoading.value = false
}

const handleReload = async () => {
    isLoading.value = true
    await getAllOrders()
    isLoading.value = false
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