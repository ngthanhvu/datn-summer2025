<template>
    <div class="tw-max-w-7xl tw-mx-auto tw-px-4 md:tw-px-6 tw-py-8">
        <h1 class="tw-text-2xl tw-font-bold tw-mb-8">Theo dõi đơn hàng</h1>

        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8">
            <!-- Left Column - Search Form -->
            <div>
                <SearchForm @search="searchOrder" />
            </div>

            <!-- Right Column - Order Details -->
            <div v-if="loading" class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow-sm tw-text-center">
                <div class="tw-py-12">
                    <i class="fas fa-spinner fa-spin tw-text-4xl tw-text-gray-400 tw-mb-4"></i>
                    <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-2">Đang tìm kiếm đơn hàng...</h3>
                </div>
            </div>

            <div v-else-if="orderError"
                class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow-sm tw-text-center tw-text-red-600">
                <div class="tw-py-12">
                    <i class="fas fa-exclamation-circle tw-text-4xl tw-mb-4"></i>
                    <h3 class="tw-text-lg tw-font-medium tw-mb-2">Lỗi: {{ orderError }}</h3>
                    <p class="tw-text-gray-500">Vui lòng thử lại hoặc kiểm tra mã vận đơn.</p>
                </div>
            </div>

            <div v-else-if="!orderData" class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow-sm tw-text-center">
                <div class="tw-py-12">
                    <i class="fas fa-search tw-text-4xl tw-text-gray-400 tw-mb-4"></i>
                    <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-2">Chưa có thông tin đơn hàng</h3>
                    <p class="tw-text-gray-500">Vui lòng nhập mã vận đơn để tra cứu</p>
                </div>
            </div>

            <div v-else class="tw-space-y-8">
                <!-- Order Information -->
                <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow-sm">
                    <div class="tw-flex tw-justify-between tw-items-start tw-mb-6">
                        <div>
                            <h2 class="tw-text-lg tw-font-semibold">Đơn hàng #{{ orderData.trackingCode }}</h2>
                            <p class="tw-text-sm tw-text-gray-500">Đặt ngày: {{ orderData.orderDate }}</p>
                        </div>
                        <div class="tw-text-right">
                            <span :class="[
                                'tw-inline-block tw-px-3 tw-py-1 tw-rounded-full tw-text-sm',
                                statusClasses[orderData.status]
                            ]">
                                {{ orderData.statusText }}
                            </span>
                        </div>
                    </div>

                    <!-- Order Status Timeline -->
                    <OrderTimeline :timeline="orderData.timeline" />

                    <!-- Order Details -->
                    <div class="tw-space-y-6">
                        <!-- Shipping Information -->
                        <ShippingInfo :shipping="orderData.shipping" />

                        <!-- Payment Information -->
                        <PaymentInfo :payment="orderData.payment" />
                    </div>
                </div>

                <!-- Order Items -->
                <OrderItems :items="orderData.items" :summary="orderData.summary" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import SearchForm from '~/components/order-tracking/SearchForm.vue'
import OrderTimeline from '~/components/order-tracking/OrderTimeline.vue'
import ShippingInfo from '~/components/order-tracking/ShippingInfo.vue'
import PaymentInfo from '~/components/order-tracking/PaymentInfo.vue'
import OrderItems from '~/components/order-tracking/OrderItems.vue'
import { useOrder } from '~/composables/useOrder'
import { useNuxtApp } from '#app'

const { $config: runtimeConfig } = useNuxtApp()

const orderData = ref(null)
const orderError = ref(null)

const loading = ref(false)

const { getOrderByTrackingCode } = useOrder()

watch(() => orderError.value, (newVal) => {
    orderError.value = newVal
})

const statusClasses = {
    'pending': 'tw-bg-yellow-100 tw-text-yellow-800',
    'processing': 'tw-bg-blue-100 tw-text-blue-800',
    'shipping': 'tw-bg-green-100 tw-text-green-800',
    'completed': 'tw-bg-gray-100 tw-text-gray-800',
    'cancelled': 'tw-bg-red-100 tw-text-red-800'
}

