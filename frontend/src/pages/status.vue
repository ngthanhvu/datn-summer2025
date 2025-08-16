<template>
    <div class="mt-10 flex items-center justify-center p-4">
        <!-- Trạng thái đang chờ -->
        <div v-if="isPending" class="p-8 max-w-md w-full text-center mt-10">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 animate-spin">
                <i class="fas fa-spinner text-blue-500 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Đang xử lý thanh toán...</h1>
            <p class="text-gray-600">Vui lòng đợi trong giây lát.</p>
        </div>

        <!-- Thành công -->
        <div v-else-if="isSuccess" class="p-8 max-w-lg w-full text-center">
            <div class="w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-4">
                <div class="success-animation">
                    <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                        <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none" />
                        <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
                    </svg>
                </div>

            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Thanh toán thành công!</h1>
            <p class="text-gray-600 mb-6">
                Đơn hàng của bạn đã được xử lý thành công. Cảm ơn bạn đã mua sắm!
            </p>
            <div class="p-4 rounded-lg mb-6 text-sm border border-gray-300 w-full max-w-md bg-white">
                <div class="flex justify-between">
                    <span class="text-gray-700">Mã đơn hàng:</span>
                    <span class="font-medium">{{ orderId }}</span>
                </div>
                <div class="flex justify-between mt-1">
                    <span class="text-gray-700">Số tiền:</span>
                    <span class="font-medium">{{ formatPrice(amount) }}</span>
                </div>
                <div class="flex justify-between mt-1">
                    <span class="text-gray-700">Ngày:</span>
                    <span class="font-medium">{{ formatDate(date) }}</span>
                </div>
                <div class="flex justify-between mt-1">
                    <span class="text-gray-700">Mã tra cứu:</span>
                    <span class="font-medium flex items-center gap-2">
                        {{ trackingCode }}
                        <button @click="copyTrackingCode"
                            class="text-[#81AACC] hover:text-[#377db6] transition cursor-pointer" title="Copy mã">
                            <i class="fas fa-copy"></i>
                        </button>
                    </span>
                </div>
            </div>
            <button @click="goToOrder"
                class="w-50 bg-[#81AACC] hover:bg-[#377db6] text-white font-medium py-2 px-4 rounded-lg transition duration-200 cursor-pointer">
                Xem đơn hàng
            </button>
        </div>

        <!-- Thất bại -->
        <div v-else class="p-8 max-w-lg w-full text-center">
            <div class="w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-4">
                <div class="error-animation">
                    <svg class="crossmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                        <circle class="crossmark__circle" cx="26" cy="26" r="25" fill="none" />
                        <path class="crossmark__cross" fill="none" d="M16 16 36 36 M36 16 16 36" />
                    </svg>
                </div>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Thanh toán thất bại</h1>
            <p v-if="errorMessage" class="text-red-600 mb-6">{{ errorMessage }}</p>
            <p v-else class="text-gray-600 mb-6">
                Đã có lỗi xảy ra trong quá trình thanh toán. Vui lòng thử lại sau.
            </p>
            <div class="p-4 rounded-lg mb-6 text-sm border border-gray-300 w-full max-w-md bg-white">
                <div class="flex justify-between">
                    <span class="text-gray-700">Mã đơn hàng:</span>
                    <span class="font-medium">{{ orderId }}</span>
                </div>
                <div class="flex justify-between mt-1">
                    <span class="text-gray-700">Số tiền:</span>
                    <span class="font-medium">{{ formatPrice(amount) }}</span>
                </div>
                <div class="flex justify-between mt-1">
                    <span class="text-gray-700">Ngày:</span>
                    <span class="font-medium">{{ formatDate(date) }}</span>
                </div>
            </div>
            <button @click="goToCart"
                class="w-50 bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200 cursor-pointer">
                Quay lại giỏ hàng
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useHead } from '@vueuse/head'
import { push } from 'notivue'

const router = useRouter()
const isPending = ref(true) // trạng thái mới
const isSuccess = ref(false)
const orderId = ref('')
const trackingCode = ref('')
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

const goToOrder = () => {
    router.push('/trang-ca-nhan?tab=orders')
}

const goToCart = () => {
    router.push('/gio-hang')
}

onMounted(async () => {
    // Giả sử gọi API kiểm tra trạng thái thanh toán
    try {
        const urlParams = new URLSearchParams(window.location.search)
        const status = urlParams.get('status')
        const id = urlParams.get('orderId')
        const total = urlParams.get('amount')
        const message = urlParams.get('message')
        trackingCode.value = urlParams.get('tracking_code') || ''

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
});

const copyTrackingCode = async () => {
    if (!trackingCode.value) return;
    try {
        await navigator.clipboard.writeText(trackingCode.value);
        push.success('Đã copy mã tra cứu!');
    } catch (err) {
        console.error('Copy thất bại', err);
    }
};

</script>

<style scoped>
.success-animation {
    margin: 150px auto;
}

.checkmark {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: block;
    stroke-width: 2;
    stroke: #4bb71b;
    stroke-miterlimit: 10;
    box-shadow: inset 0px 0px 0px #4bb71b;
    animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
    position: relative;
    top: 5px;
    right: 5px;
    margin: 0 auto;
}

.checkmark__circle {
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    stroke-width: 2;
    stroke-miterlimit: 10;
    stroke: #4bb71b;
    fill: #F5F5FA;
    animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;

}

.checkmark__check {
    transform-origin: 50% 50%;
    stroke-dasharray: 48;
    stroke-dashoffset: 48;
    animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
}

@keyframes stroke {
    100% {
        stroke-dashoffset: 0;
    }
}

@keyframes scale {

    0%,
    100% {
        transform: none;
    }

    50% {
        transform: scale3d(1.1, 1.1, 1);
    }
}

@keyframes fill {
    100% {
        box-shadow: inset 0px 0px 0px 30px #4bb71b;
    }
}

.error-animation {
    margin: 0 auto 1rem;
}

.crossmark {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: block;
    stroke-width: 2;
    stroke: #e74c3c;
    stroke-miterlimit: 10;
    box-shadow: inset 0px 0px 0px #e74c3c;
    animation: fill-red .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
    margin: 0 auto;
}

.crossmark__circle {
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    stroke-width: 2;
    stroke-miterlimit: 10;
    stroke: #e74c3c;
    fill: #F5F5FA;
    animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.crossmark__cross {
    transform-origin: 50% 50%;
    stroke-dasharray: 45;
    stroke-dashoffset: 45;
    animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
}

@keyframes stroke {
    100% {
        stroke-dashoffset: 0;
    }
}

@keyframes scale {

    0%,
    100% {
        transform: none;
    }

    50% {
        transform: scale3d(1.1, 1.1, 1);
    }
}

@keyframes fill-red {
    100% {
        box-shadow: inset 0px 0px 0px 30px #e74c3c;
    }
}
</style>