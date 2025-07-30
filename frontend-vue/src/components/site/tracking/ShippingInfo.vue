<template>
    <div>
        <button @click="toggleExpanded" class="flex justify-between items-center w-full mb-4">
            <h3 class="font-semibold">Thông tin giao hàng</h3>
            <i :class="[
                'fas',
                isExpanded ? 'fa-chevron-up' : 'fa-chevron-down',
                'text-gray-400'
            ]"></i>
        </button>
        <div v-show="isExpanded" class="space-y-2">
            <p><span class="font-medium">Người nhận:</span> {{ shipping.fullName }}</p>
            <p><span class="font-medium">Số điện thoại:</span> {{ shipping.phone }}</p>
            <p><span class="font-medium">Địa chỉ:</span> {{ shipping.address }}</p>
            <p><span class="font-medium">Ghi chú:</span> {{ shipping.note }}</p>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
    shipping: {
        type: Object,
        required: true
    }
})

const isExpanded = ref(true)

const toggleExpanded = () => {
    isExpanded.value = !isExpanded.value
}
</script>