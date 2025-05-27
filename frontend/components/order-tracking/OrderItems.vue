<template>
    <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow-sm">
        <button @click="toggleExpanded" class="tw-flex tw-justify-between tw-items-center tw-w-full tw-mb-4">
            <h2 class="tw-text-lg tw-font-semibold">Chi tiết đơn hàng</h2>
            <i :class="[
                'fas',
                isExpanded ? 'fa-chevron-up' : 'fa-chevron-down',
                'tw-text-gray-400'
            ]"></i>
        </button>
        <div v-show="isExpanded">
            <div class="tw-space-y-4">
                <div v-for="(item, index) in items" :key="index" class="tw-flex tw-items-center tw-gap-4">
                    <img :src="item.image" :alt="item.name" class="tw-w-20 tw-h-20 tw-object-cover">
                    <div class="tw-flex-1">
                        <h3 class="tw-font-medium">{{ item.name }}</h3>
                        <p class="tw-text-sm tw-text-gray-500">Size: {{ item.size }} | Số lượng: {{ item.quantity }}</p>
                        <p class="tw-font-medium">{{ formatPrice(item.price) }}</p>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="tw-mt-6 tw-pt-6 tw-border-t">
                <div class="tw-space-y-3">
                    <div class="tw-flex tw-justify-between">
                        <span>Tạm tính</span>
                        <span>{{ formatPrice(summary.subtotal) }}</span>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span>Phí vận chuyển</span>
                        <span>{{ formatPrice(summary.shipping) }}</span>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span>Giảm giá</span>
                        <span class="tw-text-red-500">-{{ formatPrice(summary.discount) }}</span>
                    </div>
                    <div class="tw-flex tw-justify-between tw-font-bold tw-text-lg tw-border-t tw-pt-3">
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
</script>