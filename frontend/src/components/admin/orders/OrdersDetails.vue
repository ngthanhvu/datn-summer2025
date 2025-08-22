<template>
    <div class="order-info">
        <div v-if="loading" class="p-4 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent">
            </div>
        </div>

        <div v-else-if="error" class="p-4 text-center text-red-500">
            {{ error }}
        </div>

        <template v-else-if="currentOrder">
            <div class="space-y-8">
                <!-- Trạng thái đơn hàng -->
                <div class="border-b border-gray-300 pb-6">
                    <h4 class="font-semibold mb-4">Trạng thái đơn hàng</h4>
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col items-center relative">
                            <div
                                class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-sm mt-2">Đặt hàng</span>
                            <span class="text-xs text-gray-500">{{ formatDate(currentOrder.created_at) }}</span>
                        </div>
                        <div class="flex-1 h-0.5 bg-gray-200 mx-4"></div>
                        <div class="flex flex-col items-center relative">
                            <div :class="[
                                'w-10 h-10 rounded-full flex items-center justify-center text-white',
                                currentOrder.status === 'pending' ? 'bg-yellow-500' : 'bg-green-500'
                            ]">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <span class="text-sm mt-2">Xác nhận</span>
                            <span class="text-xs text-gray-500">
                                {{ currentOrder.status === 'pending' ? 'Đang chờ' : formatDate(currentOrder.updated_at)
                                }}</span>
                        </div>
                        <div class="flex-1 h-0.5 bg-gray-200 mx-4"></div>
                        <div class="flex flex-col items-center relative">
                            <div :class="[
                                'w-10 h-10 rounded-full flex items-center justify-center text-white',
                                ['shipping', 'completed'].includes(currentOrder.status) ? 'bg-green-500' : 'bg-gray-300'
                            ]">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                </svg>
                            </div>
                            <span class="text-sm mt-2">Giao hàng</span>
                            <span class="text-xs text-gray-500">{{ ['shipping',
                                'completed'].includes(currentOrder.status) ? 'Đang giao' : 'Chờ xử lý' }}</span>
                        </div>
                        <div class="flex-1 h-0.5 bg-gray-200 mx-4"></div>
                        <div v-if="currentOrder.status === 'completed'" class="flex flex-col items-center relative">
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
                                {{ formatDate(currentOrder.updated_at) }}
                            </span>
                        </div>
                        <div v-else-if="currentOrder.status === 'cancelled'"
                            class="flex flex-col items-center relative">
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
                                {{ formatDate(currentOrder.updated_at) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Hủy đơn hàng -->
                <div v-if="currentOrder.status === 'cancelled' && currentOrder.cancel_reason"
                    class="mt-4 p-4 bg-red-50 rounded-lg border-l-4 border-red-400">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center mr-2 mt-0.5">
                            <svg class="w-5 h-5 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <div>
                            <span class="font-medium text-red-600 text-sm md:text-base">Lý do hủy đơn hàng:
                            </span>
                            <span class="text-red-600 text-sm md:text-base">{{ currentOrder.cancel_reason
                            }}</span>
                        </div>
                    </div>
                </div>

                <!-- Hoàn đơn hàng -->
                <div v-if="currentOrder.return_status" class="mt-4 p-4 rounded-lg border-l-4 flex items-center"
                    :class="getReturnStatusContainerClass(currentOrder.return_status)">
                    <div
                        :class="['w-8', 'h-8', 'rounded-full', 'flex', 'items-center', 'justify-center', 'mr-3', getReturnStatusIconClass(currentOrder.return_status)]">
                        <svg :class="['w-5', 'h-5', getReturnStatusTextClass(currentOrder.return_status)]" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 15l-6-6 6-6M3 9h9a6 6 0 016 6v3" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-sm md:text-base"
                            :class="getReturnStatusTextClass(currentOrder.return_status)">
                            {{ getReturnStatusLabel(currentOrder.return_status) }}
                        </p>
                        <p v-if="currentOrder.return_status" class="text-sm mt-1"
                            :class="getReturnStatusTextClass(currentOrder.return_status)">
                            {{ getReturnDateLabel(currentOrder.return_status) }}: {{ currentOrder.return_requested_at ?
                                formatDate(currentOrder.return_requested_at) : formatDate(currentOrder.updated_at) }}
                        </p>
                        <p v-if="currentOrder.reject_reason" class="text-sm mt-1 text-red-600">
                            Lý do từ chối: {{ currentOrder.reject_reason }}
                        </p>
                    </div>
                </div>

                <!-- Thông tin khách hàng và thanh toán -->
                <div class="grid grid-cols-2 gap-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold mb-4">Thông tin khách hàng</h4>
                        <div class="space-y-2">
                            <p class="flex items-center">
                                <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ currentOrder.user?.username }}
                            </p>
                            <p class="flex items-center">
                                <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                {{ currentOrder.user?.email }}
                            </p>
                            <p class="flex items-center">
                                <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                {{ currentOrder.user?.phone }}
                            </p>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold mb-4">Thông tin giao hàng</h4>
                        <div class="space-y-2">
                            <p class="flex items-center">
                                <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ currentOrder.address?.full_name }}
                            </p>
                            <p class="flex items-center">
                                <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                {{ currentOrder.address?.phone }}
                            </p>
                            <p class="flex items-start">
                                {{ getFullAddress(currentOrder.address) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Thông tin thanh toán -->
                <div class="bg-gray-50 p-2 md:p-3 rounded-lg">
                    <h4 class="font-semibold mb-2 text-base">Thông tin thanh toán</h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <div class="space-y-1">
                            <p class="flex items-center text-sm">
                                <svg class="w-4 h-4 text-gray-500 mr-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                <span class="font-semibold mr-1">Phương thức:</span>
                                <span>{{ getPaymentMethod(currentOrder.payment_method) }}</span>
                            </p>
                            <p class="flex items-center text-sm">
                                <svg class="w-4 h-4 text-gray-500 mr-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-semibold mr-1">Trạng thái:</span>
                                <span :class="badgeClass(currentOrder.payment_status)" class="ml-1">
                                    {{ getPaymentStatus(currentOrder.payment_status) }}
                                </span>
                            </p>
                        </div>
                        <div class="space-y-2">
                            <p class="flex items-center text-sm">
                                <svg class="w-4 h-4 text-gray-500 mr-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <span class="font-semibold mr-1">Tổng sản phẩm:</span>
                                <span class="font-medium text-blue-600">{{ getTotalItems() }} sản phẩm</span>
                            </p>
                            <p class="flex items-center text-sm">
                                <svg class="w-4 h-4 text-gray-500 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1116.65 5.65a7.5 7.5 0 010 10.6z" />
                                </svg>
                                <span class="font-semibold mr-1">Mã tra cứu:</span>
                                <span class="font-medium text-blue-600">{{ currentOrder?.tracking_code || 'Chưa có mã'
                                }}</span>
                            </p>
                        </div>
                        <div class="space-y-1">
                            <p class="flex items-center text-sm">
                                <svg class="w-4 h-4 text-gray-500 mr-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="font-semibold mr-0">Ngày đặt:</span>
                                <span>{{ formatDate(currentOrder.created_at) }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Sản phẩm -->
                <div>
                    <h4 class="font-semibold mb-4">Sản phẩm ({{ getTotalItems() }} sản phẩm)</h4>

                    <div v-if="!getOrderDetails() || getOrderDetails().length === 0"
                        class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                        <p class="font-medium">Không có dữ liệu sản phẩm</p>
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="item in getOrderDetails()" :key="item.id"
                            class="flex items-center gap-4 p-4 bg-gray-50 rounded">
                            <img :src="item.variant?.product?.main_image?.image_path"
                                class="w-20 h-20 object-cover rounded" :alt="item.variant?.product?.name" />
                            <div class="flex-1">
                                <h5 class="font-medium">
                                    <template v-if="item.variant?.product?.slug">
                                        <a :href="`/products/${item.variant.product.slug}`" target="_blank"
                                            class="text-primary underline">
                                            {{ item.variant?.product?.name }}
                                        </a>
                                    </template>
                                    <template v-else>
                                        {{ item.variant?.product?.name }}
                                    </template>
                                </h5>
                                <div class="grid grid-cols-2 gap-2 mt-2">
                                    <p class="text-gray-600">Màu: {{ item.variant?.color }}</p>
                                    <p class="text-gray-600">Size: {{ item.variant?.size }}</p>
                                    <p class="text-gray-600">SKU: {{ item.variant?.sku }}</p>
                                    <p class="text-gray-600">Mã SP: {{ item.variant?.product?.id }}</p>
                                </div>
                            </div>
                            <div class="text-right min-w-[120px]">
                                <div class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-medium mb-2">
                                    Số lượng: {{ item.quantity }}
                                </div>
                                <p class="font-medium">{{ formatPrice(item.price) }}</p>
                                <p class="text-gray-600">Tổng: {{ formatPrice(item.total_price) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tổng tiền -->
                <div class="border-t border-gray-300 pt-4">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tổng tiền hàng</span>
                            <span>{{ formatPrice(currentOrder.total_price) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Phí vận chuyển</span>
                            <span>{{ formatPrice(calculateShipping(currentOrder)) }}</span>
                        </div>
                        <div v-if="currentOrder.discount_price > 0" class="flex justify-between">
                            <span class="text-gray-600">Giảm giá</span>
                            <span>-{{ formatPrice(currentOrder.discount_price) }}</span>
                        </div>
                        <div class="flex justify-between font-bold text-lg border-t border-gray-300 pt-2">
                            <span>Thành tiền</span>
                            <span>{{ formatPrice(currentOrder.final_price) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Cập nhật trạng thái và duyệt/từ chối hoàn hàng -->
                <div class="order-status">
                    <h3 class="mb-2 text-base font-semibold">Cập nhật trạng thái đơn hàng</h3>
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                        <!-- Nhóm cập nhật trạng thái -->
                        <div class="flex flex-wrap items-center gap-2">
                            <select v-model="selectedStatus" class="border border-gray-300 rounded px-3 py-1 text-sm">
                                <option v-for="opt in statusOptions" :value="opt.value" :key="opt.value">{{ opt.label }}
                                </option>
                            </select>
                            <select v-model="selectedPaymentStatus"
                                class="border border-gray-300 rounded px-3 py-1 text-sm">
                                <option v-for="opt in paymentStatusOptions" :value="opt.value" :key="opt.value">{{
                                    opt.label }}</option>
                            </select>
                            <button
                                @click="handleUpdateStatus({ status: selectedStatus, payment_status: selectedPaymentStatus })"
                                class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-primary-dark">Gửi</button>
                        </div>
                        <!-- Nhóm duyệt/từ chối hoàn hàng -->
                        <div v-if="currentOrder.return_status === 'requested'"
                            class="flex flex-col items-end gap-2 mt-2 md:mt-0">
                            <span class="text-xs text-gray-500 font-medium mb-1">Xử lý yêu cầu hoàn hàng:</span>
                            <div class="flex gap-2">
                                <button @click="handleApproveReturn"
                                    class="bg-green-600 text-white px-3 py-1.5 rounded text-sm hover:bg-green-700">
                                    Duyệt
                                </button>
                                <button @click="openRejectModal"
                                    class="bg-red-600 text-white px-3 py-1.5 rounded text-sm hover:bg-red-700">
                                    Từ chối
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

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
import { ref, onMounted, watch } from 'vue'
import { useOrder } from '../../../composable/useOrder'

const props = defineProps({
    orderId: {
        type: [String, Number],
        required: true
    }
})

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
    { value: 'shipping', label: 'Đang giao hàng' },
    { value: 'completed', label: 'Hoàn thành' },
    { value: 'cancelled', label: 'Đã hủy' }
]

const paymentStatusOptions = [
    { value: 'pending', label: 'Chờ thanh toán' },
    { value: 'paid', label: 'Đã thanh toán' },
    { value: 'failed', label: 'Thanh toán thất bại' },
    { value: 'refunded', label: 'Đã hoàn tiền' },
    { value: 'canceled', label: 'Đã huỷ' }
]

const selectedStatus = ref(currentOrder.value?.status || 'pending')
const selectedPaymentStatus = ref(currentOrder.value?.payment_status || 'pending')

watch(() => currentOrder.value?.status, (val) => {
    selectedStatus.value = val
})

watch(() => currentOrder.value?.payment_status, (val) => {
    selectedPaymentStatus.value = val
})

const showRejectModal = ref(false)
const rejectReason = ref('')

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

onMounted(async () => {
    await getOrder(props.orderId)
})

const handleUpdateStatus = async (data) => {
    try {
        await updateOrderStatus(props.orderId, data.status, data.payment_status)
        await getOrder(props.orderId)
    } catch (err) {
        console.error('Failed to update order status:', err)
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

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getFullAddress = (address) => {
    if (!address) return ''
    const parts = [
        address.street,
        address.hamlet,
        address.ward,
        address.district,
        address.province
    ].filter(Boolean)
    return parts.join(', ')
}

const badgeClass = (status) => {
    switch (status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs'
        case 'processing':
            return 'bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs'
        case 'shipping':
            return 'bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-xs'
        case 'completed':
            return 'bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs'
        case 'cancelled':
            return 'bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs'
        case 'paid':
            return 'bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs'
        case 'failed':
            return 'bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs'
        case 'canceled':
            return 'bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs'
        case 'refunded':
            return 'bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs'
        default:
            return 'bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs'
    }
}

const getTotalItems = () => {
    const details = currentOrder.value?.orderDetails || currentOrder.value?.order_details
    if (!details) return 0
    return details.reduce((total, item) => total + item.quantity, 0)
}

const getOrderDetails = () => {
    return currentOrder.value?.orderDetails || currentOrder.value?.order_details || []
}

const getReturnStatusContainerClass = (status) => {
    switch (status) {
        case 'requested': return 'bg-orange-50 border-orange-400'
        case 'approved': return 'bg-green-50 border-green-400'
        case 'rejected': return 'bg-red-50 border-red-400'
        default: return 'bg-gray-50 border-gray-400'
    }
}

const getReturnStatusIconClass = (status) => {
    switch (status) {
        case 'requested': return 'bg-orange-100'
        case 'approved': return 'bg-green-100'
        case 'rejected': return 'bg-red-100'
        default: return 'bg-gray-100'
    }
}

const getReturnStatusTextClass = (status) => {
    switch (status) {
        case 'requested': return 'text-orange-600'
        case 'approved': return 'text-green-600'
        case 'rejected': return 'text-red-600'
        default: return 'text-gray-600'
    }
}

const getReturnStatusLabel = (status) => {
    switch (status) {
        case 'requested': return 'Yêu cầu hoàn hàng'
        case 'approved': return 'Yêu cầu hoàn hàng đã được duyệt'
        case 'rejected': return 'Yêu cầu hoàn hàng đã bị từ chối'
        default: return 'Chưa yêu cầu hoàn hàng'
    }
}

const getReturnDateLabel = (status) => {
    switch (status) {
        case 'requested': return 'Ngày yêu cầu'
        case 'approved': return 'Ngày duyệt'
        case 'rejected': return 'Ngày từ chối'
        default: return 'Ngày cập nhật'
    }
}

const calculateShipping = (order) => {
    if (!order) return 0
    const subtotal = Number(order.total_price) || 0
    const total = Number(order.final_price) || 0
    const shipping = total - subtotal
    return shipping > 0 ? shipping : 0
}
</script>

<style scoped>
.order-info {
    padding: 1.5rem;
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
</style>