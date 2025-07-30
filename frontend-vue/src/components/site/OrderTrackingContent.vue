<template>
    <div class="max-w-7xl mx-auto px-4 md:px-6 py-8">
        <h1 class="text-2xl font-bold mb-8">Theo dõi đơn hàng</h1>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Column - Search Form -->
            <div>
                <SearchForm @search="searchOrder" />
            </div>

            <!-- Right Column - Order Details -->
            <div v-if="loading" class="bg-white p-6 rounded-lg shadow-sm text-center">
                <div class="py-12">
                    <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Đang tìm kiếm đơn hàng...</h3>
                </div>
            </div>

            <div v-else-if="orderError" class="bg-white p-6 rounded-lg shadow-sm text-center text-red-600">
                <div class="py-12">
                    <i class="fas fa-exclamation-circle text-4xl mb-4"></i>
                    <h3 class="text-lg font-medium mb-2">Lỗi: {{ orderError }}</h3>
                    <p class="text-gray-500">Vui lòng thử lại hoặc kiểm tra mã vận đơn.</p>
                </div>
            </div>

            <div v-else-if="!orderData" class="bg-white p-6 rounded-lg shadow-sm text-center">
                <div class="py-12">
                    <i class="fas fa-search text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Chưa có thông tin đơn hàng</h3>
                    <p class="text-gray-500">Vui lòng nhập mã vận đơn để tra cứu</p>
                </div>
            </div>

            <div v-else class="space-y-8">
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h2 class="text-lg font-semibold">Đơn hàng #{{ orderData.trackingCode }}</h2>
                            <p class="text-sm text-gray-500">Đặt ngày: {{ orderData.orderDate }}</p>
                        </div>
                        <div class="text-right">
                            <span
                                :class="['inline-block px-3 py-1 rounded-full text-sm', statusClasses[orderData.status]]">
                                {{ orderData.statusText }}
                            </span>
                        </div>
                    </div>

                    <OrderTimeline :timeline="orderData.timeline" />

                    <div class="space-y-6">
                        <ShippingInfo :shipping="orderData.shipping" />
                        <PaymentInfo :payment="orderData.payment" />
                    </div>
                </div>

                <OrderItems :items="orderData.items" :summary="orderData.summary" />
            </div>
        </div>
    </div>
</template>


<script setup>
import { ref, watch } from 'vue'
import SearchForm from './tracking/SearchForm.vue'
import OrderTimeline from './tracking/OrderTimeline.vue'
import ShippingInfo from './tracking/ShippingInfo.vue'
import PaymentInfo from './tracking/PaymentInfo.vue'
import OrderItems from './tracking/OrderItems.vue'
import { useOrder } from '../../composable/useOrder'

const orderData = ref(null)
const orderError = ref(null)
const loading = ref(false)

const { getOrderByTrackingCode } = useOrder()

watch(() => orderError.value, (newVal) => {
    orderError.value = newVal
})

const statusClasses = {
    pending: 'bg-yellow-100 text-yellow-800',
    processing: 'bg-blue-100 text-blue-800',
    shipping: 'bg-green-100 text-green-800',
    completed: 'bg-gray-100 text-gray-800',
    cancelled: 'bg-red-100 text-red-800'
}

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL

const mapOrderData = (order) => {
    if (!order) return null

    const getImageUrl = (path) => {
        if (!path) return 'https://placehold.co/100x100'
        if (path.startsWith('http://') || path.startsWith('https://')) {
            const storageIndex = path.lastIndexOf('/storage/')
            if (storageIndex !== -1) return apiBaseUrl.replace(/\/$/, '') + path.substring(storageIndex)
            return path
        }
        if (path.startsWith('/storage/')) return apiBaseUrl.replace(/\/$/, '') + path
        if (path.startsWith('storage/')) return apiBaseUrl.replace(/\/$/, '') + '/' + path
        return apiBaseUrl.replace(/\/$/, '') + '/' + path
    }

    return {
        trackingCode: order.tracking_code,
        orderDate: new Date(order.created_at).toLocaleDateString('vi-VN'),
        status: order.status,
        statusText: order.status,
        timeline: [
            {
                title: 'Đơn hàng đã được xác nhận',
                time: new Date(order.created_at).toLocaleDateString('vi-VN') + ' ' +
                    new Date(order.created_at).toLocaleTimeString('vi-VN').substring(0, 5),
                icon: 'fas fa-check',
                completed: true
            },
            ...(order.status !== 'pending' ? [{
                title: 'Đơn hàng đang được xử lý',
                time: order.updated_at
                    ? new Date(order.updated_at).toLocaleDateString('vi-VN') + ' ' +
                    new Date(order.updated_at).toLocaleTimeString('vi-VN').substring(0, 5)
                    : 'N/A',
                icon: 'fas fa-box',
                completed: true
            }] : []),
            ...(order.status === 'shipping' || order.status === 'completed' ? [{
                title: 'Đơn hàng đang được giao',
                time: order.updated_at
                    ? new Date(order.updated_at).toLocaleDateString('vi-VN') + ' ' +
                    new Date(order.updated_at).toLocaleTimeString('vi-VN').substring(0, 5)
                    : 'N/A',
                icon: 'fas fa-truck',
                completed: true
            }] : []),
            ...(order.status === 'completed' ? [{
                title: 'Giao hàng thành công',
                time: order.updated_at
                    ? new Date(order.updated_at).toLocaleDateString('vi-VN') + ' ' +
                    new Date(order.updated_at).toLocaleTimeString('vi-VN').substring(0, 5)
                    : 'N/A',
                icon: 'fas fa-home',
                completed: true
            }] : []),
            ...(order.status === 'cancelled' ? [{
                title: 'Đơn hàng đã hủy',
                time: order.updated_at
                    ? new Date(order.updated_at).toLocaleDateString('vi-VN') + ' ' +
                    new Date(order.updated_at).toLocaleTimeString('vi-VN').substring(0, 5)
                    : 'N/A',
                icon: 'fas fa-times-circle',
                completed: true
            }] : [])
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


<style></style>e