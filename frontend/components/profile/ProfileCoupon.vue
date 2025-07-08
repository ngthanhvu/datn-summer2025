<template>
    <div>
        <!-- Header Section -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6 tw-mb-6">
            <h1 class="tw-text-3xl tw-font-bold tw-text-gray-800 tw-mb-2">Voucher Đã Lưu</h1>
            <p class="tw-text-gray-600">Danh sách các mã giảm giá bạn đã lưu</p>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-8 tw-text-center">
            <div
                class="tw-animate-spin tw-rounded-full tw-h-8 tw-w-8 tw-border-b-2 tw-border-blue-600 tw-mx-auto tw-mb-4">
            </div>
            <p class="tw-text-gray-500">Đang tải voucher...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="!loading && myCoupons.length === 0"
            class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-8 tw-text-center">
            <i class="fa-solid fa-ticket tw-text-4xl tw-text-gray-400 tw-mb-4"></i>
            <p class="tw-text-gray-500 tw-mb-2">Bạn chưa lưu voucher nào</p>
            <NuxtLink to="/kho-voucher" class="tw-text-blue-600 hover:tw-underline">
                Khám phá voucher mới →
            </NuxtLink>
        </div>

        <!-- Coupons List -->
        <div v-else class="tw-space-y-4">
            <div v-for="coupon in myCoupons" :key="coupon.id" class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6"
                :class="{ 'tw-opacity-60': getCouponStatus(coupon) !== 'active' }">

                <div class="tw-flex tw-items-start tw-justify-between">
                    <!-- Coupon Info -->
                    <div class="tw-flex-1">
                        <div class="tw-flex tw-items-center tw-gap-3 tw-mb-3">
                            <div class="tw-bg-blue-100 tw-p-2 tw-rounded-full">
                                <i class="fa-solid fa-ticket tw-text-blue-600"></i>
                            </div>
                            <div>
                                <h3 class="tw-font-semibold tw-text-gray-800">{{ coupon.name }}</h3>
                                <p class="tw-text-sm tw-text-gray-500">{{ coupon.code }}</p>
                            </div>
                        </div>

                        <p class="tw-text-sm tw-text-gray-600 tw-mb-3">
                            {{ coupon.description || 'Không có mô tả' }}
                        </p>

                        <div class="tw-flex tw-flex-wrap tw-gap-4 tw-mb-4">
                            <div class="tw-bg-gray-50 tw-px-3 tw-py-1 tw-rounded-full">
                                <span class="tw-text-xs tw-text-gray-600">
                                    <i class="fa-solid fa-calendar tw-mr-1"></i>
                                    Hạn sử dụng: {{ formatDate(coupon.end_date) }}
                                </span>
                            </div>
                            <div class="tw-bg-gray-50 tw-px-3 tw-py-1 tw-rounded-full">
                                <span class="tw-text-xs tw-text-gray-600">
                                    <i class="fa-solid fa-clock tw-mr-1"></i>
                                    Lưu lúc: {{ formatDate(coupon.pivot?.claimed_at) }}
                                </span>
                            </div>
                        </div>

                        <!-- Discount Info -->
                        <div class="tw-bg-blue-50 tw-p-3 tw-rounded-lg tw-mb-4">
                            <div class="tw-flex tw-items-center tw-justify-between">
                                <div>
                                    <span class="tw-text-lg tw-font-bold tw-text-blue-600">
                                        {{ coupon.type === 'percent' ? `${coupon.value}%` : formatCurrency(coupon.value)
                                        }}
                                    </span>
                                    <span class="tw-text-sm tw-text-gray-600 tw-ml-2">
                                        {{ coupon.type === 'percent' ? 'giảm giá' : 'giảm cố định' }}
                                    </span>
                                </div>
                                <div class="tw-text-right">
                                    <div class="tw-text-xs tw-text-gray-500">Đơn tối thiểu</div>
                                    <div class="tw-text-sm tw-font-medium">{{ formatCurrency(coupon.min_order_value) }}
                                    </div>
                                </div>
                            </div>
                            <div v-if="coupon.max_discount_value && coupon.type === 'percent'"
                                class="tw-text-xs tw-text-gray-500 tw-mt-1">
                                Giảm tối đa: {{ formatCurrency(coupon.max_discount_value) }}
                            </div>
                        </div>
                    </div>

                    <!-- Status & Actions -->
                    <div class="tw-flex tw-flex-col tw-items-end tw-gap-3">
                        <!-- Status Badge -->
                        <div class="tw-flex tw-items-center tw-gap-2">
                            <span v-if="coupon.pivot?.status === 'used'"
                                class="tw-bg-red-100 tw-text-red-800 tw-text-xs tw-px-2 tw-py-1 tw-rounded-full">
                                <i class="fa-solid fa-check tw-mr-1"></i>Đã sử dụng
                            </span>
                            <span v-else-if="getCouponStatus(coupon) === 'active'"
                                class="tw-bg-green-100 tw-text-green-800 tw-text-xs tw-px-2 tw-py-1 tw-rounded-full">
                                <i class="fa-solid fa-clock tw-mr-1"></i>Có thể sử dụng
                            </span>
                            <span v-else-if="getCouponStatus(coupon) === 'expired'"
                                class="tw-bg-gray-100 tw-text-gray-800 tw-text-xs tw-px-2 tw-py-1 tw-rounded-full">
                                <i class="fa-solid fa-times tw-mr-1"></i>Đã hết hạn
                            </span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="tw-flex tw-gap-2">
                            <button v-if="getCouponStatus(coupon) === 'active' && coupon.pivot?.status !== 'used'"
                                @click="copyCouponCode(coupon.code)"
                                class="tw-bg-blue-600 tw-text-white tw-text-sm tw-px-4 tw-py-2 tw-rounded-lg hover:tw-bg-blue-700 tw-transition">
                                <i class="fa-solid fa-copy tw-mr-1"></i>Sao chép mã
                            </button>
                            <button v-else disabled
                                class="tw-bg-gray-300 tw-text-gray-500 tw-text-sm tw-px-4 tw-py-2 tw-rounded-lg tw-cursor-not-allowed">
                                {{ coupon.pivot?.status === 'used' ? 'Đã sử dụng' : 'Không khả dụng' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useCouponStore } from '~/stores/useCouponStore'

const notyf = useNuxtApp().$notyf
const couponStore = useCouponStore()

const loading = computed(() => couponStore.isLoadingCoupons)

const myCoupons = computed(() => {
    // Giả sử couponStore.coupons có trường is_claimed hoặc pivot cho biết đã lưu
    return (couponStore.coupons || []).filter(coupon => coupon.is_claimed || coupon.pivot)
})

onMounted(async () => {
    if (!couponStore.coupons.length) {
        await couponStore.fetchCoupons()
    }
})

const copyCouponCode = async (code) => {
    try {
        await navigator.clipboard.writeText(code)
        notyf.success(`Đã sao chép mã: ${code}`)
    } catch (err) {
        console.error('Không thể sao chép mã:', err)
        notyf.error('Không thể sao chép mã voucher')
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
</script>
