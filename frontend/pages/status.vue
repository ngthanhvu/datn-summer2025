<template>
    <div class="tw-mt-10 tw-flex tw-items-center tw-justify-center tw-p-4">
        <div v-if="isSuccess"
            class="tw-bg-white tw-p-8 tw-rounded-lg tw-shadow-md tw-max-w-md tw-w-full tw-text-center">
            <div
                class="tw-w-16 tw-h-16 tw-bg-green-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                <i class="fas fa-check tw-text-green-500 tw-text-2xl"></i>
            </div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-800 tw-mb-2">Thanh toán thành công!</h1>
            <p class="tw-text-gray-600 tw-mb-6">Đơn hàng của bạn đã được xử lý thành công. Cảm ơn bạn đã mua sắm!</p>
            <div class="tw-bg-gray-100 tw-p-4 tw-rounded-lg tw-mb-6 tw-text-left">
                <p class="tw-text-gray-700"><span class="tw-font-medium">Mã đơn hàng:</span> {{ orderId }}</p>
                <p class="tw-text-gray-700"><span class="tw-font-medium">Số tiền:</span> {{ formatPrice(amount) }}</p>
                <p class="tw-text-gray-700"><span class="tw-font-medium">Ngày:</span> {{ formatDate(date) }}</p>
            </div>
            <button @click="goToHome"
                class="tw-w-full tw-bg-[#81AACC] hover:tw-bg-[#377db6] tw-text-white tw-font-medium tw-py-2 tw-px-4 tw-rounded-lg tw-transition tw-duration-200">
                Quay lại trang chủ
            </button>
        </div>

        <div v-else class="tw-bg-white tw-p-8 tw-rounded-lg tw-shadow-md tw-max-w-md tw-w-full tw-text-center">
            <div
                class="tw-w-16 tw-h-16 tw-bg-red-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-4">
                <i class="fas fa-times tw-text-red-500 tw-text-2xl"></i>
            </div>
            <h1 class="tw-text-2xl tw-font-bold tw-text-gray-800 tw-mb-2">Thanh toán thất bại</h1>
            <p v-if="errorMessage" class="tw-text-red-600 tw-mb-6">{{ errorMessage }}</p>
            <p v-else class="tw-text-gray-600 tw-mb-6">Đã có lỗi xảy ra trong quá trình thanh toán. Vui lòng thử lại
                sau.</p>
            <div class="tw-bg-gray-100 tw-p-4 tw-rounded-lg tw-mb-6 tw-text-left">
                <p class="tw-text-gray-700"><span class="tw-font-medium">Mã đơn hàng:</span> {{ orderId }}</p>
                <p class="tw-text-gray-700"><span class="tw-font-medium">Số tiền:</span> {{ formatPrice(amount) }}</p>
                <p class="tw-text-gray-700"><span class="tw-font-medium">Ngày:</span> {{ formatDate(date) }}</p>
            </div>
            <button @click="goToHome"
                class="tw-w-full tw-bg-red-500 hover:tw-bg-red-600 tw-text-white tw-font-medium tw-py-2 tw-px-4 tw-rounded-lg tw-transition tw-duration-200">
                Quay lại trang chủ
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const isSuccess = ref(false)
const orderId = ref('')
const amount = ref(0)
const date = ref(new Date())
const errorMessage = ref('')

useHead({
    title: computed(() => isSuccess.value ? 'Thanh toán thành công' : 'Thanh toán thất bại')
})

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search)
    const status = urlParams.get('status')
    const id = urlParams.get('orderId')
    const total = urlParams.get('amount')
    const paymentMethod = urlParams.get('payment_method')
    const message = urlParams.get('message')

    isSuccess.value = status === 'success'
    orderId.value = id || 'N/A'
    amount.value = total ? parseFloat(total) : 0

    if (message) {
        errorMessage.value = decodeURIComponent(message)
    }
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}

const formatDate = (date) => {
    return new Intl.DateTimeFormat('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    }).format(date)
}

const goToHome = () => {
    router.push('/')
}
</script>

<style>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
</style>