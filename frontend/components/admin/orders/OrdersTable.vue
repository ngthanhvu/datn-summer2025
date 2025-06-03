<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow">
        <div class="tw-p-4 tw-border-b">
            <div class="tw-flex tw-justify-between tw-items-center">
                <div class="tw-relative">
                    <input type="text" v-model="searchQuery" placeholder="Tìm kiếm..."
                        class="tw-border tw-rounded tw-px-4 tw-py-2 tw-pl-10 tw-w-64">
                    <i
                        class="fas fa-search tw-absolute tw-left-3 tw-top-1/2 tw-transform -tw-translate-y-1/2 tw-text-gray-400"></i>
                </div>
                <select v-model="filterStatus" class="tw-border tw-rounded tw-px-4 tw-py-2">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending">Chờ xử lý</option>
                    <option value="processing">Đang giao</option>
                    <option value="completed">Hoàn thành</option>
                    <option value="cancelled">Đã hủy</option>
                </select>
            </div>
        </div>

        <div class="tw-overflow-x-auto">
            <table class="tw-w-full">
                <thead>
                    <tr class="tw-bg-gray-50">
                        <th
                            class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Mã đơn
                        </th>
                        <th
                            class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Khách hàng
                        </th>
                        <th
                            class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Ngày đặt
                        </th>
                        <th
                            class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Tổng tiền
                        </th>
                        <th
                            class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Trạng thái
                        </th>
                        <th
                            class="tw-px-4 tw-py-3 tw-text-right tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Thao tác
                        </th>
                    </tr>
                </thead>
                <tbody class="tw-divide-y tw-divide-gray-200">
                    <tr v-for="order in filteredOrders" :key="order.id" class="tw-hover:tw-bg-gray-50">
                        <td class="tw-px-4 tw-py-3 tw-text-sm tw-text-gray-900">{{ order.id }}</td>
                        <td class="tw-px-4 tw-py-3 tw-text-sm tw-text-gray-900">{{ order.customerName }}</td>
                        <td class="tw-px-4 tw-py-3 tw-text-sm tw-text-gray-900">{{ order.orderDate }}</td>
                        <td class="tw-px-4 tw-py-3 tw-text-sm tw-text-gray-900">{{ formatPrice(order.total) }}</td>
                        <td class="tw-px-4 tw-py-3">
                            <span :class="[
                                'tw-px-2 tw-py-1 tw-rounded-full tw-text-xs',
                                {
                                    'tw-bg-yellow-100 tw-text-yellow-700': order.status === 'pending',
                                    'tw-bg-blue-100 tw-text-blue-700': order.status === 'processing',
                                    'tw-bg-green-100 tw-text-green-700': order.status === 'completed',
                                    'tw-bg-red-100 tw-text-red-700': order.status === 'cancelled'
                                }
                            ]">
                                {{ getStatusText(order.status) }}
                            </span>
                        </td>
                        <td class="tw-px-4 tw-py-3 tw-text-right tw-text-sm tw-font-medium">
                            <button @click="handleView(order)" class="tw-text-primary tw-hover:tw-text-primary-dark">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
    orders: {
        type: Array,
        required: true
    }
})

const emit = defineEmits(['view'])

const searchQuery = ref('')
const filterStatus = ref('')

const filteredOrders = computed(() => {
    return props.orders.filter(order => {
        const matchesSearch = order.id.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            order.customerName.toLowerCase().includes(searchQuery.value.toLowerCase())
        const matchesStatus = !filterStatus.value || order.status === filterStatus.value
        return matchesSearch && matchesStatus
    })
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}

const getStatusText = (status) => {
    switch (status) {
        case 'pending': return 'Chờ xử lý'
        case 'processing': return 'Đang giao'
        case 'completed': return 'Hoàn thành'
        case 'cancelled': return 'Đã hủy'
        default: return status
    }
}

const handleView = (order) => {
    emit('view', order)
}
</script>

<style scoped>
.tw-text-primary {
    color: #3bb77e;
}

.tw-text-primary-dark {
    color: #2d9d6a;
}
</style>