<template>
    <div class="bg-white rounded-lg shadow px-6 py-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-semibold text-lg">Đơn hàng gần đây</h3>
            <router-link to="/admin/orders" class="text-primary hover:text-[#3BB77E] cursor-pointer">
                Xem tất cả
            </router-link>
        </div>
        <div class="overflow-x-auto overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-300 bg-gray-50">
                        <th class="px-4 py-3 text-left">Mã đơn</th>
                        <th class="px-4 py-3 text-left">Khách hàng</th>
                        <th class="px-4 py-3 text-left">Sản phẩm</th>
                        <th class="px-4 py-3 text-left">Tổng tiền</th>
                        <th class="px-4 py-3 text-left">Trạng thái</th>
                        <th class="px-4 py-3 text-left">Ngày đặt</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="order in orders" :key="order.id" class="border-b border-gray-300 hover:bg-gray-50">
                        <td class="px-4 py-3">#{{ order.tracking_code || order.id }}</td>
                        <td class="px-4 py-3">{{ order.customer }}</td>
                        <td class="px-4 py-3">{{ order.items }} sản phẩm</td>
                        <td class="px-4 py-3">{{ formatPrice(order.total) }}</td>
                        <td class="px-4 py-3">
                            <span :class="orderStatusClass(order.status)">
                                {{ order.status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ formatDate(order.date) }}</td>
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
        case 'Hoàn thành':
        case 'Đã giao hàng':
            return 'bg-green-100 text-green-700 border border-green-300 px-3 py-1 rounded-full text-xs'
        case 'Đang giao hàng':
            return 'bg-blue-100 text-blue-700 border border-blue-300 px-3 py-1 rounded-full text-xs'
        case 'Đang xử lý':
            return 'bg-yellow-100 text-yellow-700 border border-yellow-300 px-3 py-1 rounded-full text-xs'
        case 'Chờ xử lý':
            return 'bg-orange-100 text-orange-700 border border-orange-300 px-3 py-1 rounded-full text-xs'
        case 'Đã hủy':
            return 'bg-red-100 text-red-700 border border-red-300 px-3 py-1 rounded-full text-xs'
        case 'Đã trả hàng':
            return 'bg-gray-100 text-gray-700 border border-gray-300 px-3 py-1 rounded-full text-xs'
        default:
            return 'bg-gray-100 text-gray-700 border border-gray-300 px-3 py-1 rounded-full text-xs'
    }
}
</script>