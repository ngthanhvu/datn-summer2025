<template>
    <div class="orders-page">
        <div class="page-header">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1>Quản lý đơn hàng</h1>
                    <p class="text-gray-600">Quản lý và theo dõi đơn hàng</p>
                </div>
                <button @click="handleReload" :disabled="isLoading"
                    class="w-full sm:w-auto inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                    <i class="fas fa-sync-alt me-2" :class="{ 'animate-spin': isLoading }"></i>
                    {{ isLoading ? 'Đang tải...' : 'Tải lại' }}
                </button>
            </div>
        </div>

        <OrderStats :orders="orders" />
        <OrdersTable :orders="orders" :isLoading="isLoading" />
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { useOrderStore } from '../../../stores/orders'

import OrderStats from './OrderStats.vue'
import OrdersTable from './OrdersTable.vue'

const orderStore = useOrderStore()
const { orders, loading } = storeToRefs(orderStore)

const isLoading = ref(false)

const handleReload = async () => {
    loading.value = true
    await orderStore.fetchOrders()
    loading.value = false
}

onMounted(() => {
    orderStore.fetchOrders()
})
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

@media (max-width: 640px) {
    .page-header h1 {
        font-size: 1.5rem;
    }
}
</style>