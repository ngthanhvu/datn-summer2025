<template>
    <div class="flex-grow">
        <div class="container mx-auto px-4 py-4">
            <!-- Header Section -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Kho Voucher</h1>
                <p class="text-gray-600">Khám phá và sử dụng các mã giảm giá hấp dẫn</p>
            </div>

            <!-- Login Prompt for non-logged users -->
            <div v-if="!isLoggedIn" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-info-circle text-blue-600 text-lg"></i>
                        <div>
                            <p class="text-blue-800 font-semibold">Đăng nhập để nhận mã giảm giá</p>
                            <p class="text-blue-600 text-sm">Bạn cần đăng nhập hoặc đăng ký để có thể lưu và sử dụng các
                                mã giảm giá</p>
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

            <!-- Nhập mã voucher Section -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <form class="flex justify-center items-center gap-2" @submit.prevent="onSubmitVoucher">
                    <label class="mr-2 font-medium">Mã Voucher</label>
                    <input v-model="voucherInput" type="text" placeholder="Nhập mã voucher tại đây"
                        class="w-full max-w-md pl-3 pr-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                    <button type="submit" :disabled="!voucherInput.trim() || savingVoucher"
                        class="bg-gray-200 text-white px-5 py-2 rounded hover:bg-blue-600 hover:text-white transition disabled:bg-gray-200 disabled:text-white">
                        Lưu
                    </button>
                </form>
            </div>

            <!-- Voucher Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 bg-white p-8 rounded-[5px]">

                <!-- Loading State -->
                <div v-if="loading" class="col-span-full flex justify-center items-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                </div>

                <!-- Empty State -->
                <div v-else-if="!loading && filteredCoupons.length === 0" class="col-span-full text-center py-8">
                    <i class="fa-solid fa-ticket text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">Không tìm thấy voucher nào</p>
                </div>

                <!-- Voucher Cards -->
                <div v-else v-for="coupon in filteredCoupons" :key="coupon.id"
                    class="flex max-w-xs w-full bg-white shadow-md rounded-md"
                    :class="{ 'opacity-60': getCouponStatus(coupon) !== 'active' }">

                    <div class="left-edge flex items-center justify-center">
                        <i class="fa-solid fa-ticket text-white text-2xl"></i>
                    </div>

                    <div class="flex flex-col justify-center px-4 py-4 flex-1">
                        <p class="text-md text-blue-600 font-semibold text-center">
                            NHẬP MÃ:
                            <span class="font-normal">{{ coupon.code || 'N/A' }}</span>
                        </p>
                        <p class="text-xs text-gray-500 text-center mt-1 leading-tight">
                            {{ coupon.description || coupon.name || 'Không có mô tả' }}
                        </p>

                        <div class="mt-2 text-xs text-gray-600 text-center">
                            <div v-if="coupon.type === 'percent'">
                                Giảm {{ coupon.value || 0 }}%
                                <span v-if="coupon.max_discount_value">tối đa {{
                                    formatCurrency(coupon.max_discount_value)
                                }}</span>
                            </div>
                            <div v-else-if="coupon.type === 'shipping'">
                                Miễn ship
                            </div>
                            <div v-else>
                                Giảm {{ formatCurrency(coupon.value || 0) }}
                            </div>
                            <div v-if="coupon.min_order_value > 0">
                                Đơn tối thiểu: {{ formatCurrency(coupon.min_order_value) }}
                            </div>
                        </div>

                        <div class="mt-3 flex items-center justify-between">
                            <button v-if="getCouponStatus(coupon) === 'active' && !coupon.is_claimed && isLoggedIn"
                                @click="claimVoucherCode(coupon.id)"
                                class="bg-blue-600 text-white text-xs px-3 py-1 rounded-sm hover:bg-blue-700 transition">
                                Lấy mã
                            </button>
                            <button
                                v-else-if="getCouponStatus(coupon) === 'active' && !coupon.is_claimed && !isLoggedIn"
                                @click="claimVoucherCode(coupon.id)"
                                class="bg-orange-500 text-white text-xs px-3 py-1 rounded-sm hover:bg-orange-600 transition mr-5">
                                Đăng nhập để lấy
                            </button>
                            <button v-else-if="getCouponStatus(coupon) === 'active' && coupon.is_claimed" disabled
                                class="bg-gray-300 text-white text-xs px-3 py-1 rounded-sm cursor-not-allowed">
                                Đã lưu
                            </button>
                            <button v-else disabled
                                class="bg-gray-400 text-white text-xs px-3 py-1 rounded-sm cursor-not-allowed">
                                {{ getCouponStatus(coupon) === 'expired' ? 'Đã hết hạn' :
                                    getCouponStatus(coupon) === 'used' ? 'Đã sử dụng' : 'Không hoạt động' }}
                            </button>
                            <div class="text-xs text-gray-700 hover:underline cursor-pointer">
                                <div>Hạn sử dụng: {{ formatDate(coupon.end_date) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useCouponStore } from '../../stores/coupons'
import { useCoupon } from '../../composable/useCoupon'
import { push } from 'notivue'
import Cookies from 'js-cookie'
import { useRouter } from 'vue-router'

const router = useRouter()

const couponStore = useCouponStore()
const { claimCoupon, getMyCoupons } = useCoupon()

const loading = computed(() => couponStore.isLoadingCoupons)
const myCoupons = ref([])

const isLoggedIn = computed(() => {
    return !!Cookies.get('token')
})

onMounted(async () => {
    try {
        if (!couponStore.coupons.length) {
            await couponStore.fetchCoupons()
        }
        const myCouponsData = await getMyCoupons()
        myCoupons.value = Array.isArray(myCouponsData) ? myCouponsData : (myCouponsData?.data || myCouponsData?.coupons || [])
    } catch (error) {
        console.error('Error loading coupons:', error)
        push.error('Có lỗi xảy ra khi tải mã giảm giá')
    }
})

const voucherInput = ref('')
const savingVoucher = ref(false)

const onSubmitVoucher = async () => {
    if (!voucherInput.value.trim()) return
    savingVoucher.value = true
    try {
        const found = couponStore.coupons.find(c => c.code?.toLowerCase() === voucherInput.value.trim().toLowerCase())
        if (found) {
            await claimVoucherCode(found.id)
        } else {
            push.error('Không tìm thấy mã voucher này!')
        }
    } catch (err) {
        // push.error('Có lỗi xảy ra khi lưu mã!')
        console.error('Error saving voucher:', err)
    } finally {
        savingVoucher.value = false
        voucherInput.value = ''
    }
}

const claimVoucherCode = async (couponId) => {
    try {
        await claimCoupon(couponId)
        push.success('Đã lưu mã voucher thành công!')
        const myCouponsData = await getMyCoupons()
        myCoupons.value = Array.isArray(myCouponsData) ? myCouponsData : (myCouponsData?.data || myCouponsData?.coupons || [])
    } catch (err) {
        if (err.message === 'Vui lòng đăng nhập / đăng ký để nhận coupon') {
            router.push('/login')
            push.error("Vui lòng đăng nhập / đăng ký để nhận coupon")
        } else {
            push.error('Không thể lưu mã voucher này!')
        }
        console.error('Không thể lấy mã voucher:', err)
    }
}

const filteredCoupons = computed(() => {
    const allCoupons = couponStore.coupons || []
    const claimedIds = myCoupons.value.map(c => c.coupon_id || c.id)

    return allCoupons
        .filter(coupon => coupon.is_active) // Chỉ lọc active, không bỏ mã hết hạn
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
</script>

<style scoped>
.left-edge {
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