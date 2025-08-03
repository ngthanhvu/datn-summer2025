<template>
    <div>
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Mã Giảm Giá Mới Nhất</h2>
            <router-link to="/kho-voucher"
                class="text-blue-600 hover:text-blue-700 font-medium flex items-center gap-2">
                Xem tất cả
                <i class="fa-solid fa-arrow-right"></i>
            </router-link>
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div v-for="i in 4" :key="i" class="flex max-w-xs w-full bg-white shadow-md rounded-md animate-pulse">
                <div class="left-edge bg-gray-300"></div>
                <div class="flex flex-col justify-center px-4 py-4 flex-1 space-y-2">
                    <div class="h-4 bg-gray-300 rounded"></div>
                    <div class="h-3 bg-gray-300 rounded"></div>
                    <div class="h-6 bg-gray-300 rounded w-20"></div>
                </div>
            </div>
        </div>

        <!-- Coupons -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div v-for="coupon in latestCoupons" :key="coupon.id"
                class="flex max-w-xs w-full bg-white shadow-md rounded-md"
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
                        <div v-else>Giảm {{ formatCurrency(coupon.value) }}</div>
                        <div v-if="coupon.min_order_value > 0">
                            Đơn tối thiểu: {{ formatCurrency(coupon.min_order_value) }}
                        </div>
                    </div>

                    <div class="mt-3 flex items-center justify-between">
                        <button v-if="getCouponStatus(coupon) === 'active' && !coupon.is_claimed"
                            @click="claimVoucherCode(coupon.id)" :disabled="claimingCouponId === coupon.id"
                            class="bg-blue-600 text-white text-xs px-3 py-1 rounded-sm hover:bg-blue-700 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                            <span v-if="claimingCouponId === coupon.id">
                                <i class="fa-solid fa-spinner fa-spin mr-1"></i>Đang lưu...
                            </span>
                            <span v-else>Lấy ngay</span>
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
const { getMyCoupons, claimCoupon } = useCoupon();

const couponStore = useCouponStore()
const isLoading = ref(false)
const claimingCouponId = ref(null)
const myCoupons = ref([])

onMounted(async () => {
    try {
        await couponStore.fetchCoupons()
        const myCouponsData = await getMyCoupons()
        myCoupons.value = Array.isArray(myCouponsData) ? myCouponsData : (myCouponsData?.data || myCouponsData?.coupons || [])
    } catch (error) {
        console.error('Error loading coupons:', error)
        push.error('Có lỗi xảy ra khi tải mã giảm giá')
    }
})

// const latestCoupons = computed(() => {
//     const allCoupons = couponStore.coupons || []
//     const latest = allCoupons
//         .filter(coupon => coupon.is_active)
//         .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
//         .slice(0, 4)

//     const claimedIds = myCoupons.value.map(c => c.coupon_id || c.id)
//     latest.forEach(coupon => {
//         coupon.is_claimed = claimedIds.includes(coupon.id)
//     })

//     return latest
// })

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
        push.error("Có lỗi xảy ra khi lưu mã giảm giá!")
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
