<template>
    <div>
        <button @click="toggleExpanded" class="flex justify-between items-center w-full mb-4">
            <h3 class="font-semibold">Thông tin thanh toán</h3>
            <i :class="['fas', isExpanded ? 'fa-chevron-up' : 'fa-chevron-down', 'text-gray-400']"></i>
        </button>
        <div v-show="isExpanded" class="space-y-2">
            <p><span class="font-medium">Phương thức:</span> {{ payment.method }}</p>
            <p><span class="font-medium">Tổng tiền:</span> {{ formatPrice(payment.total) }}</p>
            <p>
                <span class="font-medium">Trạng thái:</span>
                <span :class="payment.status === 'paid' ? 'text-green-600' : 'text-red-600'">
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
