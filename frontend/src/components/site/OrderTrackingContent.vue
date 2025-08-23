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
                    <h3 class="text-lg font-medium mb-2">Lỗi: Đơn hàng không tồn tại hoặc nhập sai mã</h3>
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

const translateStatus = (status) => {
    switch (status) {
        case 'pending': return 'Chờ xác nhận'
        case 'processing': return 'Đang xử lý'
        case 'shipping': return 'Đang giao hàng'
        case 'completed': return 'Hoàn thành'
        case 'cancelled': return 'Đã hủy'
        default: return status
    }
}

const mapOrderData = (order) => {
    if (!order) return null

    const getImageUrl = (path) => {
        if (!path) return 'https://placehold.co/100x100?text=No+Image'
        
        // Nếu đã là URL đầy đủ từ backend (có /storage/)
        if (path.startsWith('http://') || path.startsWith('https://')) {
            return path
        }
        
        // Nếu là relative path, tạo URL đầy đủ
        if (path.startsWith('/storage/')) {
            return apiBaseUrl.replace(/\/$/, '') + path
        }
        
        if (path.startsWith('storage/')) {
            return apiBaseUrl.replace(/\/$/, '') + '/' + path
        }
        
        // Fallback
        return 'https://placehold.co/100x100?text=Image+Error'
    }

    // Helper function to safely format date
    const formatDate = (dateString) => {
        if (!dateString) return 'N/A'
        try {
            const date = new Date(dateString)
            if (isNaN(date.getTime())) return 'N/A'
            return date.toLocaleDateString('vi-VN')
        } catch (error) {
            return 'N/A'
        }
    }

    // Helper function to safely format date and time
    const formatDateTime = (dateString) => {
        if (!dateString) return 'N/A'
        try {
            const date = new Date(dateString)
            if (isNaN(date.getTime())) return 'N/A'
            return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN').substring(0, 5)
        } catch (error) {
            return 'N/A'
        }
    }

    // Helper function to safely format address
    const formatAddress = (address) => {
        if (!address) return 'N/A'
        const parts = []
        if (address.street) parts.push(address.street)
        if (address.ward) parts.push(address.ward)
        if (address.district) parts.push(address.district)
        if (address.province) parts.push(address.province)
        return parts.length > 0 ? parts.join(', ') : 'N/A'
    }

    // Helper function to safely calculate shipping fee
    const calculateShippingFee = () => {
        const total = parseFloat(order.total_price) || 0
        const final = parseFloat(order.final_price) || 0
        const shipping = final - total
        return isNaN(shipping) ? 0 : shipping
    }

    return {
        trackingCode: order.tracking_code || 'N/A',
        orderDate: formatDate(order.created_at),
        status: order.status || 'pending',
        statusText: order.status || 'Chờ xác nhận',
        trackingCode: order.tracking_code,
        orderDate: new Date(order.created_at).toLocaleDateString('vi-VN'),
        status: order.status,
        statusText: translateStatus(order.status),
        timeline: [
            {
                title: 'Đơn hàng đã được xác nhận',
                time: formatDateTime(order.created_at),
                icon: 'fas fa-check',
                completed: true
            },
            ...(order.status !== 'pending' ? [{
                title: 'Đơn hàng đang được xử lý',
                time: formatDateTime(order.updated_at),
                icon: 'fas fa-box',
                completed: true
            }] : []),
            ...(order.status === 'shipping' || order.status === 'completed' ? [{
                title: 'Đơn hàng đang được giao',
                time: formatDateTime(order.updated_at),
                icon: 'fas fa-truck',
                completed: true
            }] : []),
            ...(order.status === 'completed' ? [{
                title: 'Giao hàng thành công',
                time: formatDateTime(order.updated_at),
                icon: 'fas fa-home',
                completed: true
            }] : []),
            ...(order.status === 'cancelled' ? [{
                title: 'Đơn hàng đã hủy',
                time: formatDateTime(order.updated_at),
                icon: 'fas fa-times-circle',
                completed: true
            }] : [])
        ],
        shipping: {
            fullName: order.address?.full_name || 'N/A',
            phone: order.address?.phone || 'N/A',
            address: formatAddress(order.address),
            note: order.note || 'Không có ghi chú'
        },
        payment: {
            method: order.payment_method || 'N/A',
            total: parseFloat(order.final_price) || 0,
            status: order.payment_status || 'pending',
            statusText: order.payment_status === 'paid' ? 'Đã thanh toán' : 
                       order.payment_status === 'pending' ? 'Chưa thanh toán' : 
                       order.payment_status === 'failed' ? 'Thanh toán thất bại' : 
                       order.payment_status === 'refunded' ? 'Đã hoàn tiền' : 
                       order.payment_status === 'canceled' ? 'Đã hủy' : 'Chưa thanh toán'
        },
        items: (order.order_details || []).map(item => ({
            name: item.variant?.product?.name || 'N/A',
            size: item.variant?.size || 'N/A',
            quantity: parseInt(item.quantity) || 0,
            price: parseFloat(item.price) || 0,
            image: item.variant?.product?.mainImage?.image_url || item.variant?.product?.main_image?.image_url || 'https://placehold.co/100x100?text=No+Image'
        })),
        summary: {
            subtotal: parseFloat(order.total_price) || 0,
            shipping: calculateShippingFee(),
            discount: parseFloat(order.discount_price) || 0,
            total: parseFloat(order.final_price) || 0
        }
    }
}

const searchOrder = async (formData) => {
    try {
        loading.value = true
        orderError.value = null
        orderData.value = null
        
        if (!formData.trackingCode || formData.trackingCode.trim() === '') {
            orderError.value = 'Vui lòng nhập mã vận đơn'
            return
        }
        
        const order = await getOrderByTrackingCode(formData.trackingCode.trim())
        if (order) {
            orderData.value = mapOrderData(order)
        } else {
            orderError.value = 'Không tìm thấy đơn hàng'
        }
    } catch (err) {
        console.error('Search order error:', err)
        orderError.value = err?.message || 'Có lỗi xảy ra khi tra cứu đơn hàng'
    } finally {
        loading.value = false
    }
}
</script>


<style></style>