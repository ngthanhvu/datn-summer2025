<template>
    <div class="coupon-panel" :class="{ 'coupon-panel-open': isOpen }">
        <div class="coupon-panel-content">
            <div class="flex justify-between items-center mb-4">
                <h6 class="font-semibold m-0">Mã giảm giá</h6>
                <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="space-y-4 max-h-[calc(100vh-200px)] overflow-y-auto">
                <div v-if="loading" class="text-center py-8">
                    <div class="text-gray-500">
                        <i class="bi bi-hourglass-split text-2xl block mb-2"></i>
                        <p>Đang tải mã giảm giá...</p>
                    </div>
                </div>

                <div v-else-if="!coupons.length" class="text-center py-8">
                    <div class="text-gray-500 mb-4">
                        <i class="bi bi-ticket-perforated text-4xl block mb-3"></i>
                        <p class="text-lg">Không có mã giảm giá nào</p>
                    </div>
                </div>

                <div v-else v-for="coupon in coupons" :key="coupon.id"
                    class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="bi bi-ticket-perforated text-blue-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <h6 class="font-semibold text-blue-600">{{ coupon.code }}</h6>
                                <span v-if="coupon.discount_type === 'percentage'"
                                    class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                    {{ coupon.discount_value }}%
                                </span>
                                <span v-else-if="coupon.discount_type === 'fixed'"
                                    class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                    -{{ formatPrice(coupon.discount_value) }}
                                </span>
                                <span v-else-if="coupon.discount_type === 'shipping'"
                                    class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full">
                                    Freeship
                                </span>
                            </div>

                            <p class="text-sm text-gray-700 mb-2">{{ coupon.description }}</p>

                            <div class="text-xs text-gray-500 mb-3">
                                <div v-if="coupon.minimum_amount">
                                    Áp dụng cho đơn hàng tối thiểu: {{ formatPrice(coupon.minimum_amount) }}
                                </div>
                                <div v-if="coupon.maximum_discount">
                                    Giảm tối đa: {{ formatPrice(coupon.maximum_discount) }}
                                </div>
                                <div v-if="coupon.valid_until">
                                    Hạn sử dụng: {{ formatDate(coupon.valid_until) }}
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <button @click="copyCouponCode(coupon.code)"
                                    class="flex-1 bg-blue-600 text-white text-sm py-2 px-3 rounded-md hover:bg-blue-700 transition-colors">
                                    <i class="bi bi-copy mr-1"></i>
                                    Sao chép
                                </button>
                                <button @click="showCouponDetails(coupon)"
                                    class="px-3 py-2 text-blue-600 text-sm border border-blue-600 rounded-md hover:bg-blue-50 transition-colors">
                                    <i class="bi bi-info-circle mr-1"></i>
                                    Chi tiết
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="coupon-overlay" :class="{ 'coupon-overlay-open': isOpen }" @click="$emit('close')"></div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useCoupon } from '../../composable/useCoupon'

const props = defineProps({
    isOpen: Boolean
})
const emit = defineEmits(['close'])

const { getCoupons } = useCoupon()
const coupons = ref([])
const loading = ref(false)

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleDateString('vi-VN')
}

const fetchCoupons = async () => {
    try {
        loading.value = true
        const data = await getCoupons()
        // Lọc các coupon còn hiệu lực và sắp xếp theo thứ tự ưu tiên
        const validCoupons = data.filter(coupon => {
            if (coupon.valid_until) {
                return new Date(coupon.valid_until) > new Date()
            }
            return true
        }).sort((a, b) => {
            // Ưu tiên theo thứ tự: percentage > fixed > shipping
            const priority = { percentage: 3, fixed: 2, shipping: 1 }
            return priority[b.discount_type] - priority[a.discount_type]
        })
        coupons.value = validCoupons.slice(0, 10) // Chỉ lấy 10 coupon đầu tiên
    } catch (error) {
        console.error('Error fetching coupons:', error)
        coupons.value = []
    } finally {
        loading.value = false
    }
}

const copyCouponCode = async (code) => {
    try {
        await navigator.clipboard.writeText(code)
        // Hiển thị thông báo thành công
        showCopySuccess(code)
    } catch (error) {
        console.error('Error copying coupon code:', error)
        // Fallback cho các trình duyệt cũ
        const textArea = document.createElement('textarea')
        textArea.value = code
        document.body.appendChild(textArea)
        textArea.select()
        document.execCommand('copy')
        document.body.removeChild(textArea)
        showCopySuccess(code)
    }
}

const showCopySuccess = (code) => {
    // Tạo thông báo tạm thời
    const notification = document.createElement('div')
    notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-md shadow-lg z-50 transform transition-all duration-300'
    notification.textContent = `Đã sao chép mã: ${code}`
    document.body.appendChild(notification)

    // Tự động ẩn sau 3 giây
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)'
        setTimeout(() => {
            document.body.removeChild(notification)
        }, 300)
    }, 3000)
}

const showCouponDetails = (coupon) => {
}

watch(() => props.isOpen, (open) => {
    if (open) {
        fetchCoupons()
    }
})

onMounted(() => {
    if (props.isOpen) {
        fetchCoupons()
    }
})
</script>

<style scoped>
.coupon-panel {
    position: fixed;
    top: 0;
    right: -400px;
    width: 400px;
    height: 100vh;
    background-color: white;
    box-shadow: -2px 0 8px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    transition: right 0.3s ease;
}

.coupon-panel-open {
    right: 0;
}

.coupon-panel-content {
    height: 100%;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}

.coupon-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 999;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
}

.coupon-overlay-open {
    opacity: 1;
    visibility: visible;
}
</style>