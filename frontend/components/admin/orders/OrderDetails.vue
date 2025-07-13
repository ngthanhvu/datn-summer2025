<template>
    <div class="order-info">
        <div v-if="loading" class="tw-p-4 tw-text-center">
            <div
                class="tw-inline-block tw-animate-spin tw-rounded-full tw-h-8 tw-w-8 tw-border-4 tw-border-primary tw-border-t-transparent">
            </div>
        </div>

        <div v-else-if="error" class="tw-p-4 tw-text-center tw-text-red-500">
            {{ error }}
        </div>

        <template v-else-if="currentOrder">
            <div class="tw-space-y-8">
                <!-- Trạng thái đơn hàng -->
                <div class="tw-border-b tw-pb-6">
                    <h4 class="tw-font-semibold tw-mb-4">Trạng thái đơn hàng</h4>
                    <div class="tw-flex tw-items-center tw-justify-between">
                        <div class="tw-flex tw-flex-col tw-items-center tw-relative">
                            <div
                                class="tw-w-10 tw-h-10 tw-rounded-full tw-bg-green-500 tw-flex tw-items-center tw-justify-center tw-text-white">
                                <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="tw-text-sm tw-mt-2">Đặt hàng</span>
                            <span class="tw-text-xs tw-text-gray-500">{{ formatDate(currentOrder.created_at) }}</span>
                        </div>
                        <div class="tw-flex-1 tw-h-0.5 tw-bg-gray-200 tw-mx-4"></div>
                        <div class="tw-flex tw-flex-col tw-items-center tw-relative">
                            <div :class="[
                                'tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-white',
                                currentOrder.status === 'pending' ? 'tw-bg-yellow-500' : 'tw-bg-green-500'
                            ]">
                                <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <span class="tw-text-sm tw-mt-2">Xác nhận</span>
                            <span class="tw-text-xs tw-text-gray-500">
                                {{ currentOrder.status === 'pending' ? 'Đang chờ' : formatDate(currentOrder.updated_at)
                                }}</span>
                        </div>
                        <div class="tw-flex-1 tw-h-0.5 tw-bg-gray-200 tw-mx-4"></div>
                        <div class="tw-flex tw-flex-col tw-items-center tw-relative">
                            <div :class="[
                                'tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-white',
                                ['shipping', 'completed'].includes(currentOrder.status) ? 'tw-bg-green-500' : 'tw-bg-gray-300'
                            ]">
                                <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                </svg>
                            </div>
                            <span class="tw-text-sm tw-mt-2">Giao hàng</span>
                            <span class="tw-text-xs tw-text-gray-500">{{ ['shipping',
                                'completed'].includes(currentOrder.status) ? 'Đang giao' : 'Chờ xử lý' }}</span>
                        </div>
                        <div class="tw-flex-1 tw-h-0.5 tw-bg-gray-200 tw-mx-4"></div>
                        <div v-if="currentOrder.status === 'completed'"
                            class="tw-flex tw-flex-col tw-items-center tw-relative">
                            <div :class="[
                                'tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-white',
                                'tw-bg-green-500'
                            ]">
                                <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="tw-text-sm tw-mt-2">Hoàn thành</span>
                            <span class="tw-text-xs tw-text-gray-500">
                                {{ formatDate(currentOrder.updated_at) }}
                            </span>
                        </div>
                        <div v-else-if="currentOrder.status === 'cancelled'"
                            class="tw-flex tw-flex-col tw-items-center tw-relative">
                            <div :class="[
                                'tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-white',
                                'tw-bg-red-500'
                            ]">
                                <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <span class="tw-text-sm tw-mt-2">Đã huỷ</span>
                            <span class="tw-text-xs tw-text-gray-500">
                                {{ formatDate(currentOrder.updated_at) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Hủy đơn hàng -->
                <div v-if="currentOrder.status === 'cancelled' && currentOrder.cancel_reason"
                    class="tw-mt-4 tw-p-4 tw-bg-red-50 tw-rounded-lg tw-border-l-4 tw-border-red-400">
                    <div class="tw-flex tw-items-center">
                        <div
                            class="tw-w-8 tw-h-8 tw-rounded-full tw-bg-red-100 tw-flex tw-items-center tw-justify-center tw-mr-2 tw-mt-0.5">
                            <svg class="tw-w-5 tw-h-5 tw-text-red-700" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <div>
                            <span class="tw-font-medium tw-text-red-600 tw-text-sm md:tw-text-base">Lý do hủy đơn hàng:
                            </span>
                            <span class="tw-text-red-600 tw-text-sm md:tw-text-base">{{ currentOrder.cancel_reason
                            }}</span>
                        </div>
                    </div>
                </div>

                <!-- Hoàn đơn hàng -->
                <div v-if="currentOrder.return_status"
                    class="tw-mt-4 tw-p-4 tw-rounded-lg tw-border-l-4 tw-flex tw-items-center"
                    :class="getReturnStatusContainerClass(currentOrder.return_status)">
                    <div
                        :class="['tw-w-8', 'tw-h-8', 'tw-rounded-full', 'tw-flex', 'tw-items-center', 'tw-justify-center', 'tw-mr-3', getReturnStatusIconClass(currentOrder.return_status)]">
                        <svg :class="['tw-w-5', 'tw-h-5', getReturnStatusTextClass(currentOrder.return_status)]"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 15l-6-6 6-6M3 9h9a6 6 0 016 6v3" />
                        </svg>
                    </div>
                    <div>
                        <p class="tw-font-medium tw-text-sm md:tw-text-base"
                            :class="getReturnStatusTextClass(currentOrder.return_status)">
                            {{ getReturnStatusLabel(currentOrder.return_status) }}
                        </p>
                        <p v-if="currentOrder.return_status" class="tw-text-sm tw-mt-1"
                            :class="getReturnStatusTextClass(currentOrder.return_status)">
                            {{ getReturnDateLabel(currentOrder.return_status) }}: {{ currentOrder.return_requested_at ?
                                formatDate(currentOrder.return_requested_at) : formatDate(currentOrder.updated_at) }}
                        </p>
                        <p v-if="currentOrder.reject_reason" class="tw-text-sm tw-mt-1 tw-text-red-600">
                            Lý do từ chối: {{ currentOrder.reject_reason }}
                        </p>
                    </div>
                </div>

                <!-- Thông tin khách hàng và thanh toán -->
                <div class="tw-grid tw-grid-cols-2 tw-gap-6">
                    <div class="tw-bg-gray-50 tw-p-4 tw-rounded-lg">
                        <h4 class="tw-font-semibold tw-mb-4">Thông tin khách hàng</h4>
                        <div class="tw-space-y-2">
                            <p class="tw-flex tw-items-center">
                                <svg class="tw-w-5 tw-h-5 tw-text-gray-500 tw-mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ currentOrder.user?.username }}
                            </p>
                            <p class="tw-flex tw-items-center">
                                <svg class="tw-w-5 tw-h-5 tw-text-gray-500 tw-mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                {{ currentOrder.user?.email }}
                            </p>
                            <p class="tw-flex tw-items-center">
                                <svg class="tw-w-5 tw-h-5 tw-text-gray-500 tw-mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                {{ currentOrder.user?.phone }}
                            </p>
                        </div>
                    </div>

                    <div class="tw-bg-gray-50 tw-p-4 tw-rounded-lg">
                        <h4 class="tw-font-semibold tw-mb-4">Thông tin giao hàng</h4>
                        <div class="tw-space-y-2">
                            <p class="tw-flex tw-items-center">
                                <svg class="tw-w-5 tw-h-5 tw-text-gray-500 tw-mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ currentOrder.address?.full_name }}
                            </p>
                            <p class="tw-flex tw-items-center">
                                <svg class="tw-w-5 tw-h-5 tw-text-gray-500 tw-mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                {{ currentOrder.address?.phone }}
                            </p>
                            <p class="tw-flex tw-items-start">
                                {{ getFullAddress(currentOrder.address) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Thông tin thanh toán -->
                <div class="tw-bg-gray-50 tw-p-4 tw-rounded-lg">
                    <h4 class="tw-font-semibold tw-mb-4">Thông tin thanh toán</h4>
                    <div class="tw-grid tw-grid-cols-3 tw-gap-4">
                        <div class="tw-space-y-2">
                            <p class="tw-flex tw-items-center">
                                <svg class="tw-w-5 tw-h-5 tw-text-gray-500 tw-mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                Phương thức: {{ getPaymentMethod(currentOrder.payment_method) }}
                            </p>
                            <p class="tw-flex tw-items-center">
                                <svg class="tw-w-5 tw-h-5 tw-text-gray-500 tw-mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Trạng thái:
                                <span :class="badgeClass(currentOrder.payment_status)" class="tw-ml-2">
                                    {{ getPaymentStatus(currentOrder.payment_status) }}
                                </span>
                            </p>
                        </div>
                        <div class="tw-space-y-2">
                            <p class="tw-flex tw-items-center">
                                <svg class="tw-w-5 tw-h-5 tw-text-gray-500 tw-mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                Tổng sản phẩm:
                                <span class="tw-ml-2 tw-font-medium tw-text-blue-600">{{ getTotalItems() }} sản
                                    phẩm</span>
                            </p>
                            <p class="tw-flex tw-items-center">
                                Mã tra cứu:
                                <span class="tw-ml-2 tw-font-medium tw-text-blue-600">{{ currentOrder?.tracking_code ||
                                    'Chưa có mã' }}</span>
                            </p>
                        </div>
                        <div class="tw-space-y-2">
                            <p class="tw-flex tw-items-center">
                                <svg class="tw-w-5 tw-h-5 tw-text-gray-500 tw-mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Ngày đặt: {{ formatDate(currentOrder.created_at) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Sản phẩm -->
                <div>
                    <h4 class="tw-font-semibold tw-mb-4">Sản phẩm ({{ getTotalItems() }} sản phẩm)</h4>

                    <div v-if="!getOrderDetails() || getOrderDetails().length === 0"
                        class="tw-bg-yellow-100 tw-border tw-border-yellow-400 tw-text-yellow-700 tw-px-4 tw-py-3 tw-rounded tw-mb-4">
                        <p class="tw-font-medium">Không có dữ liệu sản phẩm</p>
                    </div>

                    <div v-else class="tw-space-y-4">
                        <div v-for="item in getOrderDetails()" :key="item.id"
                            class="tw-flex tw-items-center tw-gap-4 tw-p-4 tw-bg-gray-50 tw-rounded">
                            <img :src="item.variant?.product?.main_image?.image_path"
                                class="tw-w-20 tw-h-20 tw-object-cover tw-rounded" :alt="item.variant?.product?.name" />
                            <div class="tw-flex-1">
                                <h5 class="tw-font-medium">
                                    <template v-if="item.variant?.product?.slug">
                                        <a :href="`/products/${item.variant.product.slug}`" target="_blank"
                                            class="tw-text-primary tw-underline">
                                            {{ item.variant?.product?.name }}
                                        </a>
                                    </template>
                                    <template v-else>
                                        {{ item.variant?.product?.name }}
                                    </template>
                                </h5>
                                <div class="tw-grid tw-grid-cols-2 tw-gap-2 tw-mt-2">
                                    <p class="tw-text-gray-600">Màu: {{ item.variant?.color }}</p>
                                    <p class="tw-text-gray-600">Size: {{ item.variant?.size }}</p>
                                    <p class="tw-text-gray-600">SKU: {{ item.variant?.sku }}</p>
                                    <p class="tw-text-gray-600">Mã SP: {{ item.variant?.product?.id }}</p>
                                </div>
                            </div>
                            <div class="tw-text-right tw-min-w-[120px]">
                                <div
                                    class="tw-bg-blue-100 tw-text-blue-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-sm tw-font-medium tw-mb-2">
                                    Số lượng: {{ item.quantity }}
                                </div>
                                <p class="tw-font-medium">{{ formatPrice(item.price) }}đ</p>
                                <p class="tw-text-gray-600">Tổng: {{ formatPrice(item.total_price) }}đ</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tổng tiền -->
                <div class="tw-border-t tw-pt-4">
                    <div class="tw-space-y-2">
                        <div class="tw-flex tw-justify-between">
                            <span class="tw-text-gray-600">Tổng tiền hàng</span>
                            <span>{{ formatPrice(currentOrder.total_price) }}đ</span>
                        </div>
                        <div class="tw-flex tw-justify-between">
                            <span class="tw-text-gray-600">Phí vận chuyển</span>
                            <span>{{ formatPrice(currentOrder.shipping_fee || 0) }}đ</span>
                        </div>
                        <div v-if="currentOrder.discount_price > 0" class="tw-flex tw-justify-between">
                            <span class="tw-text-gray-600">Giảm giá</span>
                            <span>-{{ formatPrice(currentOrder.discount_price) }}đ</span>
                        </div>
                        <div class="tw-flex tw-justify-between tw-font-bold tw-text-lg tw-border-t tw-pt-2">
                            <span>Thành tiền</span>
                            <span>{{ formatPrice(currentOrder.final_price) }}đ</span>
                        </div>
                    </div>
                </div>

                <!-- Cập nhật trạng thái và duyệt/từ chối hoàn hàng -->
                <div class="order-status">
                    <h3>Cập nhật trạng thái</h3>
                    <div class="tw-flex tw-items-center tw-gap-4 tw-mb-2 tw-justify-between">
                        <div class="tw-flex tw-items-center tw-gap-4">
                            <select v-model="selectedStatus" class="tw-border tw-rounded tw-px-4 tw-py-2">
                                <option v-for="opt in statusOptions" :value="opt.value" :key="opt.value">{{ opt.label }}
                                </option>
                            </select>
                            <select v-model="selectedPaymentStatus" class="tw-border tw-rounded tw-px-4 tw-py-2">
                                <option v-for="opt in paymentStatusOptions" :value="opt.value" :key="opt.value">{{
                                    opt.label }}</option>
                            </select>
                            <button
                                @click="handleUpdateStatus({ status: selectedStatus, payment_status: selectedPaymentStatus })"
                                class="tw-bg-primary tw-text-white tw-px-4 tw-py-2 tw-rounded hover:tw-bg-primary-dark">Gửi</button>
                        </div>
                        <div v-if="currentOrder.return_status === 'requested'"
                            class="tw-flex tw-justify-end tw-gap-2 tw-mt-4">
                            <button @click="handleApproveReturn"
                                class="tw-bg-green-600 tw-text-white tw-px-4 tw-py-2 tw-rounded hover:tw-bg-green-700">
                                Duyệt hoàn hàng
                            </button>
                            <button @click="openRejectModal"
                                class="tw-bg-red-600 tw-text-white tw-px-4 tw-py-2 tw-rounded hover:tw-bg-red-700">
                                Từ chối hoàn hàng
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Modal nhập lý do từ chối hoàn hàng -->
        <div v-if="showRejectModal"
            class="tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-items-center tw-justify-center tw-z-50">
            <div class="tw-bg-white tw-rounded-lg tw-shadow-lg tw-p-6 tw-w-full tw-max-w-md">
                <h3 class="tw-text-lg tw-font-bold tw-mb-4">Lý do từ chối hoàn hàng</h3>
                <textarea v-model="rejectReason" placeholder="Nhập lý do..."
                    class="tw-w-full tw-h-24 tw-p-2 tw-border tw-rounded tw-mb-4" />
                <div class="tw-flex tw-justify-end tw-gap-2">
                    <button @click="showRejectModal = false"
                        class="tw-bg-gray-200 tw-px-4 tw-py-2 tw-rounded">Hủy</button>
                    <button @click="handleRejectReturn"
                        class="tw-bg-red-600 tw-text-white tw-px-4 tw-py-2 tw-rounded hover:tw-bg-red-700">Xác
                        nhận</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useOrder } from '~/composables/useOrder'

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
    console.log('Current Order:', currentOrder.value)
    console.log('Order Details:', currentOrder.value?.orderDetails)
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
            return 'tw-bg-yellow-100 tw-text-yellow-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
        case 'processing':
            return 'tw-bg-blue-100 tw-text-blue-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
        case 'shipping':
            return 'tw-bg-purple-100 tw-text-purple-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
        case 'completed':
            return 'tw-bg-green-100 tw-text-green-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
        case 'cancelled':
            return 'tw-bg-red-100 tw-text-red-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
        case 'paid':
            return 'tw-bg-green-100 tw-text-green-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
        case 'failed':
            return 'tw-bg-red-100 tw-text-red-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
        case 'canceled':
            return 'tw-bg-red-100 tw-text-red-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
        case 'refunded':
            return 'tw-bg-gray-100 tw-text-gray-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
        default:
            return 'tw-bg-gray-100 tw-text-gray-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
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
        case 'requested': return 'tw-bg-orange-50 tw-border-orange-400'
        case 'approved': return 'tw-bg-green-50 tw-border-green-400'
        case 'rejected': return 'tw-bg-red-50 tw-border-red-400'
        default: return 'tw-bg-gray-50 tw-border-gray-400'
    }
}

const getReturnStatusIconClass = (status) => {
    switch (status) {
        case 'requested': return 'tw-bg-orange-100'
        case 'approved': return 'tw-bg-green-100'
        case 'rejected': return 'tw-bg-red-100'
        default: return 'tw-bg-gray-100'
    }
}

const getReturnStatusTextClass = (status) => {
    switch (status) {
        case 'requested': return 'tw-text-orange-600'
        case 'approved': return 'tw-text-green-600'
        case 'rejected': return 'tw-text-red-600'
        default: return 'tw-text-gray-600'
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