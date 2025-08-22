<template>
    <div>
        <!-- Header -->
        <div class="flex justify-between items-center mb-4 md:mb-6">
            <h2 class="text-lg md:text-2xl font-semibold text-gray-800">Mã Giảm Giá Mới Nhất</h2>
            <router-link to="/kho-voucher"
                class="text-blue-600 hover:text-blue-700 font-medium flex items-center gap-2 text-sm md:text-base">
                Xem tất cả
                <i class="fa-solid fa-arrow-right"></i>
            </router-link>
        </div>

        <!-- Login Prompt for non-logged users -->
        <div v-if="!isLoggedIn" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-info-circle text-blue-600 text-lg"></i>
                    <div>
                        <p class="text-blue-800 font-medium">Đăng nhập để nhận mã giảm giá</p>
                        <p class="text-blue-600 text-sm">Bạn cần đăng nhập hoặc đăng ký để có thể lưu và sử dụng các mã
                            giảm giá</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <router-link to="/login"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition text-sm font-medium">
                        Đăng nhập
                    </router-link>
                    <router-link to="/register"
                        class="bg-white text-blue-600 border border-blue-600 px-4 py-2 rounded-md hover:bg-blue-50 transition text-sm font-medium">
                        Đăng ký
                    </router-link>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="isLoading"
            class="flex gap-4 overflow-x-auto scroll-smooth md:grid md:grid-cols-1 md:sm:grid-cols-2 md:lg:grid-cols-4 md:gap-6">
            <div v-for="i in 4" :key="i"
                class="flex max-w-xs w-80 md:w-full bg-white shadow-md rounded-md animate-pulse flex-shrink-0">
                <div class="left-edge bg-gray-300"></div>
                <div class="flex flex-col justify-center px-4 py-4 flex-1 space-y-2">
                    <div class="h-4 bg-gray-300 rounded"></div>
                    <div class="h-3 bg-gray-300 rounded"></div>
                    <div class="h-6 bg-gray-300 rounded w-20"></div>
                </div>
            </div>
        </div>

        <!-- Coupons -->
        <div v-else
            class="flex gap-4 overflow-x-auto scroll-smooth md:grid md:grid-cols-1 md:sm:grid-cols-2 md:lg:grid-cols-4 md:gap-6">
            <div v-for="coupon in latestCoupons" :key="coupon.id"
                class="flex max-w-xs w-80 md:w-full bg-white shadow-md rounded-md flex-shrink-0"
                :class="{ 'opacity-60': getCouponStatus(coupon) !== 'active' }">
                <div class="left-edge flex items-center justify-center">
                    <i class="fa-solid fa-ticket text-white text-2xl"></i>
                </div>

                <div class="flex flex-col justify-center px-4 py-4 flex-1">
                    <p class="text-sm text-blue-600 font-semibold text-center">
                        NHẬP MÃ: <span class="font-normal">{{ coupon.code || 'N/A' }}</span>
                    </p>
                    <p class="text-xs text-gray-500 text-center mt-1 leading-tight">
                        {{ coupon.description || coupon.name || 'Không có mô tả' }}
                    </p>

                    <div class="mt-2 text-xs text-gray-600 text-center">
                        <div v-if="coupon.type === 'percent'">
                            Giảm {{ coupon.value }}%
                            <span v-if="coupon.max_discount_value">
                                tối đa {{ formatCurrency(coupon.max_discount_value) }}
                            </span>
                        </div>
                        <div v-else-if="coupon.type === 'shipping'">Miễn ship</div>
                        <div v-else>Giảm {{ formatCurrency(coupon.value) }}</div>
                        <div v-if="coupon.min_order_value > 0">
                            Đơn tối thiểu: {{ formatCurrency(coupon.min_order_value) }}
                        </div>
                    </div>

                    <div class="mt-3 flex items-center justify-between">
                        <button v-if="getCouponStatus(coupon) === 'active' && !coupon.is_claimed && isLoggedIn"
                            @click="claimVoucherCode(coupon.id)" :disabled="claimingCouponId === coupon.id"
                            class="bg-blue-600 text-white text-xs px-3 py-1 rounded-sm hover:bg-blue-700 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                            <span v-if="claimingCouponId === coupon.id">
                                <i class="fa-solid fa-spinner fa-spin mr-1"></i>Đang lưu...
                            </span>
                            <span v-else>Lấy ngay</span>
                        </button>
                        <button v-else-if="getCouponStatus(coupon) === 'active' && !coupon.is_claimed && !isLoggedIn"
                            @click="claimVoucherCode(coupon.id)"
                            class="bg-orange-500 text-white text-xs px-3 py-1 rounded-sm hover:bg-orange-600 transition cursor-pointer">
                            Đăng nhập để lấy
                        </button>
                        <button v-else-if="getCouponStatus(coupon) === 'active' && coupon.is_claimed" disabled
                            class="bg-gray-300 text-white text-xs px-3 py-1 rounded-sm cursor-not-allowed">
                            Đã lưu
                        </button>
                        <button v-else disabled
                            class="bg-gray-400 text-white text-xs px-3 py-1 rounded-sm cursor-not-allowed">
                            {{
                                getCouponStatus(coupon) === 'expired'
                                    ? 'Đã hết hạn'
                                    : getCouponStatus(coupon) === 'used'
                                        ? 'Đã sử dụng'
                                        : 'Không hoạt động'
                            }}
                        </button>

                        <div class="text-xs text-gray-700 hover:underline cursor-pointer">
                            Hạn: {{ formatDate(coupon.end_date) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!isLoading && latestCoupons.length === 0" class="text-center py-8">
            <i class="fa-solid fa-ticket text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-500">Không có voucher nào</p>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useCouponStore } from '../../stores/coupons'
import { useCoupon } from '../../composable/useCoupon'
import { push } from 'notivue'
import Cookies from 'js-cookie'

const { getMyCoupons, claimCoupon } = useCoupon();

const couponStore = useCouponStore()
const isLoading = ref(false)
const claimingCouponId = ref(null)
const myCoupons = ref([])

const isLoggedIn = computed(() => {
    return !!Cookies.get('token')
})

onMounted(async () => {
    isLoading.value = true
    try {
        await couponStore.fetchCoupons()
        const myCouponsData = await getMyCoupons()
        myCoupons.value = Array.isArray(myCouponsData) ? myCouponsData : (myCouponsData?.data || myCouponsData?.coupons || [])
    } catch (error) {
        console.error('Error loading coupons:', error)
    } finally {
        isLoading.value = false
    }
})

const latestCoupons = computed(() => {
    const allCoupons = couponStore.coupons || []
    const claimedIds = myCoupons.value.map(c => c.coupon_id || c.id)

    return allCoupons
        .filter(coupon => coupon.is_active && !isExpired(coupon)) // bỏ mã hết hạn
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        .slice(0, 4)
        .map(coupon => ({
            ...coupon,
            is_claimed: claimedIds.includes(coupon.id)
        }))
})

const formatCurrency = (amount) => {
    if (!amount || isNaN(amount)) return '0 ₫'
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount)
}

