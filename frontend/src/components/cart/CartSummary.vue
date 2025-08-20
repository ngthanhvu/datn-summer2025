<template>
    <div class="w-full md:w-80 bg-[#f3f4f6] p-5 flex flex-col gap-8">
        <h3 class="font-semibold text-base text-black">Tóm tắt đơn hàng</h3>
        <hr class="border-t border-gray-300" />

        <div class="flex justify-between text-sm font-semibold text-black uppercase">
            <span>{{ itemCount }} sản phẩm</span>
            <span>{{ formatPrice(subtotal) }}</span>
        </div>

        <div class="flex justify-between text-sm font-semibold text-black uppercase border-t border-gray-300">
            <span class="mt-5">Tổng cộng</span>
            <span class="mt-5">{{ formatPrice(total) }}</span>
        </div>

        <!-- Stock Warning -->
        <div v-if="hasOverStockItems" class="p-3 bg-orange-50 border border-orange-200 rounded-lg">
            <div class="flex items-center gap-2 text-orange-800 text-sm">
                <i class="fas fa-exclamation-triangle"></i>
                <span class="font-semibold">Không thể thanh toán</span>
            </div>
            <p class="text-orange-700 text-xs mt-1">
                Vui lòng điều chỉnh số lượng sản phẩm vượt quá tồn kho trước khi thanh toán.
            </p>
        </div>

        <button type="button"
            :disabled="hasOverStockItems"
            class="text-sm font-semibold uppercase py-3 rounded w-full mt-2 transition-colors cursor-pointer"
            :class="[
                hasOverStockItems
                    ? 'bg-gray-400 text-gray-600 cursor-not-allowed'
                    : 'bg-[#81AACC] text-white hover:bg-[#427fb1]'
            ]"
            @click="handleCheckout">
            <span v-if="hasOverStockItems">Không thể thanh toán</span>
            <span v-else>Thanh toán</span>
        </button>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'

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
    },
    hasOverStockItems: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['update:shipping', 'checkout'])

const router = useRouter()

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

const handleCheckout = () => {
    if (props.hasOverStockItems) {
        return // Không cho phép thanh toán khi có sản phẩm vượt quá tồn kho
    }
    router.push('/thanh-toan')
}
</script>
