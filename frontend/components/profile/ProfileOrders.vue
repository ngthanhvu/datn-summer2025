<template>
    <div class="tw-bg-white tw-p-6 tw-rounded tw-shadow">
        <h2 class="tw-font-bold tw-text-lg tw-mb-6">Đơn hàng của tôi</h2>

        <div class="tw-flex tw-gap-4 tw-mb-6">
            <select v-model="selectedStatus" class="tw-border tw-rounded tw-px-4 tw-py-2 tw-w-56">
                <option value="">Tất cả trạng thái</option>
                <option v-for="status in orderStatuses" :key="status.value" :value="status.value">
                    {{ status.label }}
                </option>
            </select>

            <input v-model="selectedDate" type="text" class="tw-border tw-rounded tw-px-4 tw-py-2 tw-w-56"
                placeholder="dd/mm/yyyy" />
        </div>

        <div class="tw-overflow-x-auto">
            <table class="tw-w-full tw-text-left tw-bg-white tw-text-sm">
                <thead>
                    <tr class="tw-border-b tw-bg-gray-50">
                        <th class="tw-px-3 tw-py-2">Mã đơn</th>
                        <th class="tw-px-3 tw-py-2">Ngày đặt</th>
                        <th class="tw-px-3 tw-py-2">Sản phẩm</th>
                        <th class="tw-px-3 tw-py-2">Tổng tiền</th>
                        <th class="tw-px-3 tw-py-2">Thanh toán</th>
                        <th class="tw-px-3 tw-py-2">Trạng thái</th>
                        <th class="tw-px-3 tw-py-2">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="order in orders" :key="order.id" class="tw-border-b hover:tw-bg-gray-50">
                        <td class="tw-px-3 tw-py-2">
                            <span class="tw-font-medium">#{{ order.id }}</span>
                        </td>
                        <td class="tw-px-3 tw-py-2">
                            {{ formatDate(order.created_at) }}
                        </td>
                        <td class="tw-px-3 tw-py-2">
                            <div class="tw-flex tw-items-center tw-gap-2">
                                <img :src="order.order_details[0]?.variant?.product?.main_image?.image_path"
                                    class="tw-w-8 tw-h-8 tw-object-cover tw-rounded"
                                    :alt="order.order_details[0]?.variant?.product?.name" />
                                <div>
                                    <p class="tw-font-medium tw-text-sm">{{
                                        order.order_details[0]?.variant?.product?.name }}</p>
                                    <p class="tw-text-xs tw-text-gray-500">
                                        {{ order.order_details.length }} sản phẩm
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="tw-px-3 tw-py-2">
                            <span class="tw-font-medium">{{ formatPrice(order.final_price) }}đ</span>
                        </td>
                        <td class="tw-px-3 tw-py-2">
                            <div class="tw-flex tw-flex-col tw-gap-1">
                                <span :class="badgeClass(order.payment_status)">
                                    {{ getPaymentStatusLabel(order.payment_status) }}
                                </span>
                                <span class="tw-text-xs tw-text-gray-500">
                                    {{ getPaymentMethodLabel(order.payment_method) }}
                                </span>
                            </div>
                        </td>
                        <td class="tw-px-3 tw-py-2">
                            <span :class="badgeClass(order.status)">
                                {{ getStatusLabel(order.status) }}
                            </span>
                        </td>
                        <td class="tw-px-3 tw-py-2">
                            <button @click="openOrderDetail(order)"
                                class="tw-bg-blue-600 tw-text-white tw-rounded tw-px-3 tw-py-1 tw-text-sm hover:tw-bg-blue-700">
                                Chi tiết
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="orders.length === 0" class="tw-text-center tw-py-12">
            <svg class="tw-mx-auto tw-h-12 tw-w-12 tw-text-gray-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <h3 class="tw-mt-2 tw-text-sm tw-font-medium tw-text-gray-900">Không có đơn hàng</h3>
            <p class="tw-mt-1 tw-text-sm tw-text-gray-500">Bạn chưa có đơn hàng nào.</p>
        </div>

        <div v-if="showModal"
            class="tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-items-center tw-justify-center tw-z-50">
            <div
                class="tw-bg-white tw-rounded-lg tw-p-6 tw-w-full tw-max-w-2xl tw-max-h-[90vh] tw-overflow-y-auto tw-shadow-lg">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
                    <h3 class="tw-text-xl tw-font-bold">Chi tiết đơn hàng #{{ selectedOrder?.id }}</h3>
                    <button @click="closeModal" class="tw-text-gray-500 hover:tw-text-gray-700">
                        <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div v-if="selectedOrder" class="tw-space-y-8">
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
                                <span class="tw-text-xs tw-text-gray-500">{{ formatDate(selectedOrder.created_at)
                                    }}</span>
                            </div>
                            <div class="tw-flex-1 tw-h-0.5 tw-bg-gray-200 tw-mx-4"></div>
                            <div class="tw-flex tw-flex-col tw-items-center tw-relative">
                                <div :class="[
                                    'tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-white',
                                    selectedOrder.status === 'pending' ? 'tw-bg-yellow-500' : 'tw-bg-green-500'
                                ]">
                                    <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <span class="tw-text-sm tw-mt-2">Xác nhận</span>
                                <span class="tw-text-xs tw-text-gray-500">
                                    {{ selectedOrder.status === 'pending' ? 'Đang chờ' :
                                        formatDate(selectedOrder.updated_at) }}</span>
                            </div>
                            <div class="tw-flex-1 tw-h-0.5 tw-bg-gray-200 tw-mx-4"></div>
                            <div class="tw-flex tw-flex-col tw-items-center tw-relative">
                                <div :class="[
                                    'tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-white',
                                    ['shipping', 'completed'].includes(selectedOrder.status) ? 'tw-bg-green-500' : 'tw-bg-gray-300'
                                ]">
                                    <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                    </svg>
                                </div>
                                <span class="tw-text-sm tw-mt-2">Giao hàng</span>
                                <span class="tw-text-xs tw-text-gray-500">{{ ['shipping',
                                    'completed'].includes(selectedOrder.status) ? 'Đang giao' : 'Chờ xử lý' }}</span>
                            </div>
                            <div class="tw-flex-1 tw-h-0.5 tw-bg-gray-200 tw-mx-4"></div>
                            <div class="tw-flex tw-flex-col tw-items-center tw-relative">
                                <div :class="[
                                    'tw-w-10 tw-h-10 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-white',
                                    selectedOrder.status === 'completed' ? 'tw-bg-green-500' : 'tw-bg-gray-300'
                                ]">
                                    <svg class="tw-w-6 tw-h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="tw-text-sm tw-mt-2">Hoàn thành</span>
                                <span class="tw-text-xs tw-text-gray-500">
                                    {{ selectedOrder.status === 'completed' ? 'Đã nhậnhàng' : 'Chờ xử lý' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="tw-grid tw-grid-cols-2 tw-gap-6">
                        <div class="tw-bg-gray-50 tw-p-4 tw-rounded-lg">
                            <h4 class="tw-font-semibold tw-mb-4">Thông tin giao hàng</h4>
                            <div class="tw-space-y-2">
                                <p class="tw-flex tw-items-center">
                                    <svg class="tw-w-5 tw-h-5 tw-text-gray-500 tw-mr-2" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    {{ selectedOrder.address?.full_name }}
                                </p>
                                <p class="tw-flex tw-items-center">
                                    <svg class="tw-w-5 tw-h-5 tw-text-gray-500 tw-mr-2" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    {{ selectedOrder.address?.phone }}
                                </p>
                                <p class="tw-flex tw-items-start">
                                    <svg class="tw-w-5 tw-h-5 tw-text-gray-500 tw-mr-2 tw-mt-1" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ getFullAddress(selectedOrder.address) }}
                                </p>
                            </div>
                        </div>

                        <div class="tw-bg-gray-50 tw-p-4 tw-rounded-lg">
                            <h4 class="tw-font-semibold tw-mb-4">Thông tin thanh toán</h4>
                            <div class="tw-space-y-2">
                                <p class="tw-flex tw-items-center">
                                    <svg class="tw-w-5 tw-h-5 tw-text-gray-500 tw-mr-2" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    Phương thức: {{ getPaymentMethodLabel(selectedOrder.payment_method) }}
                                </p>
                                <p class="tw-flex tw-items-center">
                                    <svg class="tw-w-5 tw-h-5 tw-text-gray-500 tw-mr-2" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Trạng thái:
                                    <span :class="badgeClass(selectedOrder.payment_status)" class="tw-ml-2">
                                        {{ getPaymentStatusLabel(selectedOrder.payment_status) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="tw-font-semibold tw-mb-4">Sản phẩm</h4>
                        <div class="tw-space-y-4">
                            <div v-for="item in selectedOrder.order_details" :key="item.id"
                                class="tw-flex tw-items-center tw-gap-4 tw-p-4 tw-bg-gray-50 tw-rounded">
                                <img :src="item.variant?.product?.main_image?.image_path"
                                    class="tw-w-20 tw-h-20 tw-object-cover tw-rounded"
                                    :alt="item.variant?.product?.name" />
                                <div class="tw-flex-1">
                                    <h5 class="tw-font-medium">{{ item.variant?.product?.name }}</h5>
                                    <p class="tw-text-gray-600">Size: {{ item.variant?.size }}</p>
                                    <p class="tw-text-gray-600">Số lượng: {{ item.quantity }}</p>
                                </div>
                                <div class="tw-text-right">
                                    <p class="tw-font-medium">{{ formatPrice(item.price) }}đ</p>
                                    <p class="tw-text-gray-600">Tổng: {{ formatPrice(item.total_price) }}đ</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tw-border-t tw-pt-4">
                        <div class="tw-space-y-2">
                            <div class="tw-flex tw-justify-between">
                                <span class="tw-text-gray-600">Tổng tiền hàng</span>
                                <span>{{ formatPrice(selectedOrder.total_price) }}đ</span>
                            </div>
                            <div class="tw-flex tw-justify-between">
                                <span class="tw-text-gray-600">Phí vận chuyển</span>
                                <span>{{ formatPrice(selectedOrder.shipping_fee) }}đ</span>
                            </div>
                            <div class="tw-flex tw-justify-between">
                                <span class="tw-text-gray-600">Giảm giá</span>
                                <span>-{{ formatPrice(selectedOrder.discount_price) }}đ</span>
                            </div>
                            <div class="tw-flex tw-justify-between tw-font-bold tw-text-lg tw-border-t tw-pt-2">
                                <span>Thành tiền</span>
                                <span>{{ formatPrice(selectedOrder.final_price) }}đ</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useOrder } from '~/composables/useOrder'

useHead({
    title: 'Đơn hàng của tôi',
    meta: [
        {
            name: 'description',
            content: 'Quản lý đơn hàng của bạn',
        },
    ],
})

const orderService = useOrder()
const orders = ref([])
const showModal = ref(false)
const selectedOrder = ref(null)
const selectedStatus = ref('')
const selectedDate = ref('')

const columns = [
    { key: 'id', label: 'Mã đơn hàng' },
    { key: 'created_at', label: 'Ngày đặt' },
    { key: 'status', label: 'Trạng thái', type: 'status', labelKey: 'statusLabel' },
    { key: 'payment_status', label: 'Thanh toán', type: 'status', labelKey: 'paymentStatusLabel' },
    { key: 'final_price', label: 'Tổng tiền', type: 'price', prefix: '' },
]

const orderStatuses = [
    { value: 'pending', label: 'Chờ xác nhận' },
    { value: 'processing', label: 'Đang xử lý' },
    { value: 'shipping', label: 'Đang giao hàng' },
    { value: 'completed', label: 'Hoàn thành' },
    { value: 'cancelled', label: 'Đã hủy' }
]

const paymentStatuses = [
    { value: 'pending', label: 'Chờ thanh toán' },
    { value: 'paid', label: 'Đã thanh toán' },
    { value: 'failed', label: 'Thanh toán thất bại' },
    { value: 'canceled', label: 'Đã hủy' },
    { value: 'refunded', label: 'Đã hoàn tiền' }
]

const fetchOrders = async () => {
    try {
        const response = await orderService.getOrders()
        let filteredOrders = response.data

        if (selectedStatus.value) {
            filteredOrders = filteredOrders.filter(order => order.status === selectedStatus.value)
        }

        if (selectedDate.value) {
            const filterDate = new Date(selectedDate.value)
            filteredOrders = filteredOrders.filter(order => {
                const orderDate = new Date(order.created_at)
                return orderDate.toDateString() === filterDate.toDateString()
            })
        }

        orders.value = filteredOrders.map(order => ({
            ...order,
            statusLabel: getStatusLabel(order.status),
            paymentStatusLabel: getPaymentStatusLabel(determinePaymentStatus(order))
        }))
    } catch (error) {
        console.error('Error fetching orders:', error)
    }
}

const handleFilterChange = (filters) => {
    // console.log('Filters changed:', filters)
}

const openOrderDetail = (order) => {
    selectedOrder.value = order
    // console.log('Selected Order for detail:', selectedOrder.value)
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    selectedOrder.value = null
}

const getStatusLabel = (status) => {
    const found = orderStatuses.find(s => s.value === status)
    return found ? found.label : status
}

const getPaymentStatusLabel = (status) => {
    const found = paymentStatuses.find(s => s.value === status)
    return found ? found.label : status
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

const formatPrice = (price) => {
    const numPrice = Number(price) // Convert to number
    if (isNaN(numPrice)) return '0' // Return '0' if not a number
    return new Intl.NumberFormat('vi-VN').format(numPrice)
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

const getPaymentMethodLabel = (method) => {
    const methods = {
        'cod': 'Thanh toán khi nhận hàng (COD)',
        'vnpay': 'VNPay',
        'momo': 'MoMo',
        'paypal': 'PayPal'
    }
    return methods[method] || method
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

// Add function to determine payment status based on payment method and order status
const determinePaymentStatus = (order) => {
    if (order.status === 'cancelled') {
        return 'canceled'
    }

    if (order.payment_method === 'cod') {
        return 'pending'
    }

    if (['vnpay', 'momo', 'paypal'].includes(order.payment_method)) {
        return 'paid'
    }

    return order.payment_status
}

// Add watch for filters
watch([selectedStatus, selectedDate], () => {
    handleFilterChange({
        status: selectedStatus.value,
        date: selectedDate.value
    })
})

onMounted(() => {
    fetchOrders()
})
</script>