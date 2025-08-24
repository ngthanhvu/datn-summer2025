<template>
    <div>
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Voucher Đã Lưu</h1>
            <p class="text-gray-600">Danh sách các mã giảm giá bạn đã lưu. Hoặc có thể nhận <router-link
                    to="/kho-voucher" class="text-blue-600 hover:underline">tại đây</router-link to="/kho-voucher"></p>
        </div>

        <!-- Skeleton Loading -->
        <div v-if="loading" class="space-y-4">
            <div v-for="i in 3" :key="i" class="bg-white rounded-lg shadow-sm p-6 animate-pulse">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="bg-gray-200 p-4 rounded-full"></div>
                            <div>
                                <div class="h-4 bg-gray-200 rounded w-32 mb-2"></div>
                                <div class="h-3 bg-gray-200 rounded w-20"></div>
                            </div>
                        </div>

                        <div class="h-3 bg-gray-200 rounded w-2/3 mb-3"></div>

                        <div class="flex flex-wrap gap-4 mb-4">
                            <div class="h-5 bg-gray-200 rounded w-32"></div>
                            <div class="h-5 bg-gray-200 rounded w-32"></div>
                        </div>

                        <div class="bg-gray-100 p-3 rounded-lg mb-4">
                            <div class="h-5 bg-gray-200 rounded w-24 mb-2"></div>
                            <div class="h-3 bg-gray-200 rounded w-20"></div>
                        </div>
                    </div>

                    <div class="flex flex-col items-end gap-3">
                        <div class="h-5 bg-gray-200 rounded w-20"></div>
                        <div class="h-8 bg-gray-200 rounded w-24"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="!loading && myCoupons.length === 0" class="bg-white rounded-lg shadow-sm p-8 text-center">
            <i class="fa-solid fa-ticket text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-500 mb-2">Bạn chưa lưu voucher nào</p>
            <router-link to="/kho-voucher" class="text-blue-600 hover:underline cursor-pointer">
                Khám phá voucher mới →
            </router-link>
        </div>

        <!-- Coupons List -->
        <div v-else class="space-y-4">
            <div v-for="coupon in myCoupons" :key="coupon.id" class="bg-white rounded-lg shadow-sm p-6"
                :class="{ 'opacity-60': getCouponStatus(coupon) !== 'active' }">

                <div class="flex items-start justify-between">
                    <!-- Coupon Info -->
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="bg-blue-100 p-2 rounded-full">
                                <i class="fa-solid fa-ticket text-blue-600"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800">{{ coupon.name }}</h3>
                                <p class="text-sm text-gray-500">{{ coupon.code }}</p>
                            </div>
                        </div>

                        <p class="text-sm text-gray-600 mb-3">
                            {{ coupon.description || 'Không có mô tả' }}
                        </p>

                        <div class="flex flex-wrap gap-4 mb-4">
                            <div class="bg-gray-50 px-3 py-1 rounded-full">
                                <span class="text-xs text-gray-600">
                                    <i class="fa-solid fa-calendar mr-1"></i>
                                    Hạn sử dụng: {{ formatDate(coupon.end_date) }}
                                </span>
                            </div>
                            <div class="bg-gray-50 px-3 py-1 rounded-full">
                                <span class="text-xs text-gray-600">
                                    <i class="fa-solid fa-clock mr-1"></i>
                                    Lưu lúc: {{ formatDate(coupon.pivot?.claimed_at) }}
                                </span>
                            </div>
                        </div>

                        <!-- Discount Info -->
                        <div class="bg-blue-50 p-3 rounded-lg mb-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-lg font-bold text-blue-600">
                                        {{
                                            coupon.type === 'percent'
                                                ? `${coupon.value}%`
                                                : (coupon.type === 'shipping' ? 'Miễn ship' : formatCurrency(coupon.value))
                                        }}
                                    </span>
                                    <span class="text-sm text-gray-600 ml-2">
                                        {{ coupon.type === 'percent' ? 'giảm giá' : (coupon.type === 'shipping' ? '' :
                                            'giảm cố định') }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-500">Đơn tối thiểu</div>
                                    <div class="text-sm font-medium">{{ formatCurrency(coupon.min_order_value) }}
                                    </div>
                                </div>
                            </div>
                            <div v-if="coupon.max_discount_value && coupon.type === 'percent'"
                                class="text-xs text-gray-500 mt-1">
                                Giảm tối đa: {{ formatCurrency(coupon.max_discount_value) }}
                            </div>
                        </div>
                    </div>

                    <!-- Status & Actions -->
                    <div class="flex flex-col items-end gap-3">
                        <!-- Status Badge -->
                        <div class="flex items-center gap-2">
                            <span v-if="coupon.pivot?.status === 'used'"
                                class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">
                                <i class="fa-solid fa-check mr-1"></i>Đã sử dụng
                            </span>
                            <span v-else-if="getCouponStatus(coupon) === 'active'"
                                class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                <i class="fa-solid fa-clock mr-1"></i>Có thể sử dụng
                            </span>
                            <span v-else-if="getCouponStatus(coupon) === 'expired'"
                                class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">
                                <i class="fa-solid fa-times mr-1"></i>Đã hết hạn
                            </span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            <button v-if="getCouponStatus(coupon) === 'active' && coupon.pivot?.status !== 'used'"
                                @click="copyCouponCode(coupon.code)"
                                class="bg-blue-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                                <i class="fa-solid fa-copy mr-1"></i>Sao chép mã
                            </button>
                            <button v-else disabled
                                class="bg-gray-300 text-gray-500 text-sm px-4 py-2 rounded-lg cursor-not-allowed">
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
import { ref, onMounted } from 'vue'
import { useCoupon } from '../../composable/useCoupon'
import Swal from 'sweetalert2'
import { useHead } from '@vueuse/head'

useHead({
    title: "Mã giảm giá của tôi | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Mã giảm giá của tôi"
        }
    ]
})

const { getMyCoupons } = useCoupon()

const loading = ref(false)
const myCoupons = ref([])

onMounted(async () => {
    try {
        loading.value = true
        const res = await getMyCoupons()
        myCoupons.value = Array.isArray(res.coupons) ? res.coupons : []
    } catch (err) {
        console.error('Lỗi khi tải mã giảm giá:', err)
        Swal.fire('Lỗi', 'Không thể tải mã giảm giá', 'error')
    } finally {
        loading.value = false
    }
})

const copyCouponCode = async (code) => {
    try {
        await navigator.clipboard.writeText(code)
        Swal.fire('Thành công', `Đã sao chép mã: ${code}`, 'success')
    } catch (err) {
        console.error('Không thể sao chép mã:', err)
        Swal.fire('Lỗi', 'Không thể sao chép mã voucher', 'error')
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
    } catch {
        return 'N/A'
    }
}

const isExpired = (coupon) => {
    if (!coupon?.end_date) return false
    return new Date() > new Date(coupon.end_date)
}

const isUsedUp = (coupon) => {
    if (!coupon?.usage_limit) return false
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
