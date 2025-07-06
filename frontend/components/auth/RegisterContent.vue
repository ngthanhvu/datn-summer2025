<template>
    <div class="form-container mx-auto tw-mt-[100px] tw-bg-white tw-mb-10 tw-border tw-border-gray-150"
        id="registerForm">
        <h2 class="text-center mb-4 tw-font-semibold tw-text-2xl">Đăng Ký</h2>
        <form @submit.prevent="handleRegister">
            <div class="mb-2">
                <label for="registerUsername">Tên người dùng</label>
                <input v-model="form.username" type="text" id="registerUsername" name="username"
                    placeholder="Nhập tên người dùng"
                    class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded tw-text-base focus:tw-outline-none focus:tw-border-[#81aacc] focus:tw-ring-1 focus:tw-ring-[#81aacc]" />
                <p v-if="error.username" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ error.username }}</p>
            </div>
            <div class="mb-2">
                <label for="registerEmail">Email</label>
                <input v-model="form.email" type="email" id="registerEmail" name="email"
                    placeholder="Nhập email của bạn"
                    class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded tw-text-base focus:tw-outline-none focus:tw-border-[#81aacc] focus:tw-ring-1 focus:tw-ring-[#81aacc]" />
                <p v-if="error.email" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ error.email }}</p>
            </div>
            <div class="mb-2">
                <label for="registerPassword">Mật khẩu</label>
                <div class="position-relative">
                    <input v-model="form.password" :type="showPassword ? 'text' : 'password'" id="registerPassword"
                        name="password" placeholder="Nhập mật khẩu"
                        class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded tw-text-base focus:tw-outline-none focus:tw-border-[#81aacc] focus:tw-ring-1 focus:tw-ring-[#81aacc]" />
                    <button type="button"
                        class="btn position-absolute top-50 end-0 translate-middle-y text-decoration-none pe-3"
                        style="color:#81aacc;" @click="showPassword = !showPassword">
                        <i :class="showPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
                    </button>
                </div>
                <p v-if="error.password" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ error.password }}</p>
            </div>
            <div class="mb-2">
                <label for="confirmPassword">Xác nhận mật khẩu</label>
                <div class="position-relative">
                    <input v-model="form.confirm_password" :type="showConfirmPassword ? 'text' : 'password'"
                        id="confirmPassword" name="confirm_password" placeholder="Xác nhận mật khẩu"
                        class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded tw-text-base focus:tw-outline-none focus:tw-border-[#81aacc] focus:tw-ring-1 focus:tw-ring-[#81aacc]" />
                    <button type="button"
                        class="btn position-absolute top-50 end-0 translate-middle-y text-decoration-none pe-3"
                        style="color:#81aacc" @click="showConfirmPassword = !showConfirmPassword">
                        <i :class="showConfirmPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
                    </button>
                </div>
                <p v-if="error.confirm_password" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ error.confirm_password }}
                </p>
                <p class="tw-text-red-500 tw-text-sm tw-mt-1" v-if="error.register">{{ error.register }}</p>
            </div>
            <div id="cf-turnstile" data-theme="light"></div>
            <button type="submit"
                class="tw-py-2 tw-rounded-lg tw-bg-[#81AACC] tw-text-white hover:tw-bg-[#66a2d3] w-100 tw-relative"
                :disabled="isLoading">
                <span :class="{ 'tw-opacity-0': isLoading }">Đăng Ký</span>
                <div v-if="isLoading" class="tw-absolute tw-inset-0 tw-flex tw-items-center tw-justify-center">
                    <div
                        class="tw-w-5 tw-h-5 tw-border-2 tw-border-white tw-border-t-transparent tw-rounded-full tw-animate-spin">
                    </div>
                </div>
            </button>
            <div class="text-center mt-3">
                <NuxtLink to="/login" class="text-center mt-3 text-decoration-none text-muted">
                    Đã có tài khoản? <span class="toggle-form">Đăng nhập ngay</span>
                </NuxtLink>
            </div>
        </form>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useAuth } from '~/composables/useAuth'
import Swal from 'sweetalert2'

const { register } = useAuth()
const { captchaToken, renderCaptcha } = useCaptcha()

const form = reactive({
    username: '',
    email: '',
    password: '',
    confirm_password: '',
    cf_turnstile_response: 'test-token'
})

const error = reactive({
    username: '',
    email: '',
    password: '',
    confirm_password: '',
    register: ''
})
const isLoading = ref(false)

const showPassword = ref(false)
const showConfirmPassword = ref(false)

const isEmailValid = (email) => {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return re.test(email)
}

const resetErrors = () => {
    error.username = ''
    error.email = ''
    error.password = ''
    error.confirm_password = ''
    error.register = ''
}

const handleRegister = async () => {
    resetErrors()
    isLoading.value = true

    try {
        // Validate fields
        let hasError = false

        if (!form.username) {
            error.username = 'Vui lòng nhập tên người dùng'
            hasError = true
        }

        if (!form.email) {
            error.email = 'Vui lòng nhập email'
            hasError = true
        } else if (!isEmailValid(form.email)) {
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

        if (!form.confirm_password) {
            error.confirm_password = 'Vui lòng xác nhận mật khẩu'
            hasError = true
        } else if (form.password !== form.confirm_password) {
            error.confirm_password = 'Mật khẩu xác nhận không khớp'
            hasError = true
        }

        if (hasError) {
            isLoading.value = false
            return
        }

        // Gửi đăng ký
        const success = await register({
            username: form.username,
            email: form.email,
            password: form.password,
            password_confirmation: form.confirm_password,
            role: 'user',
            cf_turnstile_response: 'test-token'
        })

        if (success) {
            let timerInterval

            Swal.fire({
                icon: 'success',
                title: 'Đăng ký thành công!',
                html: 'Bạn sẽ được chuyển hướng sau <strong></strong> giây.',
                timer: 3000,
                timerProgressBar: true,
                didOpen: () => {
                    const content = Swal.getHtmlContainer()
                    let count = 3
                    content.querySelector('strong').textContent = count

                    timerInterval = setInterval(() => {
                        count--
                        if (count >= 0) {
                            content.querySelector('strong').textContent = count
                        }
                    }, 1000)
                },
                willClose: () => {
                    clearInterval(timerInterval)
                }
            }).then(() => {
                navigateTo('/')
            })
        }
    } catch (err) {
        console.error('Register error:', err.response?.data || err.message)
        const errorMessage = err.response?.data?.message || 'Đăng ký thất bại. Vui lòng thử lại.'

        Swal.fire({
            icon: 'error',
            title: 'Lỗi!',
            text: errorMessage
        })
    } finally {
        isLoading.value = false
    }
}
</script>

<style scoped>
.form-container {
    max-width: 500px;
    padding: 20px;
    border-radius: 10px;
}

.toggle-form {
    cursor: pointer;
    color: #81aacc;
}

.toggle-form:hover {
    text-decoration: underline;
}
</style>