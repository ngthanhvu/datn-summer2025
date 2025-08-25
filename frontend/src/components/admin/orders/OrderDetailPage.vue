<template>
    <div class="order-detail-page container mx-auto">
        <!-- Header Section -->
        <div class="px-4 sm:px-6 mt-3">
            <div class="flex flex-col gap-3 md:flex-row md:justify-between md:items-start">
                <div class="flex items-center space-x-4">
                    <button @click="goBack" class="text-gray-600 hover:text-gray-800 cursor-pointer">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </button>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Đơn hàng #{{ orderId }}</h1>
                        <div class="flex flex-wrap items-center gap-2 mt-1">
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                {{ orderStatusText }}
                            </span>
                            <span class="text-gray-500 text-sm">{{ formatDate(currentOrder?.created_at) }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col md:flex-row md:items-center md:space-x-3 gap-3">
                    <!-- Nhóm cập nhật trạng thái -->
                    <div class="flex flex-col md:flex-row md:items-center gap-2">
                        <select v-model="selectedStatus"
                            class="border border-gray-300 rounded px-4 py-2 text-sm w-full md:w-auto">
                            <option v-for="opt in statusOptions" :value="opt.value" :key="opt.value">{{ opt.label }}
                            </option>
                        </select>
                        <select v-model="selectedPaymentStatus"
                            class="border border-gray-300 rounded px-4 py-2 text-sm w-full md:w-auto">
                            <option v-for="opt in paymentStatusOptions" :value="opt.value" :key="opt.value">{{
                                opt.label }}</option>
                        </select>
                        <button
                            @click="handleUpdateStatus({ status: selectedStatus, payment_status: selectedPaymentStatus })"
                            class="bg-blue-600 text-white px-3 py-2 rounded text-sm hover:bg-blue-700 w-full md:w-auto">Gửi</button>
                    </div>
                    <!-- Thông báo yêu cầu hoàn hàng -->
                    <div v-if="currentOrder?.return_status === 'requested'"
                        class="flex items-center gap-2 px-3 py-2 bg-orange-100 text-orange-800 rounded-lg text-sm font-medium">
                        <i class="fas fa-exclamation-triangle"></i>
                        Có yêu cầu hoàn hàng cần xử lý
                    </div>
                    <button
                        class="flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 w-full md:w-auto">
                        <i class="fas fa-print mr-2"></i>
                        In
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center h-64">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent">
            </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="p-6 text-center text-red-500">
            {{ error }}
        </div>

        <!-- Main Content -->
        <div v-else-if="currentOrder" class="main-content p-4 sm:p-6 min-h-screen">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Details Card -->
                    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Chi tiết</h3>

                        </div>

                        <!-- Order Items -->
                        <div class="space-y-4">
                            <div v-for="item in getOrderDetails()" :key="item.id" class="flex items-center space-x-4">
                                <img :src="item.variant?.product?.main_image?.image_path"
                                    class="w-12 h-12 object-cover rounded" :alt="item.variant?.product?.name" />
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900 truncate">{{ item.variant?.product?.name }}
                                    </h4>
                                    <p class="text-sm text-gray-500 break-all">{{ item.variant?.sku }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-500">x{{ item.quantity }}</div>
                                    <div class="font-medium">{{ formatPrice(item.total_price) }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Price Summary -->
                        <div class="border-t border-gray-200 mt-4 pt-4 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tạm tính</span>
                                <span>{{ formatPrice(currentOrder?.total_price) }}</span>
                            </div>
                            <div class="flex justify-between text-red-600">
                                <span>Phí vận chuyển</span>
                                <span>-{{ formatPrice(calculateShipping(currentOrder)) }}</span>
                            </div>
                            <div v-if="currentOrder?.discount_price > 0" class="flex justify-between text-red-600">
                                <span>Giảm giá</span>
                                <span>-{{ formatPrice(currentOrder?.discount_price) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Thuế</span>
                                <span>{{ formatPrice(10) }}</span>
                            </div>
                            <div class="flex justify-between font-bold text-lg border-t border-gray-200 pt-2">
                                <span>Tổng cộng</span>
                                <span>{{ formatPrice(currentOrder?.final_price) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- History Card -->
                    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Trạng thái đơn hàng</h3>
                        <div class="flex items-center justify-between overflow-x-auto no-scrollbar">
                            <div class="flex flex-col items-center relative min-w-[72px]">
                                <div
                                    class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span class="text-sm mt-2">Đặt hàng</span>
                                <span class="text-xs text-gray-500">{{ formatDate(currentOrder?.created_at) }}</span>
                            </div>
                            <div class="flex-1 h-0.5 bg-gray-200 mx-2 sm:mx-4"></div>
                            <div class="flex flex-col items-center relative min-w-[72px]">
                                <div :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center text-white',
                                    currentOrder?.status === 'pending' ? 'bg-yellow-500' : 'bg-green-500'
                                ]">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <span class="text-sm mt-2">Xác nhận</span>
                                <span class="text-xs text-gray-500">
                                    {{ currentOrder?.status === 'pending' ? 'Đang chờ' :
                                        formatDate(currentOrder?.updated_at)
                                    }}</span>
                            </div>
                            <div class="flex-1 h-0.5 bg-gray-200 mx-2 sm:mx-4"></div>
                            <div class="flex flex-col items-center relative min-w-[72px]">
                                <div :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center text-white',
                                    ['shipping', 'completed'].includes(currentOrder?.status) ? 'bg-green-500' : 'bg-gray-300'
                                ]">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                    </svg>
                                </div>
                                <span class="text-sm mt-2">Giao hàng</span>
                                <span class="text-xs text-gray-500">{{ ['shipping',
                                    'completed'].includes(currentOrder?.status) ? 'Đang giao' : 'Chờ xử lý' }}</span>
                            </div>
                            <div class="flex-1 h-0.5 bg-gray-200 mx-2 sm:mx-4"></div>
                            <div v-if="currentOrder?.status === 'completed'"
                                class="flex flex-col items-center relative min-w-[72px]">
                                <div :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center text-white',
                                    'bg-green-500'
                                ]">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="text-sm mt-2">Hoàn thành</span>
                                <span class="text-xs text-gray-500">
                                    {{ formatDate(currentOrder?.updated_at) }}
                                </span>
                            </div>
                            <div v-else-if="currentOrder?.status === 'cancelled'"
                                class="flex flex-col items-center relative min-w-[72px]">
                                <div :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center text-white',
                                    'bg-red-500'
                                ]">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>
                                <span class="text-sm mt-2">Đã huỷ</span>
                                <span class="text-xs text-gray-500">
                                    {{ formatDate(currentOrder?.updated_at) }}
                                </span>
                            </div>
                            <div v-else-if="currentOrder?.status === 'refunded'"
                                class="flex flex-col items-center relative min-w-[72px]">
                                <div :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center text-white',
                                    'bg-blue-500'
                                ]">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                </div>
                                <span class="text-sm mt-2">Đã hoàn tiền</span>
                                <span class="text-xs text-gray-500">
                                    {{ formatDate(currentOrder?.updated_at) }}
                                </span>
                            </div>
                        </div>

                        <!-- Related Timestamps -->
                        <div class="mt-6 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Thời gian đặt hàng</span>
                                <span class="text-gray-900">{{ formatDate(currentOrder?.created_at) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Thời gian thanh toán</span>
                                <span class="text-gray-900">{{ formatDate(currentOrder?.created_at) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Thời gian giao cho đơn vị vận chuyển</span>
                                <span class="text-gray-900">{{ formatDate(currentOrder?.updated_at) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Thời gian hoàn thành</span>
                                <span class="text-gray-900">{{ formatDate(currentOrder?.updated_at) }}</span>
                            </div>
                            <div v-if="currentOrder?.status === 'refunded'" class="flex justify-between text-sm">
                                <span class="text-gray-600">Thời gian hoàn tiền</span>
                                <span class="text-gray-900">{{ formatDate(currentOrder?.updated_at) }}</span>
                            </div>
                        </div>

                        <button class="text-blue-600 text-sm mt-4 hover:text-blue-800">
                            Xem thêm >
                        </button>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Return Request Card - Đặt lên đầu để admin dễ thấy -->
                    <div v-if="currentOrder?.return_status"
                        class="bg-white rounded-lg shadow p-4 sm:p-6 border-l-4 border-orange-400">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-undo-alt text-orange-500 mr-2"></i>
                                Yêu cầu hoàn hàng
                            </h3>
                            <span :class="[
                                'px-3 py-1 rounded-full text-xs font-medium',
                                currentOrder.return_status === 'requested' ? 'bg-yellow-100 text-yellow-800' :
                                    currentOrder.return_status === 'approved' ? 'bg-green-100 text-green-800' :
                                        'bg-red-100 text-red-800'
                            ]">
                                {{
                                    currentOrder.return_status === 'requested' ? 'Chờ xử lý' :
                                        currentOrder.return_status === 'approved' ? 'Đã duyệt' :
                                            'Đã từ chối'
                                }}
                            </span>
                        </div>

                        <!-- Lý do hoàn hàng -->
                        <div v-if="currentOrder.return_reason" class="mb-4">
                            <p class="text-sm text-gray-600 mb-2">
                                <span class="font-medium text-gray-900">Lý do hoàn hàng:</span>
                            </p>
                            <div class="bg-orange-50 p-3 rounded-lg border border-orange-200">
                                <p class="text-orange-900 text-sm font-medium">{{ currentOrder.return_reason }}</p>
                            </div>
                        </div>

                        <!-- Lý do từ chối nếu có -->
                        <div v-if="currentOrder.reject_reason" class="mb-4">
                            <p class="text-sm text-gray-600 mb-2">
                                <span class="font-medium text-gray-900">Lý do từ chối:</span>
                            </p>
                            <div class="bg-red-50 p-3 rounded-lg border border-red-200">
                                <p class="text-red-700 text-sm font-medium">{{ currentOrder.reject_reason }}</p>
                            </div>
                        </div>

                        <!-- Thời gian yêu cầu -->
                        <div v-if="currentOrder.updated_at" class="text-xs text-gray-500 mb-4">
                            <span class="font-medium">Thời gian yêu cầu:</span> {{ formatDate(currentOrder.updated_at)
                            }}
                        </div>

                        <!-- Nút xử lý nếu đang chờ -->
                        <div v-if="currentOrder.return_status === 'requested'"
                            class="flex gap-2 pt-2 border-t border-gray-200">
                            <button @click="handleApproveReturn"
                                class="flex-1 bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700 transition-colors cursor-pointer">
                                <i class="fas fa-check mr-2"></i>Duyệt hoàn hàng
                            </button>
                            <button @click="openRejectModal"
                                class="flex-1 bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700 transition-colors cursor-pointer">
                                <i class="fas fa-times mr-2"></i>Từ chối
                            </button>
                        </div>
                    </div>

                    <!-- Customer Info Card -->
                    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Thông tin khách hàng</h3>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-gray-300 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-gray-600"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-medium text-gray-900 truncate">{{ currentOrder?.user?.username }}</h4>
                                <p class="text-sm text-gray-500 break-all">{{ currentOrder?.user?.email }}</p>
                                <p class="text-sm text-gray-500">IP: 192.158.1.38</p>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Card -->
                    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Vận chuyển</h3>

                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between gap-2">
                                <span class="text-gray-600">Mã theo dõi:</span>
                                <a href="#"
                                    class="text-blue-600 underline font-medium bg-gray-300 px-2 py-1 rounded truncate max-w-[60%] text-right">{{
                                        currentOrder?.tracking_code
                                        ||
                                        'SPX037739199373' }}</a>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Card -->
                    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Địa chỉ giao hàng</h3>

                        </div>
                        <div class="space-y-2">
                            <p class="text-gray-600"><span class=" font-medium text-gray-700">Địa chỉ:</span> <span
                                    class="break-words">{{
                                        getFullAddress(currentOrder?.address) }}</span></p>
                            <p class="text-gray-600"><span class=" font-medium text-gray-700">Số điện thoại:</span> {{
                                currentOrder?.address?.phone }}</p>
                        </div>
                    </div>

                    <!-- Payment Card -->
                    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Thanh toán</h3>

                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ getPaymentMethod(currentOrder?.payment_method)
                                }}</p>
                            </div>
                            <div class="w-8 h-8 bg-blue-100 rounded flex items-center justify-center">
                                <i class="fab fa-cc-mastercard text-blue-600"></i>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>

        <!-- Cập nhật trạng thái và duyệt/từ chối hoàn hàng -->

        <!-- Modal nhập lý do từ chối hoàn hàng -->
        <div v-if="showRejectModal"
            class="fixed inset-0 bg-gray-900/50 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-bold mb-4">Lý do từ chối hoàn hàng</h3>
                <textarea v-model="rejectReason" placeholder="Nhập lý do..."
                    class="w-full h-24 p-2 border rounded mb-4" />
                <div class="flex justify-end gap-2">
                    <button @click="showRejectModal = false" class="bg-gray-200 px-4 py-2 rounded">Hủy</button>
                    <button @click="handleRejectReturn"
                        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Xác
                        nhận</button>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useOrder } from '../../../composable/useOrder'

const props = defineProps({
    orderId: {
        type: [String, Number],
        required: true
    }
})

const router = useRouter()

const {
    currentOrder,
    loading,
    error,
    getOrder,
    updateOrderStatus,
    getOrderStatus,
    getPaymentStatus,
    getPaymentMethod,
    formatPrice,
    approveReturn,
    rejectReturn
} = useOrder()

const statusOptions = [
    { value: 'pending', label: 'Chờ xử lý' },
    { value: 'processing', label: 'Đang giao' },
    { value: 'shipping', label: 'Đã giao hàng' },
    { value: 'completed', label: 'Hoàn thành' },
    { value: 'cancelled', label: 'Đã hủy' },
]

const paymentStatusOptions = [
    { value: 'pending', label: 'Chờ thanh toán' },
    { value: 'paid', label: 'Đã thanh toán' },
    { value: 'failed', label: 'Thanh toán thất bại' },
    { value: 'refunded', label: 'Đã hoàn tiền' },
    { value: 'canceled', label: 'Đã huỷ' }
]

const selectedStatus = ref('pending')
const selectedPaymentStatus = ref('pending')
const showRejectModal = ref(false)
const rejectReason = ref('')

watch(() => currentOrder.value?.status, (val) => {
    if (val) selectedStatus.value = val
})

watch(() => currentOrder.value?.payment_status, (val) => {
    if (val) selectedPaymentStatus.value = val
})

const orderStatusText = computed(() => {
    if (!currentOrder.value?.status) return ''
    return getOrderStatus(currentOrder.value.status)
})

const goBack = () => {
    router.push('/admin/orders')
}

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getFullAddress = (address) => {
    if (!address) return '19034 Verna Unions Apt. 164 -Honolulu, RI / 87535'
    const parts = [
        address.street,
        address.hamlet,
        address.ward,
        address.district,
        address.province
    ].filter(Boolean)
    return parts.join(', ')
}

const getOrderDetails = () => {
    return currentOrder.value?.orderDetails || currentOrder.value?.order_details || []
}

const handleUpdateStatus = async (data) => {
    try {
        await updateOrderStatus(props.orderId, data.status, data.payment_status)
        await getOrder(props.orderId)
    } catch (err) {
        console.error('Failed to update order status:', err)
    }
}

const openRejectModal = () => {
    rejectReason.value = ''
    showRejectModal.value = true
}

const handleRejectReturn = async () => {
    if (!rejectReason.value.trim()) {
        const { $notyf } = useNuxtApp()
        if ($notyf) {
            $notyf.error('Vui lòng nhập lý do từ chối!')
        } else {
            alert('Vui lòng nhập lý do từ chối!')
        }
        return
    }
    try {
        await rejectReturn(currentOrder.value.id, rejectReason.value)
        showRejectModal.value = false
        await getOrder(currentOrder.value.id)
        const { $notyf } = useNuxtApp()
        if ($notyf) {
            $notyf.success('Từ chối hoàn hàng thành công!')
        } else {
            alert('Từ chối hoàn hàng thành công!')
        }
    } catch (err) {
        const { $notyf } = useNuxtApp()
        if ($notyf) {
            $notyf.error(err?.response?.data?.message || err.message || 'Từ chối hoàn hàng thất bại!')
        } else {
            alert(err?.response?.data?.message || err.message || 'Từ chối hoàn hàng thất bại!')
        }
    }
}

const handleApproveReturn = async () => {
    try {
        await approveReturn(currentOrder.value.id)
        await getOrder(currentOrder.value.id)
        const { $notyf } = useNuxtApp()
        if ($notyf) {
            $notyf.success('Duyệt hoàn hàng thành công!')
        } else {
            alert('Duyệt hoàn hàng thành công!')
        }
    } catch (err) {
        const { $notyf } = useNuxtApp()
        if ($notyf) {
            $notyf.error(err?.response?.data?.message || err.message || 'Duyệt hoàn hàng thất bại!')
        } else {
            alert(err?.response?.data?.message || err.message || 'Duyệt hoàn hàng thất bại!')
        }
    }
}

onMounted(async () => {
    await getOrder(props.orderId)
})

const calculateShipping = (order) => {
    if (!order) return 0
    const subtotal = Number(order.total_price) || 0
    const total = Number(order.final_price) || 0
    const shipping = total - subtotal
    return shipping > 0 ? shipping : 0
}
</script>

<style scoped>
.order-detail-page {
    min-height: 100vh;
}

.main-content {
    padding-top: 1.5rem;
}

.order-status {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
}

.order-status h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 1rem;
}

.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>