<template>
    <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow-sm">
        <h2 class="tw-text-lg tw-font-semibold tw-mb-4">Đơn hàng của bạn</h2>

        <!-- Order Items -->
        <div class="tw-space-y-4 tw-mb-6">
            <div v-for="(item, index) in items" :key="index" class="tw-flex tw-items-center tw-gap-4">
                <img :src="item.image" :alt="item.name" class="tw-w-20 tw-h-20 tw-object-cover">
                <div class="tw-flex-1">
                    <h3 class="tw-font-medium">{{ item.name }}</h3>
                    <p class="tw-text-sm tw-text-gray-500">{{ item.variant }}</p>
                    <p class="tw-font-medium">{{ formatPrice(item.price) }}</p>
                </div>
            </div>
        </div>

        <!-- Coupon -->
        <div class="tw-mb-6">
            <div class="tw-flex tw-gap-2">
                <input v-model="couponCode" type="text" placeholder="Nhập mã giảm giá"
                    class="tw-flex-1 tw-px-3 tw-py-2 tw-border tw-rounded-md">
                <button @click="applyCoupon"
                    class="tw-px-4 tw-py-2 tw-bg-black tw-text-white tw-rounded-md hover:tw-bg-gray-800">
                    Áp dụng
                </button>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="tw-space-y-3 tw-border-t tw-pt-4">
            <div class="tw-flex tw-justify-between">
                <span>Tạm tính</span>
                <span>{{ formatPrice(subtotal) }}</span>
            </div>
            <div class="tw-flex tw-justify-between">
                <span>Phí vận chuyển</span>
                <span>{{ formatPrice(shipping) }}</span>
            </div>
            <div class="tw-flex tw-justify-between">
                <span>Giảm giá</span>
                <span class="tw-text-red-500">-{{ formatPrice(discount) }}</span>
            </div>
            <div class="tw-flex tw-justify-between tw-font-bold tw-text-lg tw-border-t tw-pt-3">
                <span>Tổng cộng</span>
                <span>{{ formatPrice(total) }}</span>
            </div>
        </div>

        <!-- Place Order Button -->
        <button @click="$emit('place-order')"
            class="tw-w-full tw-mt-6 tw-px-6 tw-py-3 tw-bg-black tw-text-white tw-rounded-md hover:tw-bg-gray-800 tw-font-medium">
            Đặt hàng
        </button>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
    items: {
        type: Array,
        required: true
    },
    subtotal: {
        type: Number,
        required: true
    },
    shipping: {
        type: Number,
        required: true
    },
    discount: {
        type: Number,
        default: 0
    }
})

const emit = defineEmits(['place-order', 'apply-coupon'])

const couponCode = ref('')

const total = computed(() => {
    return props.subtotal + props.shipping - props.discount
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}

const applyCoupon = () => {
    if (couponCode.value) {
        emit('apply-coupon', couponCode.value)
    }
}
</script>