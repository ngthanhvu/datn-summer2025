<template>
    <div class="mt-10 flex items-center justify-center p-4">
        <!-- Trạng thái đang chờ -->
        <div v-if="isPending" class="bg-white p-8 rounded-lg shadow-md max-w-md w-full text-center">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 animate-spin">
                <i class="fas fa-spinner text-blue-500 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Đang xử lý thanh toán...</h1>
            <p class="text-gray-600">Vui lòng đợi trong giây lát.</p>
        </div>

        <!-- Thành công -->
        <div v-else-if="isSuccess" class="bg-white p-8 rounded-lg shadow-md max-w-md w-full text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check text-green-500 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Thanh toán thành công!</h1>
            <p class="text-gray-600 mb-6">
                Đơn hàng của bạn đã được xử lý thành công. Cảm ơn bạn đã mua sắm!
            </p>
            <div class="bg-gray-100 p-4 rounded-lg mb-6 text-left">
                <p class="text-gray-700">
                    <span class="font-medium">Mã đơn hàng:</span> {{ orderId }}
                </p>
                <p class="text-gray-700">
                    <span class="font-medium">Số tiền:</span> {{ formatPrice(amount) }}
                </p>
                <p class="text-gray-700">
                    <span class="font-medium">Ngày:</span> {{ formatDate(date) }}
                </p>
            </div>
            <button @click="goToHome"
                class="w-full bg-[#81AACC] hover:bg-[#377db6] text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                Quay lại trang chủ
            </button>
        </div>

        <!-- Thất bại -->
        <div v-else class="bg-white p-8 rounded-lg shadow-md max-w-md w-full text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-times text-red-500 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Thanh toán thất bại</h1>
            <p v-if="errorMessage" class="text-red-600 mb-6">{{ errorMessage }}</p>
            <p v-else class="text-gray-600 mb-6">
                Đã có lỗi xảy ra trong quá trình thanh toán. Vui lòng thử lại sau.
            </p>
            <div class="bg-gray-100 p-4 rounded-lg mb-6 text-left">
                <p class="text-gray-700">
                    <span class="font-medium">Mã đơn hàng:</span> {{ orderId }}
                </p>
                <p class="text-gray-700">
                    <span class="font-medium">Số tiền:</span> {{ formatPrice(amount) }}
                </p>
                <p class="text-gray-700">
                    <span class="font-medium">Ngày:</span> {{ formatDate(date) }}
                </p>
            </div>
            <button @click="goToHome"
                class="w-full bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200 cursor-pointer">
                Quay lại trang chủ
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useHead } from '@vueuse/head'

const router = useRouter()
const isPending = ref(true) // trạng thái mới
const isSuccess = ref(false)
const orderId = ref('')
const amount = ref(0)
const date = ref(new Date())
const errorMessage = ref('')

useHead({
    title: computed(() => {
        if (isPending.value) return 'Đang xử lý thanh toán...'
        return isSuccess.value ? 'Thanh toán thành công' : 'Thanh toán thất bại'
    })
})

const formatPrice = (price) =>
    new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)

const formatDate = (date) =>
    new Intl.DateTimeFormat('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    }).format(date)

const goToHome = () => {
    router.push('/')
}

onMounted(async () => {
    // Giả sử gọi API kiểm tra trạng thái thanh toán
    try {
        const urlParams = new URLSearchParams(window.location.search)
        const status = urlParams.get('status')
        const id = urlParams.get('orderId')
        const total = urlParams.get('amount')
        const message = urlParams.get('message')

        orderId.value = id || 'N/A'
        amount.value = total ? parseFloat(total) : 0

        // Giả lập delay như đang chờ backend trả về
        await new Promise((resolve) => setTimeout(resolve, 2000))

        if (status === 'success') {
            isSuccess.value = true
        } else {
            errorMessage.value = message || ''
            isSuccess.value = false
        }
    } finally {
        isPending.value = false
    }
})
</script>
