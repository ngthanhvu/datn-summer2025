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

        <div v-if="error" class="tw-p-4 tw-text-center tw-text-red-500">
            {{ error }}
        </div>

        <div v-else class="tw-overflow-x-auto">
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
                            Mã tra cứu
                        </th>
                        <th
                            class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Trạng thái
                        </th>
                        <th
                            class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Thanh toán
                        </th>
                        <th
                            class="tw-px-4 tw-py-3 tw-text-center tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Thao tác
                        </th>
                    </tr>
                </thead>
                <tbody class="tw-divide-y tw-divide-gray-200">
                    <tr v-if="isLoading" v-for="n in 8" :key="'skeleton-' + n">
                        <td v-for="i in 8" :key="i" class="tw-px-4 tw-py-3">
                            <div class="skeleton-loader"></div>
                        </td>
                    </tr>
                    <tr v-else v-for="order in filteredOrders" :key="order.id" class="tw-hover:tw-bg-gray-50">
                        <td class="tw-px-4 tw-py-3 tw-text-sm tw-text-gray-900">#{{ order.id }}</td>
                        <td class="tw-px-4 tw-py-3 tw-text-sm tw-text-gray-900">
                            <div>{{ order.user?.username }}</div>
                            <div class="tw-text-xs tw-text-gray-500">{{ order.user?.email }}</div>
                        </td>
                        <td class="tw-px-4 tw-py-3 tw-text-sm tw-text-gray-900">
                            {{ new Date(order.created_at).toLocaleDateString('vi-VN') }}
                        </td>
                        <td class="tw-px-4 tw-py-3 tw-text-sm tw-text-gray-900">
                            {{ formatPrice(order.final_price) }}
                        </td>
                        <td class="tw-px-4 tw-py-3 tw-text-sm tw-text-gray-900">
                            <span class="tw-font-mono tw-text-xs tw-bg-gray-100 tw-px-2 tw-py-1 tw-rounded">{{
                                order.tracking_code || '-' }}</span>
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            <span :class="[
                                'tw-px-2 tw-py-1 tw-rounded-full tw-text-xs',
                                {
                                    'tw-bg-yellow-100 tw-text-yellow-700': order.status === 'pending',
                                    'tw-bg-blue-100 tw-text-blue-700': order.status === 'processing',
                                    'tw-bg-purple-100 tw-text-purple-700': order.status === 'shipping',
                                    'tw-bg-green-100 tw-text-green-700': order.status === 'completed',
                                    'tw-bg-red-100 tw-text-red-700': order.status === 'cancelled'
                                }
                            ]">
                                {{ getOrderStatus(order.status) }}
                            </span>
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            <span :class="[
                                'tw-px-2 tw-py-1 tw-rounded-full tw-text-xs',
                                {
                                    'tw-bg-yellow-100 tw-text-yellow-700': order.payment_status === 'pending',
                                    'tw-bg-green-100 tw-text-green-700': order.payment_status === 'paid',
                                    'tw-bg-red-100 tw-text-red-700': order.payment_status === 'failed' || order.payment_status === 'canceled',
                                    'tw-bg-blue-100 tw-text-blue-700': order.payment_status === 'refunded'
                                }
                            ]">
                                {{ getPaymentStatus(order.payment_status) }}
                            </span>
                            <div class="tw-text-xs tw-text-gray-500 mt-1">
                                {{ getPaymentMethod(order.payment_method) }}
                            </div>
                        </td>
                        <td class="tw-px-4 tw-py-3 tw-text-center tw-text-sm tw-font-medium">
                            <button @click="handleView(order)" class="tw-text-primary tw-hover:tw-text-primary-dark">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!isLoading && filteredOrders.length === 0">
                        <td colspan="8" class="tw-px-3 tw-py-2 tw-text-center tw-text-gray-500">
                            Không có dữ liệu
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Order Details Modal -->
        <div v-if="showModal"
            class="tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-items-center tw-justify-center tw-z-50">
            <div class="tw-bg-white tw-rounded-lg tw-w-full tw-max-w-4xl tw-max-h-[90vh] tw-overflow-y-auto">
                <div class="tw-p-4 tw-border-b tw-flex tw-justify-between tw-items-center">
                    <h3 class="tw-text-lg tw-font-medium">Chi tiết đơn hàng #{{ selectedOrder?.id }}</h3>
                    <button @click="closeModal" class="tw-text-gray-400 hover:tw-text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="tw-p-4">
                    <OrderDetails v-if="selectedOrder" :order-id="selectedOrder.id" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useOrder } from '~/composables/useOrder'
import OrderDetails from './OrderDetails.vue'

const {
    orders,
    loading,
    error,
    getOrders,
    getOrderStatus,
    getPaymentStatus,
    getPaymentMethod,
    formatPrice
} = useOrder()

const emit = defineEmits(['view'])

const searchQuery = ref('')
const filterStatus = ref('')
const showModal = ref(false)
const selectedOrder = ref(null)

const props = defineProps({
    orders: {
        type: [Array, Object],
        required: true
    },
    isLoading: {
        type: Boolean,
        default: false
    }
})

onMounted(async () => {
    await getOrders()
})

const filteredOrders = computed(() => {
    return orders.value.data?.filter(order => {
        const matchesSearch = order.id.toString().includes(searchQuery.value) ||
            order.user?.username?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            order.user?.email?.toLowerCase().includes(searchQuery.value.toLowerCase())
        const matchesStatus = !filterStatus.value || order.status === filterStatus.value
        return matchesSearch && matchesStatus
    }) || []
})

const handleView = (order) => {
    selectedOrder.value = order
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    selectedOrder.value = null
}
</script>

<style scoped>
.tw-text-primary {
    color: #3bb77e;
}

.tw-text-primary-dark {
    color: #2d9d6a;
}

.skeleton-loader {
    height: 20px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 37%, #f0f0f0 63%);
    border-radius: 4px;
    animation: skeleton-loading 2.2s infinite;
}

@keyframes skeleton-loading {
    0% {
        background-position: -200px 0;
    }

    100% {
        background-position: calc(200px + 100%) 0;
    }
}
</style>