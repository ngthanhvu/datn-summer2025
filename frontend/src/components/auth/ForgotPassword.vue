<template>
    <div class="flex items-center justify-center font-sans mt-[150px] mb-[100px]">
        <div class="border rounded-lg p-8 w-full max-w-md bg-white border-gray-300">
            <div class="flex justify-center mb-4">
                <i class="fas fa-bolt text-orange-500 text-2xl"></i>
            </div>
            <h2 class="text-center text-gray-900 font-semibold text-xl mb-8 font-inter">
                Quên mật khẩu?
            </h2>

            <form @submit.prevent="handleForgotPassword">
                <!-- Email -->
                <label for="email" class="block text-gray-700 text-[16px] mb-2 font-semibold">Email <span
                        class="text-red-500">*</span></label>
                <div class="relative mb-1">
                    <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                        <i class="far fa-envelope"></i>
                    </span>
                    <input v-model="email" id="email" type="email" placeholder="Nhập email của bạn"
                        class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-md text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-1 ring-[#81AACC]" />
                </div>
                <p v-if="emailError" class="text-red-500 text-sm mt-1 mb-4">{{ emailError }}</p>
                <div v-else class="mb-6"></div>

                <!-- Submit -->
                <button type="submit" :disabled="isSubmitting"
                    class="bg-[#81AACC] text-white hover:bg-[#66a2d3] w-full py-2 rounded-lg relative flex items-center justify-center cursor-pointer">
                    <span v-if="!isSubmitting">Gửi</span>
                    <span v-else>
                        <i class="fas fa-spinner animate-spin mr-2"></i> Đang gửi...
                    </span>
                </button>
            </form>

            <div class="text-center mt-4">
                <router-link to="/login" class="text-gray-600 text-sm font-normal hover:underline cursor-pointer">
                    <i class="fas fa-arrow-left mr-2"></i>Trở lại đăng nhập
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuth } from '../../composable/useAuth'
import { useRouter } from 'vue-router'

const router = useRouter()
const { forgotPassword } = useAuth()

const email = ref('')
const emailError = ref('')
const isSubmitting = ref(false)

const isValidEmail = (value) => {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
}

const handleForgotPassword = async () => {
    emailError.value = ''

    // Validate
    if (!email.value.trim()) {
        emailError.value = 'Email không được bỏ trống.'
        return
    }
    if (!isValidEmail(email.value)) {
        emailError.value = 'Email không hợp lệ.'
        return
    }

    try {
        isSubmitting.value = true
        await forgotPassword(email.value)
        router.push('/reset?email=' + email.value)
    } catch (error) {
        console.error(error)
        emailError.value = 'Đã xảy ra lỗi khi gửi email. Vui lòng thử lại.'
    } finally {
        isSubmitting.value = false
    }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@600&display=swap');

.font-inter {
    font-family: 'Inter', sans-serif;
}
</style>
