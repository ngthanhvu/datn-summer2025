<template>
    <div class="form-container mx-auto tw-mt-[100px]" id="loginForm">
        <h2 class="text-center mb-4 mt-3 tw-font-semibold tw-text-2xl">Đăng Nhập</h2>
        <form @submit.prevent="handleLogin">
            <div class="mb-3">
                <label for="loginEmail" class="form-label">Email</label>
                <input v-model="form.email" type="email" class="form-control" name="email" id="loginEmail"
                    placeholder="Nhập email của bạn" :class="{ 'is-invalid': error.email }">
                <div class="invalid-feedback" v-if="error.email">{{ error.email }}</div>
            </div>
            <div class="mb-3">
                <label for="loginPassword" class="form-label">Mật khẩu</label>
                <div class="position-relative">
                    <input v-model="form.password" :type="showPassword ? 'text' : 'password'" class="form-control"
                        name="password" id="loginPassword" placeholder="Nhập mật khẩu"
                        :class="{ 'is-invalid': error.password }">
                    <button type="button"
                        class="btn btn-link position-absolute top-50 end-0 translate-middle-y text-decoration-none pe-3"
                        @click="showPassword = !showPassword">
                        <i :class="showPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
                    </button>
                </div>
                <div class="invalid-feedback" v-if="error.password">{{ error.password }}</div>
            </div>
            <div class="mb-3 form-check">
                <input v-model="rememberMe" type="checkbox" class="form-check-input" id="rememberMe">
                <label class="form-check-label" for="rememberMe">Ghi nhớ tôi</label>
                <NuxtLink to="/forgot-password" class="float-end text-decoration-none hover:tw-text-[#81AACC]">Quên mật
                    khẩu?
                </NuxtLink>
            </div>
            <button type="submit"
                class="tw-bg-[#81AACC] tw-text-white hover:tw-bg-[#66a2d3] w-100 tw-py-2 tw-rounded-lg tw-relative"
                :disabled="isLoading">
                <span :class="{ 'tw-opacity-0': isLoading }">Đăng Nhập</span>
                <div v-if="isLoading" class="tw-absolute tw-inset-0 tw-flex tw-items-center tw-justify-center">
                    <div
                        class="tw-w-5 tw-h-5 tw-border-2 tw-border-white tw-border-t-transparent tw-rounded-full tw-animate-spin">
                    </div>
                </div>
            </button>
        </form>

        <!-- Đăng nhập bằng mạng xã hội -->
        <div class="text-center mt-3">
            <p class="mb-2">Hoặc đăng nhập bằng:</p>
            <div class="d-flex justify-content-center gap-2">
                <button @click="facebookLogin" class="btn btn-facebook social-btn w-100">
                    <i class="fa-brands fa-facebook me-2"></i> Facebook
                </button>
                <button @click="googleLogin" class="btn btn-google social-btn w-100">
                    <i class="fa-brands fa-google me-2"></i> Google
                </button>
            </div>
        </div>

        <div class="text-center mt-3">
            <NuxtLink to="/register" class="text-center mt-3 text-decoration-none text-muted">Chưa có tài khoản?
                <span class="toggle-form">Đăng ký ngay</span>
            </NuxtLink>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAuth } from '~/composables/useAuth'
import Swal from 'sweetalert2'
import useCarts from '~/composables/useCarts'

const { login, googleLogin, facebookLogin } = useAuth()
const { transferCartFromSessionToUser, fetchCart } = useCarts()

const form = reactive({
    email: '',
    password: ''
})

const error = reactive({
    email: '',
    password: ''
})

const isLoading = ref(false)
const rememberMe = ref(false)
const showPassword = ref(false)

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

const mergeCartAfterLogin = async () => {
    await transferCartFromSessionToUser()
    await fetchCart()
    // Có thể hiển thị thông báo nếu muốn
}

const handleLogin = async () => {
    resetErrors()
    isLoading.value = true

    try {
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

        const success = await login({
            email: form.email,
            password: form.password
        })

        if (success) {
            // Lưu hoặc xóa thông tin đăng nhập tùy thuộc vào checkbox
            if (rememberMe.value) {
                localStorage.setItem('rememberedEmail', form.email)
                localStorage.setItem('rememberedPassword', form.password)
            } else {
                localStorage.removeItem('rememberedEmail')
                localStorage.removeItem('rememberedPassword')
            }

            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            })

            Toast.fire({
                icon: 'success',
                title: 'Đăng nhập thành công!'
            })

            // Hợp nhất cart sau khi đăng nhập
            await mergeCartAfterLogin()

            // navigateTo('/')
            window.location.href = '/'
        }
    } catch (err) {
        console.error('Login error:', err.response?.data || err.message)

        const errorMessage = err.response?.data?.message || 'Đăng nhập thất bại. Vui lòng thử lại.'
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
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
}

.toggle-form {
    cursor: pointer;
    color: #0d6efd;
}

.toggle-form:hover {
    text-decoration: underline;
}

.social-btn {
    font-size: 16px;
    padding: 10px;
}

.btn-facebook {
    background-color: #3b5998;
    color: white;
}

.btn-google {
    background-color: #db4437;
    color: white;
}

.btn-facebook:hover {
    background-color: #ffffff;
    color: #3b5998;
    border: 1px solid #3b5998;
}

.btn-google:hover {
    background-color: #ffffff;
    color: #db4437;
    border: 1px solid #db4437;
}
</style>