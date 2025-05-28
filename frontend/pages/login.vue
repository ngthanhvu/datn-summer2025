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
        <input v-model="form.password" type="password" class="form-control" name="password" id="loginPassword"
          placeholder="Nhập mật khẩu" :class="{ 'is-invalid': error.password }">
        <div class="invalid-feedback" v-if="error.password">{{ error.password }}</div>
      </div>
      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="rememberMe">
        <label class="form-check-label" for="rememberMe">Ghi nhớ tôi</label>
        <a href="/quen-mat-khau" class="float-end text-decoration-none">Quên mật khẩu?</a>
      </div>
      <button type="submit" class="tw-bg-[#81AACC] tw-text-white hover:tw-bg-[#66a2d3] w-100 tw-py-2 tw-rounded-lg">Đăng
        Nhập</button>
    </form>

    <!-- Đăng nhập bằng mạng xã hội -->
    <div class="text-center mt-3">
      <p class="mb-2">Hoặc đăng nhập bằng:</p>
      <div class="d-flex justify-content-center gap-2">
        <a href="/login/facebook" class="btn btn-facebook social-btn w-100">
          <i class="fa-brands fa-facebook me-2"></i> Facebook
        </a>
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
definePageMeta({
  layout: 'default',
  middleware: 'guest'
})
useHead({
  title: 'Đăng Nhập - DEVGANG',
  meta: [
    { name: 'description', content: 'Đăng Nhập - DEVGANG' },
  ],
});
import { ref, reactive } from 'vue'
import { useAuth } from '../composables/useAuth'
import Swal from 'sweetalert2'

const { login, googleLogin } = useAuth()

const form = reactive({
  email: '',
  password: ''
})

const error = reactive({
  email: '',
  password: ''
})

const isLoading = ref(false)

const resetErrors = () => {
  error.email = ''
  error.password = ''
}

const handleLogin = async () => {
  resetErrors()
  isLoading.value = true

  try {
    let hasError = false

    // Validate email
    if (!form.email) {
      error.email = 'Vui lòng nhập email'
      hasError = true
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
      error.email = 'Email không hợp lệ'
      hasError = true
    }

    // Validate password
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
      isLoading.value = false
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

      navigateTo('/')
    }
  } catch (err) {
    isLoading.value = false
    console.error('Login error:', err.response?.data || err.message)

    const errorMessage = err.response?.data?.message || 'Đăng nhập thất bại. Vui lòng thử lại.'
    Swal.fire({
      icon: 'error',
      title: 'Lỗi!',
      text: errorMessage
    })
  }
}
</script>

<style>
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