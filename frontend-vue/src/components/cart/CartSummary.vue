<template>
    <aside class="w-full md:w-80 bg-[#f3f4f6] p-5 flex flex-col gap-8">
        <h3 class="font-semibold text-base text-black">Tóm tắt đơn hàng</h3>
        <hr class="border-t border-gray-300" />

        <div class="flex justify-between text-sm font-semibold text-black uppercase">
            <span>{{ itemCount }} sản phẩm</span>
            <span>{{ formatPrice(subtotal) }}</span>
        </div>

        <!--
        <div>
            <p class="text-sm font-semibold text-black uppercase mb-2">Phương thức giao hàng</p>
            <select class="w-full text-sm border border-gray-300 rounded px-3 py-2"
                aria-label="Shipping options"
                v-model="selectedShipping"
                @change="$emit('update:shipping', selectedShipping)">
                <option v-for="option in shippingOptions" :key="option.value" :value="option.value">
                    {{ option.label }} - {{ formatPrice(option.price) }}
                </option>
            </select>
        </div>
        -->

        <div class="flex justify-between text-sm font-semibold text-black uppercase border-t">
            <span class="mt-5">Tổng cộng</span>
            <span class="mt-5">{{ formatPrice(total) }}</span>
        </div>

        <button type="button"
            class="bg-[#81AACC] text-white text-sm font-semibold uppercase py-3 rounded w-full mt-2 hover:bg-[#427fb1] transition-colors cursor-pointer"
            @click="handleCheckout">
            Thanh toán
        </button>
    </aside>
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
    router.push('/thanh-toan')
}
</script>
