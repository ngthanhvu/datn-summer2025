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
                <input v-model="couponCode" type="text" placeholder="Nhập mã giảm giá" :disabled="isCouponLoading"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#81AACC] disabled:opacity-50">
                <button type="button" @click="applyCoupon" :disabled="isCouponLoading"
                    class="px-4 py-2 bg-[#81AACC] text-white rounded-md hover:bg-[#6387A6] cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <div v-if="isCouponLoading" class="flex items-center gap-2">
                        <div class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></div>
                        <span>Đang áp dụng...</span>
                    </div>
                    <span v-else>Áp dụng</span>
                </button>
            </div>

            <!-- Display applied coupons only -->
            <div v-if="appliedShippingCoupon || appliedDiscountCoupon" class="mt-3 space-y-2">
                <div v-if="appliedShippingCoupon" class="flex items-center justify-between bg-green-50 p-2 rounded-md">
                    <div class="flex items-center gap-2">
                        <span class="text-green-600 text-sm">✓ Miễn phí vận chuyển: {{ appliedShippingCoupon.code
                        }}</span>
                    </div>
                    <button @click="$emit('remove-coupon', 'shipping')" class="text-red-500 hover:text-red-700 text-sm">
                        ✕
                    </button>
                </div>
                <div v-if="appliedDiscountCoupon" class="flex items-center justify-between bg-blue-50 p-2 rounded-md">
                    <div class="flex items-center gap-2">
                        <span class="text-blue-600 text-sm">✓ Giảm giá: {{ appliedDiscountCoupon.code }}</span>
                    </div>
                    <button @click="$emit('remove-coupon', 'discount')" class="text-red-500 hover:text-red-700 text-sm">
                        ✕
                    </button>
                </div>
            </div>

            <div class="mt-2 text-right">
                <button type="button" @click="openVoucherModal"
                    class="text-[#1a73e8] text-sm hover:underline cursor-pointer">Chọn hoặc nhập mã</button>
            </div>

            <!-- Display available coupons list -->
            <div v-if="availableCoupons.length > 0" class="mt-4">
                <h3 class="text-sm font-medium mb-2">Mã giảm giá đã lưu</h3>
                <div class="max-h-[200px] overflow-y-auto pr-2 space-y-2">
                    <div v-if="grouped.shipping.length > 0">
                        <div class="text-xs font-semibold text-gray-600 mb-2">Miễn phí vận chuyển</div>
                        <div v-for="coupon in grouped.shipping" :key="'s-' + coupon.id"
                            class="bg-white rounded-lg p-2 flex items-center gap-2 border border-gray-300 hover:ring-[#81AACC]/30 transition cursor-pointer"
                            @click="selectCoupon(coupon)">
                            <div class="flex-shrink-0 w-8 h-8 bg-[#81AACC] rounded-md flex items-center justify-center">
                                <img src="https://ngthanhvu.github.io/ticket-sale-svgrepo-com.svg"
                                    alt="Coupon ticket icon" class="w-5 h-5 object-contain" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm text-[#81AACC] font-semibold truncate">{{ coupon.code }}</div>
                                <div class="text-xs text-gray-500 truncate">{{ coupon.name }}</div>
                                <div class="text-xs text-gray-700">Miễn ship <span v-if="coupon.min_order_value">(Đơn
                                        tối thiểu {{
                                            formatPrice(coupon.min_order_value) }})</span></div>
                            </div>
                            <div class="text-xs text-gray-500">HSD: {{ formatDate(coupon.end_date) }}</div>
                        </div>
                    </div>
                    <div v-if="grouped.percent.length > 0">
                        <div class="text-xs font-semibold text-gray-600 mb-2">Giảm theo %</div>
                        <div v-for="coupon in grouped.percent" :key="'p-' + coupon.id"
                            class="bg-white rounded-lg p-2 flex items-center gap-2 border border-gray-300 hover:ring-[#81AACC]/30 transition cursor-pointer mb-2"
                            @click="selectCoupon(coupon)">
                            <div class="flex-shrink-0 w-8 h-8 bg-[#81AACC] rounded-md flex items-center justify-center">
                                <img src="https://ngthanhvu.github.io/ticket-sale-svgrepo-com.svg"
                                    alt="Coupon ticket icon" class="w-5 h-5 object-contain" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm text-[#81AACC] font-semibold truncate">{{ coupon.code }}</div>
                                <div class="text-xs text-gray-500 truncate">{{ coupon.name }}</div>
                                <div class="text-xs text-gray-700">Giảm {{ coupon.value }}%<span
                                        v-if="coupon.max_discount_value">, tối đa {{
                                            formatPrice(coupon.max_discount_value) }}</span></div>
                            </div>
                            <div class="text-xs text-gray-500">HSD: {{ formatDate(coupon.end_date) }}</div>
                        </div>
                    </div>
                    <div v-if="grouped.fixed.length > 0">
                        <div class="text-xs font-semibold text-gray-600 mb-2">Giảm số tiền</div>
                        <div v-for="coupon in grouped.fixed" :key="'f-' + coupon.id"
                            class="bg-white rounded-lg p-2 flex items-center gap-2 border border-gray-300 hover:ring-[#81AACC]/30 transition cursor-pointer mb-2"
                            @click="selectCoupon(coupon)">
                            <div class="flex-shrink-0 w-8 h-8 bg-[#81AACC] rounded-md flex items-center justify-center">
                                <img src="https://ngthanhvu.github.io/ticket-sale-svgrepo-com.svg"
                                    alt="Coupon ticket icon" class="w-5 h-5 object-contain" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm text-[#81AACC] font-semibold truncate">{{ coupon.code }}</div>
                                <div class="text-xs text-gray-500 truncate">{{ coupon.name }}</div>
                                <div class="text-xs text-gray-700">Giảm {{ formatPrice(coupon.value) }}</div>
                            </div>
                            <div class="text-xs text-gray-500">HSD: {{ formatDate(coupon.end_date) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else>
                <div class="text-center">
                    <p class="text-xs text-gray-500 text-center mt-3">Không có mã giảm giá nào.</p>
                    <router-link to="/kho-voucher" class="text-[11px] text-gray-500 text-center hover:text-[#81aacc]">Ấn
                        vào đây để săn mã giảm giá</router-link>
                </div>
            </div>
        </div>

        <!-- Voucher Modal -->
        <div v-if="showVoucherModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-[10000]">
            <div class="bg-white w-full max-w-2xl rounded-md shadow-lg p-4 md:p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold">Chọn mã giảm giá</h3>
                    <button @click="closeVoucherModal" class="text-gray-500 hover:text-gray-700">✕</button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-[60vh] overflow-y-auto pr-2">
                    <!-- Loading state -->
                    <div v-if="isModalLoading" class="col-span-2 flex items-center justify-center py-8">
                        <div class="flex items-center gap-2">
                            <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-[#81AACC]"></div>
                            <span class="text-gray-600">Đang tải mã giảm giá...</span>
                        </div>
                    </div>

                    <!-- Shipping group -->
                    <div v-else>
                        <div class="text-sm font-semibold text-gray-700 mb-2">Miễn phí vận chuyển (chọn 1)</div>
                        <div v-if="grouped.shipping.length === 0" class="text-xs text-gray-400 mb-2">Không có mã phù hợp
                        </div>
                        <label v-for="coupon in grouped.shipping" :key="'ms-' + coupon.id"
                            class="flex items-start border border-gray-300 gap-3 p-3 rounded-lg mb-2 mr-[1px] cursor-pointer bg-white hover:shadow-md hover:ring-1 hover:ring-[#81AACC]/30">
                            <input type="radio" name="shippingCoupon" :value="coupon.id" v-model="selectedShippingId">
                            <div
                                class="flex-shrink-0 w-10 h-10 bg-[#81AACC] rounded-md flex items-center justify-center">
                                <img src="https://ngthanhvu.github.io/ticket-sale-svgrepo-com.svg"
                                    alt="Coupon ticket icon" class="w-6 h-6 object-contain" width="24" height="24" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-[#81AACC] truncate">{{ coupon.code }}</div>
                                <div class="text-xs text-gray-500 truncate">{{ coupon.name }}</div>
                                <div class="text-xs mt-1">Miễn ship <span v-if="coupon.min_order_value">(Đơn tối thiểu
                                        {{ formatPrice(coupon.min_order_value) }})</span></div>
                                <div class="text-[11px] text-gray-500">HSD: {{ formatDate(coupon.end_date) }}</div>
                            </div>
                        </label>
                    </div>

                    <!-- Discount group (percent/fixed) -->
                    <div v-else>
                        <div class="text-sm font-semibold text-gray-700 mb-2">Giảm giá (chọn 1)</div>
                        <div v-if="grouped.percent.length + grouped.fixed.length === 0"
                            class="text-xs text-gray-400 mb-2">Không có mã phù hợp</div>
                        <label v-for="coupon in [...grouped.percent, ...grouped.fixed]" :key="'md-' + coupon.id"
                            class="flex items-start border border-gray-300 gap-3 p-3 rounded-lg mb-2 cursor-pointer bg-white hover:shadow-md hover:ring-1 hover:ring-[#81AACC]/30">
                            <input type="radio" name="discountCoupon" :value="coupon.id" v-model="selectedDiscountId">
                            <div
                                class="flex-shrink-0 w-10 h-10 bg-[#81AACC] rounded-md flex items-center justify-center">
                                <img src="https://ngthanhvu.github.io/ticket-sale-svgrepo-com.svg"
                                    alt="Coupon ticket icon" class="w-6 h-6 object-contain" width="24" height="24" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-[#81AACC] truncate">{{ coupon.code }}</div>
                                <div class="text-xs text-gray-500 truncate">{{ coupon.name }}</div>
                                <div class="text-xs mt-1">
                                    <span v-if="coupon.type === 'percent'">Giảm {{ coupon.value }}% <span
                                            v-if="coupon.max_discount_value">(tối đa {{
                                                formatPrice(coupon.max_discount_value) }})</span></span>
                                    <span v-else>Giảm {{ formatPrice(coupon.value) }}</span>
                                    <span v-if="coupon.min_order_value"> - Đơn tối thiểu {{
                                        formatPrice(coupon.min_order_value) }}</span>
                                </div>
                                <div class="text-[11px] text-gray-500">HSD: {{ formatDate(coupon.end_date) }}</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button type="button"
                        class="px-4 py-2 text-sm border border-gray-300 cursor-pointer rounded-md hover:bg-gray-50"
                        @click="closeVoucherModal">Trở lại</button>
                    <button type="button"
                        class="px-4 py-2 text-sm bg-[#81AACC] text-white rounded-md hover:bg-[#6387A6] cursor-pointer"
                        @click="applySelectedCoupons">Áp dụng</button>
                </div>
            </div>
        </div>

        <!-- Thông tin vận chuyển -->
        <div class="mb-6">
            <ShippingSection ref="shippingSectionRef" :cart-items="cartItems" :selected-address="selectedAddress"
                :free-shipping="freeShipping" @shipping-calculated="handleShippingCalculated" />
        </div>

        <div class="space-y-3 border-t border-gray-300 pt-4">
            <div class="flex justify-between">
                <span>Tạm tính</span>
                <span>{{ formatPrice(subtotal) }}</span>
            </div>
            <div class="flex justify-between">
                <span>Phí vận chuyển <span v-if="shippingZone" class="text-xs text-gray-500">({{ shippingZone
                        }})</span></span>
                <span v-if="shippingLoading" class="flex items-center">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600 mr-2"></div>
                    Đang tính...
                </span>
                <span v-else>{{ formatPrice(shipping) }}</span>
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
        <button @click="$emit('place-order')" :disabled="isPlacingOrder"
            class="w-full mt-6 px-6 py-3 bg-[#81AACC] text-white rounded-md hover:bg-[#6387A6] font-medium cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
            <div v-if="isPlacingOrder" class="flex items-center justify-center gap-2">
                <div class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></div>
                <span>Đang đặt hàng...</span>
            </div>
            <span v-else>Đặt hàng</span>
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
    freeShipping: {
        type: Boolean,
        default: false
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
    },
    shippingLoading: {
        type: Boolean,
        default: false
    },
    isPlacingOrder: {
        type: Boolean,
        default: false
    },
    appliedShippingCoupon: {
        type: Object,
        default: null
    },
    appliedDiscountCoupon: {
        type: Object,
        default: null
    },
    isCouponLoading: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['apply-coupon', 'apply-coupons', 'place-order', 'shipping-calculated', 'remove-coupon'])

const couponCode = ref('')
const availableCoupons = ref([])
const couponService = useCoupon()
const shippingSectionRef = ref(null)

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
        // Clear the input after applying
        couponCode.value = ''
    }
}

const selectCoupon = (coupon) => {
    couponCode.value = coupon.code
    applyCoupon()
}

const handleShippingCalculated = (shippingData) => {
    emit('shipping-calculated', shippingData)
}

const forceShippingCalculation = () => {
    if (shippingSectionRef.value) {
        shippingSectionRef.value.handleShippingCalculation()
    }
}

// Add method to handle free shipping application
const handleFreeShippingApplied = (isFree) => {
    // When free shipping is applied, we should still calculate shipping
    // but set the fee to 0 for display purposes
    if (isFree && shippingSectionRef.value) {
        // Force shipping calculation to get zone info, but fee will be 0
        shippingSectionRef.value.handleShippingCalculation()
    }
}

defineExpose({
    forceShippingCalculation,
    handleFreeShippingApplied
})

const grouped = computed(() => {
    const s = []
    const p = []
    const f = []
    for (const c of availableCoupons.value) {
        if (c.type === 'shipping') s.push(c)
        else if (c.type === 'percent') p.push(c)
        else f.push(c)
    }
    return { shipping: s, percent: p, fixed: f }
})

// Modal state
const showVoucherModal = ref(false)
const selectedShippingId = ref(null)
const selectedDiscountId = ref(null)
const isModalLoading = ref(false)

const openVoucherModal = async () => {
    // Only fetch coupons when modal is opened
    if (availableCoupons.value.length === 0) {
        isModalLoading.value = true
        try {
            const myCouponsData = await couponService.getMyCoupons()
            const myCoupons = myCouponsData?.coupons || []

            if (Array.isArray(myCoupons)) {
                const now = new Date()
                availableCoupons.value = myCoupons.filter((coupon) => {
                    return (
                        coupon.is_active &&
                        coupon.pivot?.status !== 'used' &&
                        new Date(coupon.start_date) <= now &&
                        new Date(coupon.end_date) >= now
                    )
                })
            }
        } catch (error) {
            // Failed to fetch coupons
        } finally {
            isModalLoading.value = false
        }
    }

    showVoucherModal.value = true
}

const closeVoucherModal = () => {
    showVoucherModal.value = false
}

const applySelectedCoupons = () => {
    const selected = []
    if (selectedShippingId.value) {
        const sc = grouped.value.shipping.find(c => c.id === selectedShippingId.value)
        if (sc) selected.push(sc.code)
    }
    if (selectedDiscountId.value) {
        // Could be from percent or fixed list
        const dc = [...grouped.value.percent, ...grouped.value.fixed].find(c => c.id === selectedDiscountId.value)
        if (dc) selected.push(dc.code)
    }

    if (selected.length === 0) {
        showVoucherModal.value = false
        return
    }

    // Emit in order: freeship first (safe) then discount
    emit('apply-coupons', selected)
    showVoucherModal.value = false
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

onMounted(async () => {
    // Fetch available coupons when component mounts
    try {
        const myCouponsData = await couponService.getMyCoupons()
        const myCoupons = myCouponsData?.coupons || []

        if (Array.isArray(myCoupons)) {
            const now = new Date()
            availableCoupons.value = myCoupons.filter((coupon) => {
                return (
                    coupon.is_active &&
                    coupon.pivot?.status !== 'used' &&
                    new Date(coupon.start_date) <= now &&
                    new Date(coupon.end_date) >= now
                )
            })
        }
    } catch (error) {
        console.error('Failed to fetch coupons:', error)
    }
})
</script>
