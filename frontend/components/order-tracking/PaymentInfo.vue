<template>
    <div>
        <button @click="toggleExpanded" class="tw-flex tw-justify-between tw-items-center tw-w-full tw-mb-4">
            <h3 class="tw-font-semibold">Thông tin thanh toán</h3>
            <i :class="[
                'fas',
                isExpanded ? 'fa-chevron-up' : 'fa-chevron-down',
                'tw-text-gray-400'
            ]"></i>
        </button>
        <div v-show="isExpanded" class="tw-space-y-2">
            <p><span class="tw-font-medium">Phương thức:</span> {{ payment.method }}</p>
            <p><span class="tw-font-medium">Tổng tiền:</span> {{ formatPrice(payment.total) }}</p>
            <p><span class="tw-font-medium">Trạng thái:</span>
                <span :class="payment.status === 'paid' ? 'tw-text-green-600' : 'tw-text-red-600'">
                    {{ payment.status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                </span>
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
    payment: {
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