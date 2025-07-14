<template>
    <div>
        <!-- Header with View All button -->
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
            <h2 class="tw-text-2xl tw-font-bold tw-text-gray-800">Mã Giảm Giá Mới Nhất</h2>
            <NuxtLink to="/kho-voucher"
                class="tw-text-blue-600 hover:tw-text-blue-700 tw-font-medium tw-flex tw-items-center tw-gap-2">
                Xem tất cả
                <i class="fa-solid fa-arrow-right"></i>
            </NuxtLink>
        </div>

        <!-- Loading State -->
        <div v-if="homeStore.isLoadingCoupons"
            class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6">
            <div v-for="i in 4" :key="i"
                class="tw-flex tw-max-w-xs tw-w-full tw-bg-white tw-shadow-md tw-rounded-md tw-animate-pulse">
                <div class="tw-left-edge tw-bg-gray-300"></div>
                <div class="tw-flex tw-flex-col tw-justify-center tw-px-4 tw-py-4 tw-flex-1 tw-space-y-2">
                    <div class="tw-h-4 tw-bg-gray-300 tw-rounded"></div>
                    <div class="tw-h-3 tw-bg-gray-300 tw-rounded"></div>
                    <div class="tw-h-6 tw-bg-gray-300 tw-rounded tw-w-20"></div>
                </div>
            </div>
        </div>

        <!-- Coupons Grid -->
        <div v-else class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-6">
            <div v-for="coupon in latestCoupons" :key="coupon.id"
                class="tw-flex tw-max-w-xs tw-w-full tw-bg-white tw-shadow-md tw-rounded-md"
                :class="{ 'tw-opacity-60': getCouponStatus(coupon) !== 'active' }">

                <div class="tw-left-edge tw-flex tw-items-center tw-justify-center">
                    <i class="fa-solid fa-ticket tw-text-white tw-text-2xl"></i>
                </div>

                <div class="tw-flex tw-flex-col tw-justify-center tw-px-4 tw-py-4 tw-flex-1">
                    <p class="tw-text-sm tw-text-blue-600 tw-font-semibold tw-text-center">
                        NHẬP MÃ:
                        <span class="tw-font-normal">{{ coupon.code || 'N/A' }}</span>
                    </p>
                    <p class="tw-text-xs tw-text-gray-500 tw-text-center tw-mt-1 tw-leading-tight">
                        {{ coupon.description || coupon.name || 'Không có mô tả' }}
                    </p>

                    <div class="tw-mt-2 tw-text-xs tw-text-gray-600 tw-text-center">
                        <div v-if="coupon.type === 'percent'">
                            Giảm {{ coupon.value || 0 }}%
                            <span v-if="coupon.max_discount_value">tối đa {{ formatCurrency(coupon.max_discount_value)
                            }}</span>
                        </div>
                        <div v-else>
                            Giảm {{ formatCurrency(coupon.value || 0) }}
                        </div>
                        <div v-if="coupon.min_order_value > 0">
                            Đơn tối thiểu: {{ formatCurrency(coupon.min_order_value) }}
                        </div>
                    </div>

                    <div class="tw-mt-3 tw-flex tw-items-center tw-justify-between">
                        <button v-if="getCouponStatus(coupon) === 'active' && !coupon.is_claimed"
                            @click="claimVoucherCode(coupon.id)"
                            class="tw-bg-blue-600 tw-text-white tw-text-xs tw-px-3 tw-py-1 tw-rounded-sm hover:tw-bg-blue-700 tw-transition">
                            Lấy ngay
                        </button>
                        <button v-else-if="getCouponStatus(coupon) === 'active' && coupon.is_claimed" disabled
                            class="tw-bg-gray-300 tw-text-white tw-text-xs tw-px-3 tw-py-1 tw-rounded-sm tw-cursor-not-allowed">
                            Đã lưu
                        </button>
                        <button v-else disabled
                            class="tw-bg-gray-400 tw-text-white tw-text-xs tw-px-3 tw-py-1 tw-rounded-sm tw-cursor-not-allowed">
                            {{ getCouponStatus(coupon) === 'expired' ? 'Đã hết hạn' :
                                getCouponStatus(coupon) === 'used' ? 'Đã sử dụng' : 'Không hoạt động' }}
                        </button>
                        <div class="tw-text-xs tw-text-gray-700 hover:tw-underline tw-cursor-pointer">
                            <div>Hạn: {{ formatDate(coupon.end_date) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!homeStore.isLoadingCoupons && latestCoupons.length === 0" class="tw-text-center tw-py-8">
            <i class="fa-solid fa-ticket tw-text-4xl tw-text-gray-400 tw-mb-4"></i>
            <p class="tw-text-gray-500">Không có voucher nào</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useHomeStore } from '~/stores/useHomeStore'
import { useCoupon } from '~/composables/useCoupon'

const notyf = useNuxtApp().$notyf
const homeStore = useHomeStore()
const { getMyCoupons, claimCoupon } = useCoupon()

const myCoupons = ref([])

// Computed property for latest coupons
const latestCoupons = computed(() => {
    const allCoupons = homeStore.coupons || []
    const latest = allCoupons
        .filter(coupon => coupon.is_active)
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        .slice(0, 4)

    // Mark claimed coupons
    const claimedIds = myCoupons.value.map(c => c.id)
    latest.forEach(coupon => {
        coupon.is_claimed = claimedIds.includes(coupon.id)
    })

    return latest
})

const claimVoucherCode = async (couponId) => {
    try {
        const result = await claimCoupon(couponId)
        console.log(`Đã lấy mã voucher thành công:`, result)

        // Fetch lại danh sách my-coupons để cập nhật trạng thái
        const myCouponsData = await getMyCoupons()
        myCoupons.value = myCouponsData?.coupons || []

        notyf.success('Đã lưu mã voucher thành công!')
    } catch (err) {
        console.error('Không thể lấy mã voucher:', err)
        notyf.error('Có lỗi xảy ra khi lấy mã voucher!')
    }
}

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

// XÓA toàn bộ onMounted fetch data, chỉ lấy state từ store
</script>

<style scoped>
/* Custom shape for left edge with small semicircle cutouts */
.tw-left-edge {
    position: relative;
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

/* The above polygon creates a scalloped left edge with 4 semicircle cutouts */
</style>
