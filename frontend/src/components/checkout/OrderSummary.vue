<template>
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <h2 class="text-lg font-semibold mb-4">Đơn hàng của bạn</h2>
        <div class="space-y-4 mb-6 max-h-[300px] overflow-y-auto pr-2">
            <div v-for="(item, index) in items" :key="index" class="flex items-center gap-4">
                <img :src="getImageUrl(item.image)" :alt="item.name" class="w-20 h-20 object-cover">
                <div class="flex-1">
                    <h3 class="font-medium">{{ item.name }}</h3>
                    <p class="text-sm text-gray-500">{{ item.variant }}</p>
                    <p class="font-medium">{{ formatPrice(item.price) }}</p>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <div class="flex gap-2">
                <input v-model="couponCode" type="text" placeholder="Nhập mã giảm giá"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#81AACC]">
                <button @click="applyCoupon"
                    class="px-4 py-2 bg-[#81AACC] text-white rounded-md hover:bg-[#6387A6] cursor-pointer">
                    Áp dụng
                </button>
            </div>
            <div v-if="availableCoupons.length > 0" class="mt-4">
                <h3 class="text-sm font-medium mb-2">Mã giảm giá đã lưu:</h3>
                <div class="max-h-[300px] overflow-y-auto pr-2 space-y-3">
                    <div v-for="coupon in availableCoupons" :key="coupon.id"
                        class="bg-white shadow-sm rounded-sm flex cursor-pointer hover:shadow-md transition"
                        @click="selectCoupon(coupon)">
                        <div class="flex-shrink-0 bg-[#81AACC] flex items-center justify-center p-4 rounded-l-sm">
                            <img src="https://ngthanhvu.github.io/ticket-sale-svgrepo-com.svg" alt="Coupon ticket icon"
                                class="w-12 h-12 object-contain text-white" width="48" height="48" />
                        </div>
                        <div class="flex flex-col justify-center px-4 py-3 flex-grow">
                            <p class="text-sm font-semibold text-[#81AACC]">
                                NHẬP MÃ:
                                <span class="font-normal">{{ coupon.code }}</span>
                            </p>
                            <p class="text-xs text-gray-500 mt-1 leading-tight">
                                {{ coupon.name }}
                            </p>
                            <div class="flex items-center mt-2">
                                <span class="text-sm font-medium text-[#81AACC]">
                                    {{ coupon.type === 'percent' ? `${coupon.value}%` : formatPrice(coupon.value) }}
                                </span>
                                <span v-if="coupon.min_order_value" class="text-xs text-gray-500 ml-2">
                                    (Đơn tối thiểu {{ formatPrice(coupon.min_order_value) }})
                                </span>
                            </div>
                            <div class="flex items-center mt-1">
                                <span class="text-xs text-green-600">
                                    <i class="fa-solid fa-check mr-1"></i>Có thể sử dụng
                                </span>
                                <span class="text-xs text-gray-500 ml-2">
                                    Hạn: {{ formatDate(coupon.end_date) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else>
                <div class="text-center">
                    <p class="text-sm text-gray-500 text-center mt-5">Không có mã giảm giá nào.</p>
                    <router-link to="/kho-voucher" class="text-[12px] text-gray-500 text-center hover:text-[#81aacc]">Ấn
                        vào đây để săn
                        mã
                        giảm
                        giá</router-link>
                </div>
            </div>
        </div>

        <!-- Thông tin vận chuyển -->
        <div class="mb-6">
            <ShippingSection 
                :cart-items="cartItems" 
                :selected-address="selectedAddress"
                @shipping-calculated="handleShippingCalculated" 
            />
        </div>
        <div class="space-y-3 border-t border-gray-300 pt-4">
            <div class="flex justify-between">
                <span>Tạm tính</span>
                <span>{{ formatPrice(subtotal) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Phí vận chuyển <span v-if="shippingZone" class="text-xs text-gray-500">({{ shippingZone }})</span></span>
                <span>{{ formatPrice(shipping) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Giảm giá</span>
                <span class="text-red-500">-{{ formatPrice(discount) }}</span>
            </div>
            <div class="flex justify-between font-bold text-lg border-t border-gray-300 pt-3">
                <span>Tổng cộng</span>
                <span>{{ formatPrice(total) }}</span>
            </div>
        </div>
        <button @click="$emit('place-order')"
            class="w-full mt-6 px-6 py-3 bg-[#81AACC] text-white rounded-md hover:bg-[#6387A6] font-medium cursor-pointer">
            Đặt hàng
        </button>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useCoupon } from '../../composable/useCoupon' // thay '~/composables' bằng relative path
import ShippingSection from './ShippingSection.vue'

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
    },
    shippingZone: {
        type: String,
        default: ''
    },
    selectedAddress: {
        type: Object,
        default: null
    },
    cartItems: {
        type: Array,
        required: true
    }
})

const emit = defineEmits(['apply-coupon', 'place-order', 'shipping-calculated'])

const couponCode = ref('')
const availableCoupons = ref([])
const couponService = useCoupon()

// ⚠️ Tự khai báo base URL (lấy từ .env qua Vite)
const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || ''

const total = computed(() => props.subtotal + props.shipping - props.discount)

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

const handleShippingCalculated = (shippingData) => {
    emit('shipping-calculated', shippingData)
}

const fetchAvailableCoupons = async () => {
    try {
        const myCouponsData = await couponService.getMyCoupons()
        const myCoupons = myCouponsData?.coupons || []

        if (!Array.isArray(myCoupons)) {
            return
        }

        const now = new Date()
        availableCoupons.value = myCoupons.filter((coupon) => {
            return (
                coupon.is_active &&
                coupon.pivot?.status !== 'used' &&
                new Date(coupon.start_date) <= now &&
                new Date(coupon.end_date) >= now
            )
        })
    } catch (error) {
        // Handle error silently
    }
}

const getImageUrl = (path) => {
    if (!path) return '/default-image.jpg'
    if (path.startsWith('http://') || path.startsWith('https://')) return path

    const base = apiBaseUrl.replace(/\/$/, '')

    if (path.startsWith('/storage/')) return base + path
    if (path.startsWith('storage/')) return `${base}/${path}`

    return `${base}/${path}`
}

const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    try {
        return new Date(dateString).toLocaleDateString('vi-VN')
    } catch {
        return 'N/A'
    }
}

onMounted(() => {
    fetchAvailableCoupons()
})
</script>
