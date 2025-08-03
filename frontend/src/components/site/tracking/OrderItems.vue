<template>
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <button @click="toggleExpanded" class="flex justify-between items-center w-full mb-4">
            <h2 class="text-lg font-semibold">Chi tiết đơn hàng</h2>
            <i :class="['fas', isExpanded ? 'fa-chevron-up' : 'fa-chevron-down', 'text-gray-400']"></i>
        </button>

        <div v-show="isExpanded">
            <div class="space-y-4">
                <div v-for="(item, index) in items" :key="index" class="flex items-center gap-4">
                    <img :src="getImageUrl(item.image)" :alt="item.name" class="w-20 h-20 object-cover" />
                    <div class="flex-1">
                        <h3 class="font-medium">{{ item.name }}</h3>
                        <p class="text-sm text-gray-500">Size: {{ item.size }} | Số lượng: {{ item.quantity }}</p>
                        <p class="font-medium">{{ formatPrice(item.price) }}</p>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="mt-6 pt-6 border-t border-gray-300">
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span>Tạm tính</span>
                        <span>{{ formatPrice(summary.subtotal) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Phí vận chuyển</span>
                        <span>{{ formatPrice(summary.shipping) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Giảm giá</span>
                        <span class="text-red-500">-{{ formatPrice(summary.discount) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-lg border-t border-gray-300 pt-3">
                        <span>Tổng cộng</span>
                        <span>{{ formatPrice(summary.total) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
    items: {
        type: Array,
        required: true
    },
    summary: {
        type: Object,
        required: true
    }
})

const isExpanded = ref(true)

const toggleExpanded = () => {
    isExpanded.value = !isExpanded.value
}

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL

const getImageUrl = (path) => {
    if (!path) return '/default-image.jpg'
    if (path.startsWith('http://') || path.startsWith('https://')) return path
    if (path.startsWith('/storage/')) return apiBaseUrl.replace(/\/$/, '') + path
    if (path.startsWith('storage/')) return apiBaseUrl.replace(/\/$/, '') + '/' + path
    return apiBaseUrl.replace(/\/$/, '') + '/' + path
}
</script>
