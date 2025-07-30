<template>
    <div class="form-container mx-auto mt-[100px] bg-white mb-10 border border-gray-200">
        <h2 class="text-center mb-4 mt-3 font-semibold text-2xl">Đăng Nhập</h2>
        <form @submit.prevent="handleLogin">
            <div class="mb-3">
                <label for="loginEmail" class="block font-medium mb-1">Email</label>
                <input v-model="form.email" type="email"
                    class="form-input w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-[#81aacc]"
                    id="loginEmail" placeholder="Nhập email của bạn" />
                <p class="text-red-500 text-sm mt-1" v-if="error.email">{{ error.email }}</p>
            </div>

            <div class="mb-3">
                <label for="loginPassword" class="block font-medium mb-1">Mật khẩu</label>
                <div class="relative">
                    <input v-model="form.password" :type="showPassword ? 'text' : 'password'"
                        class="form-input w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-[#81aacc]"
                        id="loginPassword" placeholder="Nhập mật khẩu" />
                    <button type="button" class="absolute top-1/2 right-3 -translate-y-1/2 text-[#81aacc]"
                        @click="showPassword = !showPassword">
                        <i :class="showPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
                    </button>
                </div>
                <p class="text-red-500 text-sm mt-1" v-if="error.password">{{ error.password }}</p>
            </div>

            <div class="mb-3 flex items-center justify-between">
                <label class="inline-flex items-center">
                    <input v-model="rememberMe" type="checkbox" class="form-checkbox" />
                    <span class="ml-2">Ghi nhớ tôi</span>
                </label>
                <router-link to="/forgot-password" class="text-[#81AACC] hover:underline text-md">
                    Quên mật khẩu?
                </router-link>
            </div>

            <button type="submit" class="bg-[#81AACC] text-white hover:bg-[#66a2d3] w-full py-2 rounded-lg relative"
                :disabled="isLoading">
                <span :class="{ 'opacity-0': isLoading }">Đăng Nhập</span>
                <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center">
                    <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                </div>
            </button>
        </form>

        <div class="text-center mt-3">
            <p class="mb-2">Hoặc</p>
            <div class="flex justify-center">
                <button @click="googleLogin"
                    class="bg-white text-gray-800 border border-gray-200 rounded-lg px-4 py-2 flex items-center justify-center gap-2 shadow-sm hover:bg-gray-100 w-full">
                    <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google"
                        class="w-6 h-6" />
                    <span>Đăng nhập bằng Google</span>
                </button>
            </div>
        </div>

        <div class="text-center mt-3">
            <router-link to="/register" class="text-md text-gray-500">
                Chưa có tài khoản? <span class="text-[#81aacc] hover:underline">Đăng ký ngay</span>
            </router-link>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAuth } from '../../composable/useAuth'
import { useAuthStore } from '../../stores/auth'
import { push } from 'notivue'

const form = reactive({ email: '', password: '' })
const error = reactive({ email: '', password: '' })
const isLoading = ref(false)
const rememberMe = ref(false)
const showPassword = ref(false)

const { login, getUser } = useAuth()
const authStore = useAuthStore()

onMounted(() => {
    const savedEmail = localStorage.getItem('rememberedEmail')
    const savedPassword = localStorage.getItem('rememberedPassword')
    if (savedEmail && savedPassword) {
        form.email = savedEmail
        form.password = savedPassword
        rememberMe.value = true
    }
})

const resetErrors = () => {
    error.email = ''
    error.password = ''
}

const handleLogin = async () => {
    resetErrors()
    isLoading.value = true

    let hasError = false
    if (!form.email) {
        error.email = 'Vui lòng nhập email'
        hasError = true
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
        error.email = 'Email không hợp lệ'
        hasError = true
    }

    if (!form.password) {
        error.password = 'Vui lòng nhập mật khẩu'
        hasError = true
    } else if (form.password.length < 6) {
        error.password = 'Mật khẩu phải có ít nhất 6 ký tự'
        hasError = true
    }

    if (hasError) {
        isLoading.value = false
        return
    }

    try {
        const success = await login({ email: form.email, password: form.password })
        if (success) {
            // Lưu thông tin user vào store (trừ token)
            const user = await getUser()
            if (user) {
                authStore.setUser(user)
            }
            if (rememberMe.value) {
                localStorage.setItem('rememberedEmail', form.email)
                localStorage.setItem('rememberedPassword', form.password)
            } else {
                localStorage.removeItem('rememberedEmail')
                localStorage.removeItem('rememberedPassword')
            }
            isLoading.value = false
            push.success('Đăng nhập thành công! Đang chuyển hướng...')
            setTimeout(() => {
                window.location.href = '/'
            }, 3000)
        } else {
            error.email = 'Email hoặc mật khẩu không đúng'
            isLoading.value = false
        }
    } catch (err) {
        error.email = 'Đăng nhập thất bại'
        console.log(err)
        isLoading.value = false
    }
}

const googleLogin = () => {
    alert('Đăng nhập bằng Google (fake)')
}
</script>

<style scoped>
.form-container {
    max-width: 500px;
    padding: 20px;
    border-radius: 10px;
}
</style>