const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    try {
        return new Date(dateString).toLocaleDateString('vi-VN')
    } catch (error) {
        return 'N/A'
    }
}

const isExpired = (coupon) => {
    if (!coupon || !coupon.end_date) return false
    const now = new Date()
    const endDate = new Date(coupon.end_date)
    return now > endDate
}

const isUsedUp = (coupon) => {
    if (!coupon || !coupon.usage_limit) return false
    return coupon.used_count >= coupon.usage_limit
}

const getCouponStatus = (coupon) => {
    if (!coupon) return 'inactive'
    if (isExpired(coupon)) return 'expired'
    if (isUsedUp(coupon)) return 'used'
    if (!coupon.is_active) return 'inactive'
    return 'active'
}

const claimVoucherCode = async (couponId) => {
    if (claimingCouponId.value) return // Prevent multiple clicks

    try {
        claimingCouponId.value = couponId
        await claimCoupon(couponId)
        // Refresh my coupons directly from composable
        const myCouponsData = await getMyCoupons()
        myCoupons.value = Array.isArray(myCouponsData) ? myCouponsData : (myCouponsData?.data || myCouponsData?.coupons || [])
        push.success("Lưu mã giảm giá thành công!")
    } catch (error) {
        console.log(error)
        if (error.message === 'Vui lòng đăng nhập / đăng ký để nhận coupon') {
            push.error("Vui lòng đăng nhập / đăng ký để nhận coupon")
        } else {
            push.error("Có lỗi xảy ra khi lưu mã giảm giá!")
        }
    } finally {
        claimingCouponId.value = null
    }
}
</script>

<style scoped>
.left-edge {
    width: 64px;
    background-color: #1565d8;
    clip-path: polygon(0 0,
            100% 0,
            100% 100%,
            0 100%,
            0 85%,
            10% 85%,
            10% 70%,
            0 70%,
            0 55%,
            10% 55%,
            10% 40%,
            0 40%,
            0 25%,
            10% 25%,
            10% 10%,
            0 10%);
}
</style>
