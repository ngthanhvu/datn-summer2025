<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
            <h3 class="tw-font-semibold">Đơn hàng gần đây</h3>
            <NuxtLink to="/admin/orders" class="tw-text-primary hover:tw-text-primary-dark">
                Xem tất cả
            </NuxtLink>
        </div>
        <div class="tw-overflow-x-auto">
            <table class="tw-w-full">
                <thead>
                    <tr class="tw-border-b tw-bg-gray-50">
                        <th class="tw-px-4 tw-py-3 tw-text-left">Mã đơn</th>
                        <th class="tw-px-4 tw-py-3 tw-text-left">Khách hàng</th>
                        <th class="tw-px-4 tw-py-3 tw-text-left">Sản phẩm</th>
                        <th class="tw-px-4 tw-py-3 tw-text-left">Tổng tiền</th>
                        <th class="tw-px-4 tw-py-3 tw-text-left">Trạng thái</th>
                        <th class="tw-px-4 tw-py-3 tw-text-left">Ngày đặt</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="order in orders" :key="order.id" class="tw-border-b hover:tw-bg-gray-50">
                        <td class="tw-px-4 tw-py-3">#{{ order.id }}</td>
                        <td class="tw-px-4 tw-py-3">{{ order.customer }}</td>
                        <td class="tw-px-4 tw-py-3">{{ order.items }} sản phẩm</td>
                        <td class="tw-px-4 tw-py-3">{{ formatPrice(order.total) }}</td>
                        <td class="tw-px-4 tw-py-3">
                            <span :class="orderStatusClass(order.status)">
                                {{ order.status }}
                            </span>
                        </td>
                        <td class="tw-px-4 tw-py-3">{{ formatDate(order.date) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
defineProps({
    orders: {
        type: Array,
        required: true
    }
})

// Utility functions
const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const orderStatusClass = (status) => {
    switch (status) {
        case 'Đã giao':
            return 'tw-bg-green-100 tw-text-green-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
        case 'Đang giao':
            return 'tw-bg-blue-100 tw-text-blue-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
        case 'Đang xử lý':
            return 'tw-bg-yellow-100 tw-text-yellow-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
        case 'Chờ thanh toán':
            return 'tw-bg-orange-100 tw-text-orange-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
        case 'Đã hủy':
            return 'tw-bg-red-100 tw-text-red-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
        default:
            return 'tw-bg-gray-100 tw-text-gray-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
    }
}
</script>