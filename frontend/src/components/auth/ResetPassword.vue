<template>
    <div class="flex items-center justify-center p-4 mt-5">
        <main class="bg-white rounded-xl max-w-md w-full p-[30px] text-center border border-gray-200">
            <h1 class="font-extrabold text-xl leading-tight mb-3">Đặt lại mật khẩu</h1>
            <p class="text-[#4A5568] mb-8 text-base leading-relaxed">
                Nhập 6 số từ email của bạn để xác nhận đổi mật khẩu
            </p>

            <form @submit.prevent="handleResetPassword" class="mb-6">
                <!-- OTP Inputs -->
                <div class="flex justify-center gap-4 mb-5">
                    <input v-for="(digit, index) in 6" :key="index" v-model="otpInputs[index]" type="text" maxlength="1"
                        :id="`otp-${index}`"
                        class="w-14 h-14 rounded-md bg-[#F1F5F9] text-center text-xl font-semibold text-black focus:outline-none focus:ring-2"
                        :class="otpError ? 'ring-red-400 border border-red-400' : 'focus:ring-[#81AACC]'"
                        inputmode="numeric" pattern="[0-9]*" :aria-label="`Digit ${index + 1}`"
                        @input="(e) => onDigitInput(e, index)" />
                </div>
                <p v-if="otpError" class="text-red-500 text-sm mb-4">{{ otpError }}</p>

                <!-- Email (hidden) -->
                <input v-model="emailInput" type="hidden" />

                <!-- New Password -->
                <div class="relative mb-3">
                    <input :type="showPassword ? 'text' : 'password'" v-model="newPassword" placeholder="Mật khẩu mới"
                        class="pl-3 pr-10 py-2 w-full border rounded-md focus:outline-none focus:ring-1"
                        :class="passwordError ? 'border-red-500 focus:ring-red-300' : 'border-gray-300 focus:ring-[#81AACC]'" />
                    <span role="button" @click="showPassword = !showPassword"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500" tabindex="-1"
                        aria-hidden="true">
                        <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                    </span>
                </div>
                <p v-if="passwordError" class="text-red-500 text-sm mb-3">{{ passwordError }}</p>

                <!-- Confirm Password -->
                <div class="relative mb-3">
                    <input :type="showConfirmPassword ? 'text' : 'password'" v-model="confirmPassword"
                        placeholder="Xác nhận mật khẩu mới"
                        class="pl-3 pr-10 py-2 w-full border rounded-md focus:outline-none focus:ring-1"
                        :class="confirmPasswordError ? 'border-red-500 focus:ring-red-300' : 'border-gray-300 focus:ring-[#81AACC]'" />
                    <span role="button" @click="showConfirmPassword = !showConfirmPassword" tabindex="-1"
                        aria-hidden="true" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">
                        <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                    </span>
                </div>
                <p v-if="confirmPasswordError" class="text-red-500 text-sm mb-4">{{ confirmPasswordError }}</p>

                <!-- Submit -->
                <button type="submit" :disabled="isSubmitting"
                    class="bg-[#81AACC] text-white hover:bg-[#66a2d3] w-full py-2 rounded-lg relative flex justify-center items-center cursor-pointer">
                    <span v-if="!isSubmitting">Xác nhận đổi mật khẩu</span>
                    <span v-else>
                        <i class="fas fa-spinner animate-spin mr-2"></i> Đang xử lý...
                    </span>
                </button>
            </form>

            <p class="mt-5 text-[#4A5568] text-sm">
                Bạn không nhận được mã?
                <button class="text-[#81AACC] font-semibold cursor-pointer">Gửi lại</button>
            </p>
        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuth } from '../../composable/useAuth'
import { push } from 'notivue'

const router = useRouter()
const route = useRoute()
const { resetPassword } = useAuth()

const emailInput = ref(route.query.email || '')
const otpInputs = ref(Array(6).fill(''))
const newPassword = ref('')
const confirmPassword = ref('')
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const isSubmitting = ref(false)

// Error states
const otpError = ref('')
const passwordError = ref('')
const confirmPasswordError = ref('')

const onDigitInput = (event, index) => {
    const value = event.target.value

    if (value.length === 6 && /^\d{6}$/.test(value)) {
        for (let i = 0; i < 6; i++) {
            otpInputs.value[i] = value[i]
        }
        setTimeout(() => {
            document.getElementById('otp-5')?.focus()
        }, 10)
        return
    }

    if (value.length === 1 && index < otpInputs.value.length - 1) {
        const nextInput = document.getElementById(`otp-${index + 1}`)
        nextInput?.focus()
    }
}

const handleResetPassword = async () => {
    if (isSubmitting.value) return

    // Reset error messages
    otpError.value = ''
    passwordError.value = ''
    confirmPasswordError.value = ''

    const otpRaw = otpInputs.value.join('')
    if (!/^\d{6}$/.test(otpRaw)) {
        otpError.value = 'Vui lòng nhập đầy đủ 6 chữ số OTP.'
        return
    }

    if (!newPassword.value || newPassword.value.length < 6) {
        passwordError.value = 'Mật khẩu phải có ít nhất 6 ký tự.'
        return
    }

    if (confirmPassword.value !== newPassword.value) {
        confirmPasswordError.value = 'Mật khẩu xác nhận không khớp.'
        return
    }

    try {
        isSubmitting.value = true
        const otp = otpRaw.split('').map(Number)
        await resetPassword(emailInput.value, otp, newPassword.value, confirmPassword.value)
        push.success('Đổi mật khẩu thành công')
        router.push('/login')
    } catch (error) {
        console.error(error)
        push.error('Đổi mật khẩu thất bại')
    } finally {
        isSubmitting.value = false
    }
}
</script>