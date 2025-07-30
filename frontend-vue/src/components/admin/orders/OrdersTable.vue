<template>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="pb-4">
            <div class="flex justify-between items-center">
                <div class="relative">
                    <input type="text" v-model="searchQuery" placeholder="Tìm kiếm..."
                        class="border border-gray-300 rounded px-4 py-2 pl-10 w-64" />
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
                <select v-model="filterStatus" class="border border-gray-300 rounded px-4 py-2">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending">Chờ xử lý</option>
                    <option value="processing">Đang giao</option>
                    <option value="completed">Hoàn thành</option>
                    <option value="cancelled">Đã hủy</option>
                </select>
            </div>
        </div>

        <div v-if="error" class="p-4 text-center text-red-500">
            {{ error }}
        </div>

        <div v-else class="overflow-x-auto overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mã đơn</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Khách hàng</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ngày đặt</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tổng tiền</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mã tra cứu</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Thanh toán</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-if="loading" v-for="n in 8" :key="'skeleton-' + n">
                        <td v-for="i in 8" :key="i" class="px-4 py-3">
                            <div class="skeleton-loader"></div>
                        </td>
                    </tr>

                    <tr v-else v-for="order in filteredOrders" :key="order.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">#{{ order.id }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <div>{{ order.user?.username }}</div>
                            <div class="text-xs text-gray-500">{{ order.user?.email }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ new Date(order.created_at).toLocaleDateString('vi-VN') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ formatPrice(order.final_price) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">
                                {{ order.tracking_code || '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span :class="[
                                'px-2 py-1 rounded-full text-xs',
                                {
                                    'bg-yellow-100 text-yellow-700': order.status === 'pending',
                                    'bg-blue-100 text-blue-700': order.status === 'processing',
                                    'bg-purple-100 text-purple-700': order.status === 'shipping',
                                    'bg-green-100 text-green-700': order.status === 'completed',
                                    'bg-red-100 text-red-700': order.status === 'cancelled'
                                }
                            ]">
                                {{ getOrderStatus(order.status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span :class="[
                                'px-2 py-1 rounded-full text-xs',
                                {
                                    'bg-yellow-100 text-yellow-700': order.payment_status === 'pending',
                                    'bg-green-100 text-green-700': order.payment_status === 'paid',
                                    'bg-red-100 text-red-700':
                                        order.payment_status === 'failed' || order.payment_status === 'canceled',
                                    'bg-blue-100 text-blue-700': order.payment_status === 'refunded'
                                }
                            ]">
                                {{ getPaymentStatus(order.payment_status) }}
                            </span>
                            <div class="text-xs text-gray-500 mt-1">{{ getPaymentMethod(order.payment_method) }}</div>
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-medium">
                            <button @click="handleView(order)" class="text-primary hover:text-primary-dark">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>

                    <tr v-if="!loading && filteredOrders.length === 0">
                        <td colspan="8" class="px-3 py-2 text-center text-gray-500">Không có dữ liệu</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useOrderStore } from '../../../stores/orders'

const orderStore = useOrderStore()
const { orders, loading, error } = storeToRefs(orderStore)

const searchQuery = ref('')
const filterStatus = ref('')

const emit = defineEmits(['view']) // ✅ thêm dòng này

const handleView = (order) => {
    emit('view', order) // ✅ emit ra parent
}


onMounted(() => {
    orderStore.fetchOrders() // gọi API và gán orders.value
})

const filteredOrders = computed(() => {
    const orderList = Array.isArray(orders.value) ? orders.value : []
    return orderList.filter((order) => {
        const matchesSearch =
            order.id.toString().includes(searchQuery.value) ||
            order.user?.username?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            order.user?.email?.toLowerCase().includes(searchQuery.value.toLowerCase())

        const matchesStatus = !filterStatus.value || order.status === filterStatus.value
        return matchesSearch && matchesStatus
    })
})

// const handleView = (order) => {
//     selectedOrder.value = order
//     showModal.value = true
// }

// const closeModal = () => {
//     selectedOrder.value = null
//     showModal.value = false
// }

const getOrderStatus = (status) => {
    const map = {
        pending: 'Chờ xử lý',
        processing: 'Đang giao',
        shipping: 'Đang vận chuyển',
        completed: 'Hoàn thành',
        cancelled: 'Đã hủy'
    }
    return map[status] || status
}

const getPaymentStatus = (status) => {
    const map = {
        pending: 'Chờ thanh toán',
        paid: 'Đã thanh toán',
        failed: 'Thanh toán thất bại',
        canceled: 'Đã hủy thanh toán',
        refunded: 'Đã hoàn tiền'
    }
    return map[status] || status
}

const getPaymentMethod = (method) => {
    const map = { cod: 'Thanh toán khi nhận hàng', vnpay: 'VNPay', momo: 'MoMo', paypal: 'PayPal' }
    return map[method] || method
}

const formatPrice = (price) =>
    new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
</script>

<style scoped>
.text-primary {
    color: #3bb77e;
}

.text-primary-dark {
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
