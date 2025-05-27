<template>
    <div>
        <button @click="toggleExpanded" class="tw-flex tw-justify-between tw-items-center tw-w-full tw-mb-4">
            <h3 class="tw-font-semibold">Thông tin giao hàng</h3>
            <i :class="[
                'fas',
                isExpanded ? 'fa-chevron-up' : 'fa-chevron-down',
                'tw-text-gray-400'
            ]"></i>
        </button>
        <div v-show="isExpanded" class="tw-space-y-2">
            <p><span class="tw-font-medium">Người nhận:</span> {{ shipping.fullName }}</p>
            <p><span class="tw-font-medium">Số điện thoại:</span> {{ shipping.phone }}</p>
            <p><span class="tw-font-medium">Địa chỉ:</span> {{ shipping.address }}</p>
            <p><span class="tw-font-medium">Ghi chú:</span> {{ shipping.note }}</p>
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