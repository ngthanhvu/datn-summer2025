<template>
    <div>
        <!-- Header Section -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6 tw-mb-6">
            <h1 class="tw-text-3xl tw-font-bold tw-text-gray-800 tw-mb-2">Kho Voucher</h1>
            <p class="tw-text-gray-600">Khám phá và sử dụng các mã giảm giá hấp dẫn</p>
        </div>

        <!-- Search and Filter Section -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6 tw-mb-6">
            <div class="tw-flex tw-flex-col md:tw-flex-row tw-gap-4">
                <!-- Search Input -->
                <div class="tw-flex-1">
                    <div class="tw-relative">
                        <input v-model="searchQuery" type="text" placeholder="Tìm kiếm voucher..."
                            class="tw-w-full tw-pl-10 tw-pr-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-transparent">
                        <i
                            class="fa-solid fa-search tw-absolute tw-left-3 tw-top-1/2 tw-transform -tw-translate-y-1/2 tw-text-gray-400"></i>
                    </div>
                </div>

                <!-- Filter Dropdown -->
                <div class="tw-flex tw-gap-2">
                    <select v-model="selectedCategory"
                        class="tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-transparent">
                        <option value="">Tất cả danh mục</option>
                        <option value="percent">Giảm theo %</option>
                        <option value="fixed">Giảm cố định</option>
                        <option value="freeship">Miễn phí ship</option>
                    </select>

                    <select v-model="selectedStatus"
                        class="tw-px-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-transparent">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active">Đang hoạt động</option>
                        <option value="expired">Đã hết hạn</option>
                        <option value="used">Đã sử dụng</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Voucher Grid -->
        <div
            class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 xl:tw-grid-cols-4 tw-gap-6 tw-bg-white tw-p-8 tw-rounded-[5px]">

            <!-- Loading State -->
            <div v-if="loading" class="tw-col-span-full tw-flex tw-justify-center tw-items-center tw-py-8">
                <div class="tw-animate-spin tw-rounded-full tw-h-8 tw-w-8 tw-border-b-2 tw-border-blue-600"></div>
            </div>

            <!-- Empty State -->
            <div v-else-if="!loading && filteredCoupons.length === 0" class="tw-col-span-full tw-text-center tw-py-8">
                <i class="fa-solid fa-ticket tw-text-4xl tw-text-gray-400 tw-mb-4"></i>
                <p class="tw-text-gray-500">Không tìm thấy voucher nào</p>
            </div>

            <!-- Voucher Cards -->
            <div v-else v-for="coupon in filteredCoupons" :key="coupon.id"
                class="tw-flex tw-max-w-xs tw-w-full tw-bg-white tw-shadow-md tw-rounded-md"
                :class="{ 'tw-opacity-60': getCouponStatus(coupon) !== 'active' }">

                <div class="tw-left-edge tw-flex tw-items-center tw-justify-center">
                    <i class="fa-solid fa-ticket tw-text-white tw-text-2xl"></i>
                </div>

                <div class="tw-flex tw-flex-col tw-justify-center tw-px-4 tw-py-4 tw-flex-1">
                    <p class="tw-text-md tw-text-blue-600 tw-font-semibold tw-text-center">
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
                            Lấy mã
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
                            <div>Hạn sử dụng: {{ formatDate(coupon.end_date) }}</div>
                            <!-- <div v-if="coupon.usage_limit">
                                Đã sử dụng: {{ coupon.used_count || 0 }}/{{ coupon.usage_limit }}
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="tw-mt-8 tw-flex tw-justify-center">
            <nav class="tw-flex tw-items-center tw-space-x-2">
                <button
                    class="tw-px-3 tw-py-2 tw-text-gray-500 tw-bg-white tw-border tw-border-gray-300 tw-rounded-md hover:tw-bg-gray-50">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <button
                    class="tw-px-3 tw-py-2 tw-text-white tw-bg-blue-600 tw-border tw-border-blue-600 tw-rounded-md">1</button>
                <button
                    class="tw-px-3 tw-py-2 tw-text-gray-700 tw-bg-white tw-border tw-border-gray-300 tw-rounded-md hover:tw-bg-gray-50">2</button>
                <button
                    class="tw-px-3 tw-py-2 tw-text-gray-700 tw-bg-white tw-border tw-border-gray-300 tw-rounded-md hover:tw-bg-gray-50">3</button>
                <button
                    class="tw-px-3 tw-py-2 tw-text-gray-500 tw-bg-white tw-border tw-border-gray-300 tw-rounded-md hover:tw-bg-gray-50">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </nav>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useCouponStore } from '~/stores/useCouponStore'

const notyf = useNuxtApp().$notyf
const couponStore = useCouponStore()

const loading = computed(() => couponStore.isLoadingCoupons)

onMounted(async () => {
    if (!couponStore.coupons.length) {
        await couponStore.fetchCoupons()
    }
})

const searchQuery = ref('')
const selectedCategory = ref('')
const selectedStatus = ref('')

const claimVoucherCode = async (couponId) => {
    try {
        await couponStore.applyCoupon(couponId)
        notyf.success('Đã lưu mã voucher thành công!')
        await couponStore.fetchCoupons()
    } catch (err) {
        console.error('Không thể lấy mã voucher:', err)
    }
}

const filteredCoupons = computed(() => {
    let filtered = couponStore.coupons || []
    if (searchQuery.value) {
        filtered = filtered.filter(coupon => coupon.code?.toLowerCase().includes(searchQuery.value.toLowerCase()) || coupon.description?.toLowerCase().includes(searchQuery.value.toLowerCase()))
    }
    if (selectedCategory.value) {
        filtered = filtered.filter(coupon => coupon.type === selectedCategory.value)
    }
    if (selectedStatus.value) {
        filtered = filtered.filter(coupon => getCouponStatus(coupon) === selectedStatus.value)
    }
    return filtered
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
</script>

<style scoped>
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