const mapOrderData = (order) => {
    if (!order) {
        return null;
    }
    const getImageUrl = (path) => {
        if (!path) return 'https://placehold.co/100x100'
        if (path.includes('http://') || path.includes('https://')) {
            const storageIndex = path.lastIndexOf('/storage/')
            if (storageIndex !== -1) {
                return runtimeConfig.public.apiBaseUrl.replace(/\/$/, '') + path.substring(storageIndex)
            }
            return path
        }
        if (path.startsWith('/storage/')) return runtimeConfig.public.apiBaseUrl.replace(/\/$/, '') + path
        if (path.startsWith('storage/')) return runtimeConfig.public.apiBaseUrl.replace(/\/$/, '') + '/' + path
        return runtimeConfig.public.apiBaseUrl.replace(/\/$/, '') + '/' + path
    }
    return {
        trackingCode: order.tracking_code,
        orderDate: new Date(order.created_at).toLocaleDateString('vi-VN'),
        status: order.status,
        statusText: order.status,
        timeline: [
            {
                title: 'Đơn hàng đã được xác nhận',
                time: new Date(order.created_at).toLocaleDateString('vi-VN') + ' ' + new Date(order.created_at).toLocaleTimeString('vi-VN').substring(0, 5),
                icon: 'fas fa-check',
                completed: true
            },
            ...(order.status === 'processing' || order.status === 'shipping' || order.status === 'completed' || order.status === 'cancelled' ? [
                {
                    title: 'Đơn hàng đang được xử lý',
                    time: order.updated_at ? new Date(order.updated_at).toLocaleDateString('vi-VN') + ' ' + new Date(order.updated_at).toLocaleTimeString('vi-VN').substring(0, 5) : 'N/A',
                    icon: 'fas fa-box',
                    completed: order.status !== 'pending'
                }
            ] : []),
            ...(order.status === 'shipping' || order.status === 'completed' ? [
                {
                    title: 'Đơn hàng đang được giao',
                    time: order.updated_at ? new Date(order.updated_at).toLocaleDateString('vi-VN') + ' ' + new Date(order.updated_at).toLocaleTimeString('vi-VN').substring(0, 5) : 'N/A',
                    icon: 'fas fa-truck',
                    completed: order.status === 'shipping' || order.status === 'completed'
                }
            ] : []),
            ...(order.status === 'completed' ? [
                {
                    title: 'Giao hàng thành công',
                    time: order.updated_at ? new Date(order.updated_at).toLocaleDateString('vi-VN') + ' ' + new Date(order.updated_at).toLocaleTimeString('vi-VN').substring(0, 5) : 'N/A',
                    icon: 'fas fa-home',
                    completed: true
                }
            ] : []),
            ...(order.status === 'cancelled' ? [
                {
                    title: 'Đơn hàng đã hủy',
                    time: order.updated_at ? new Date(order.updated_at).toLocaleDateString('vi-VN') + ' ' + new Date(order.updated_at).toLocaleTimeString('vi-VN').substring(0, 5) : 'N/A',
                    icon: 'fas fa-times-circle',
                    completed: true
                }
            ] : []),
        ],
        shipping: {
            fullName: order.address?.full_name || 'N/A',
            phone: order.address?.phone || 'N/A',
            address: `${order.address?.street}, ${order.address?.ward}, ${order.address?.district}, ${order.address?.province}` || 'N/A',
            note: order.note || 'Không có ghi chú'
        },
        payment: {
            method: order.payment_method,
            total: order.final_price,
            status: order.payment_status,
            statusText: order.payment_status
        },
        items: (order.order_details || []).map(item => ({
            name: item.variant?.product?.name || 'N/A',
            size: item.variant?.size || 'N/A',
            quantity: item.quantity || 0,
            price: item.price || 0,
            image: getImageUrl(item.variant?.product?.main_image?.image_path)
        })),
        summary: {
            subtotal: order.total_price,
            shipping: order.shipping_fee,
            discount: order.discount_price,
            total: order.final_price
        }
    }
}

const searchOrder = async (formData) => {
    try {
        orderError.value = null
        orderData.value = null
        const order = await getOrderByTrackingCode(formData.trackingCode)
        orderData.value = mapOrderData(order)
    } catch (err) {
        orderError.value = err?.message || 'Không tìm thấy đơn hàng'
    }
}
</script>

<style scoped></style>