<template>
    <aside class="tw-w-full md:tw-w-80 tw-bg-[#f3f4f6] tw-p-5 tw-flex tw-flex-col tw-gap-8">
        <h3 class="tw-font-semibold tw-text-base tw-text-black">Tóm tắt đơn hàng</h3>
        <hr class="tw-border-t tw-border-gray-300" />

        <div class="tw-flex tw-justify-between tw-text-sm tw-font-semibold tw-text-black tw-uppercase">
            <span>{{ itemCount }} sản phẩm</span>
            <span>{{ formatPrice(subtotal) }}</span>
        </div>

        <!-- <div>
            <p class="tw-text-sm tw-font-semibold tw-text-black tw-uppercase tw-mb-2">Phương thức giao hàng</p>
            <select class="tw-w-full tw-text-sm tw-border tw-border-gray-300 tw-rounded tw-px-3 tw-py-2"
                aria-label="Shipping options" v-model="selectedShipping"
                @change="$emit('update:shipping', selectedShipping)">
                <option v-for="option in shippingOptions" :key="option.value" :value="option.value">
                    {{ option.label }} - {{ formatPrice(option.price) }}
                </option>
            </select>
        </div> -->

        <div class="tw-flex tw-justify-between tw-text-sm tw-font-semibold tw-text-black tw-uppercase tw-border-t">
            <span class="tw-mt-5">Tổng cộng</span>
            <span class="tw-mt-5">{{ formatPrice(total) }}</span>

        </div>

        <button type="button"
            class="tw-bg-[#81AACC] tw-text-white tw-text-sm tw-font-semibold tw-uppercase tw-py-3 tw-rounded tw-w-full tw-mt-2 hover:tw-bg-[#427fb1] tw-transition-colors"
            @click="$emit('checkout')">
            Thanh toán
        </button>
    </aside>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
    itemCount: {
        type: Number,
        required: true
    },
    subtotal: {
        type: Number,
        required: true
    },
    shipping: {
        type: Object,
        required: true
    }
})

const shippingOptions = [
    { label: 'Giao hàng tiêu chuẩn', value: 'standard', price: 10000 },
    { label: 'Giao hàng nhanh', value: 'express', price: 20000 }
]

const selectedShipping = ref(props.shipping?.value || shippingOptions[0].value)

const total = computed(() => {
    const shippingPrice = shippingOptions.find(option => option.value === selectedShipping.value)?.price || 0
    return props.subtotal + shippingPrice
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}

defineEmits(['update:shipping', 'checkout'])
</script>