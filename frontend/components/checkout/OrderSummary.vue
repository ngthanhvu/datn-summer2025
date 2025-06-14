<template>
    <div class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow-sm">
        <h2 class="tw-text-lg tw-font-semibold tw-mb-4">Đơn hàng của bạn</h2>
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

        <div class="tw-mb-6">
            <div class="tw-flex tw-gap-2">
                <input v-model="couponCode" type="text" placeholder="Nhập mã giảm giá"
                    class="tw-flex-1 tw-px-3 tw-py-2 tw-border tw-rounded-md">
                <button @click="applyCoupon"
                    class="tw-px-4 tw-py-2 tw-bg-[#81AACC] tw-text-white tw-rounded-md hover:tw-bg-[#6387A6]">
                    Áp dụng
                </button>
            </div>
            <div v-if="availableCoupons.length > 0" class="tw-mt-4">
                <h3 class="tw-text-sm tw-font-medium tw-mb-2">Mã giảm giá có sẵn:</h3>
                <div class="tw-max-h-[300px] tw-overflow-y-auto tw-pr-2 tw-space-y-3">
                    <div v-for="coupon in availableCoupons" :key="coupon.id"
                        class="tw-bg-white tw-shadow-sm tw-rounded-sm tw-flex tw-cursor-pointer hover:tw-shadow-md tw-transition"
                        @click="selectCoupon(coupon)">
                        <div
                            class="tw-flex-shrink-0 tw-bg-[#81AACC] tw-flex tw-items-center tw-justify-center tw-p-4 tw-rounded-l-sm">
                            <img src="https://ngthanhvu.github.io/ticket-sale-svgrepo-com.svg" alt="Coupon ticket icon"
                                class="tw-w-12 tw-h-12 tw-object-contain tw-text-white" width="48" height="48" />
                        </div>
                        <div class="tw-flex tw-flex-col tw-justify-center tw-px-4 tw-py-3 tw-flex-grow">
                            <p class="tw-text-sm tw-font-semibold tw-text-[#81AACC]">
                                NHẬP MÃ:
                                <span class="tw-font-normal">{{ coupon.code }}</span>
                            </p>
                            <p class="tw-text-xs tw-text-gray-500 tw-mt-1 tw-leading-tight">
                                {{ coupon.name }}
                            </p>
                            <div class="tw-flex tw-items-center tw-mt-2">
                                <span class="tw-text-sm tw-font-medium tw-text-[#81AACC]">
                                    {{ coupon.type === 'percent' ? `${coupon.value}%` : formatPrice(coupon.value) }}
                                </span>
                                <span v-if="coupon.min_order_value" class="tw-text-xs tw-text-gray-500 tw-ml-2">
                                    (Đơn tối thiểu {{ formatPrice(coupon.min_order_value) }})
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
        <button @click="$emit('place-order')"
            class="tw-w-full tw-mt-6 tw-px-6 tw-py-3 tw-bg-[#81AACC] tw-text-white tw-rounded-md hover:tw-bg-[#6387A6] tw-font-medium">
            Đặt hàng
        </button>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useCoupon } from '~/composables/useCoupon'

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

const emit = defineEmits(['apply-coupon', 'place-order'])

const couponCode = ref('')
const availableCoupons = ref([])
const couponService = useCoupon()

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
    if (couponCode.value.trim()) {
        emit('apply-coupon', couponCode.value.trim())
    }
}

const selectCoupon = (coupon) => {
    couponCode.value = coupon.code
    applyCoupon()
}

const fetchAvailableCoupons = async () => {
    try {
        const coupons = await couponService.getCoupons()

        if (!coupons || !Array.isArray(coupons)) {
            console.error('Invalid coupons data:', coupons)
            return
        }

        const now = new Date()
        availableCoupons.value = coupons.filter(coupon => {
            return coupon.is_active
        })

    } catch (error) {
        console.error('Error fetching coupons:', error)
    }
}

onMounted(() => {
    fetchAvailableCoupons()
})
</script>