<template>
    <div class="tw-max-w-7xl tw-mx-auto tw-px-4 md:tw-px-6 tw-py-8">
        <h1 class="tw-text-2xl tw-font-bold tw-mb-8">Theo dõi đơn hàng</h1>

        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8">
            <!-- Left Column - Search Form -->
            <div>
                <SearchForm @search="searchOrder" />
            </div>

            <!-- Right Column - Order Details -->
            <div v-if="!orderData" class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow-sm tw-text-center">
                <div class="tw-py-12">
                    <i class="fas fa-search tw-text-4xl tw-text-gray-400 tw-mb-4"></i>
                    <h3 class="tw-text-lg tw-font-medium tw-text-gray-900 tw-mb-2">Chưa có thông tin đơn hàng</h3>
                    <p class="tw-text-gray-500">Vui lòng nhập mã đơn hàng và số điện thoại để tra cứu</p>
                </div>
            </div>

            <div v-else class="tw-space-y-8">
                <!-- Order Information -->
                <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow-sm">
                    <div class="tw-flex tw-justify-between tw-items-start tw-mb-6">
                        <div>
                            <h2 class="tw-text-lg tw-font-semibold">Đơn hàng #{{ orderData.orderId }}</h2>
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
import { ref } from 'vue'
import SearchForm from '~/components/order-tracking/SearchForm.vue'
import OrderTimeline from '~/components/order-tracking/OrderTimeline.vue'
import ShippingInfo from '~/components/order-tracking/ShippingInfo.vue'
import PaymentInfo from '~/components/order-tracking/PaymentInfo.vue'
import OrderItems from '~/components/order-tracking/OrderItems.vue'

const statusClasses = {
    'processing': 'tw-bg-blue-100 tw-text-blue-800',
    'shipping': 'tw-bg-green-100 tw-text-green-800',
    'delivered': 'tw-bg-gray-100 tw-text-gray-800',
    'cancelled': 'tw-bg-red-100 tw-text-red-800'
}

const orderData = ref(null)

// Sample data for testing
const sampleOrderData = {
    orderId: 'EGA123456',
    orderDate: '20/03/2024',
    status: 'shipping',
    statusText: 'Đang giao hàng',
    timeline: [
        {
            title: 'Đơn hàng đã được xác nhận',
            time: '20/03/2024 10:30',
            icon: 'fas fa-check',
            completed: true
        },
        {
            title: 'Đơn hàng đang được xử lý',
            time: '20/03/2024 14:15',
            icon: 'fas fa-box',
            completed: true
        },
        {
            title: 'Đơn hàng đang được giao',
            time: '21/03/2024 09:00',
            icon: 'fas fa-truck',
            completed: true
        },
        {
            title: 'Giao hàng thành công',
            time: 'Chưa hoàn thành',
            icon: 'fas fa-home',
            completed: false
        }
    ],
    shipping: {
        fullName: 'Nguyễn Văn A',
        phone: '0123456789',
        address: '123 Đường ABC, Phường 1, Quận 1, TP. HCM',
        note: 'Giao giờ hành chính'
    },
    payment: {
        method: 'Thanh toán khi nhận hàng (COD)',
        total: 2210000,
        status: 'paid'
    },
    items: [
        {
            name: 'Áo sơ mi trắng',
            size: 'M',
            quantity: 1,
            price: 1290000,
            image: 'https://placehold.co/100x100'
        },
        {
            name: 'Quần jean slim fit',
            size: '32',
            quantity: 1,
            price: 890000,
            image: 'https://placehold.co/100x100'
        }
    ],
    summary: {
        subtotal: 2180000,
        shipping: 30000,
        discount: 0,
        total: 2210000
    }
}

const searchOrder = (formData) => {
    // Simulate API call
    if (formData.orderId && formData.phone) {
        orderData.value = sampleOrderData
    } else {
        orderData.value = null
    }
}
</script>

<style scoped>
/* Add any component-specific styles here */
</style>