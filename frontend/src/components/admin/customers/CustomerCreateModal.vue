<template>
    <div v-if="isVisible" class="fixed inset-0 bg-gray-900/70 bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-auto max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    Thêm người dùng mới
                </h3>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleSubmit" class="p-4 sm:p-6">
                <div class="space-y-4">
                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                            Tên người dùng <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="username" v-model="formData.username" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            :class="{ 'border-red-500': errors.username }" placeholder="Nhập tên người dùng" />
                        <p v-if="errors.username" class="text-red-500 text-xs mt-1">{{ errors.username }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" v-model="formData.email" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            :class="{ 'border-red-500': errors.email }" placeholder="Nhập email" />
                        <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</p>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                            Số điện thoại
                        </label>
                        <input type="tel" id="phone" v-model="formData.phone"
                            class="w-full px-3 py-2 border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            :class="{ 'border-red-500': errors.phone }" placeholder="Nhập số điện thoại" />
                        <p v-if="errors.phone" class="text-red-500 text-xs mt-1">{{ errors.phone }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Mật khẩu <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" id="password" v-model="formData.password"
                                required
                                class="w-full px-3 py-2 pr-10 border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                :class="{ 'border-red-500': errors.password }"
                                placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)" />
                            <button type="button" @click="togglePassword" tabindex="-1"
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21">
                                    </path>
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                    </path>
                                </svg>
                            </button>
                        </div>
                        <p v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password }}</p>
                    </div>

                    <!-- Role -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                            Vai trò <span class="text-red-500">*</span>
                        </label>
                        <select id="role" v-model="formData.role" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            :class="{ 'border-red-500': errors.role }">
                            <option value="">Chọn vai trò</option>
                            <option value="user">Người dùng</option>
                            <option value="admin">Quản trị viên</option>
                            <option v-if="currentUserRole === 'master_admin'" value="master_admin">Master Admin</option>
                        </select>
                        <p v-if="errors.role" class="text-red-500 text-xs mt-1">{{ errors.role }}</p>
                        <p class="text-gray-500 text-xs mt-1">Chọn vai trò cho người dùng này</p>
                    </div>

                    <!-- Gender -->
                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">
                            Giới tính
                        </label>
                        <select id="gender" v-model="formData.gender"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Chọn giới tính</option>
                            <option value="male">Nam</option>
                            <option value="female">Nữ</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>

                    <!-- Date of Birth -->
                    <div>
                        <label for="dateOfBirth" class="block text-sm font-medium text-gray-700 mb-1">
                            Ngày sinh
                        </label>
                        <input type="date" id="dateOfBirth" v-model="formData.dateOfBirth"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" />
                    </div>
                </div>

                <!-- Actions -->
                <div
                    class="flex flex-col sm:flex-row sm:justify-end sm:space-x-3 space-y-2 sm:space-y-0 mt-6 pt-4 border-t border-gray-200">
                    <button type="button" @click="closeModal"
                        class="w-full sm:w-auto px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors cursor-pointer">
                        Hủy
                    </button>
                    <button type="submit" :disabled="isSubmitting"
                        class="w-full sm:w-auto px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer">
                        <span v-if="isSubmitting">Đang tạo...</span>
                        <span v-else>Tạo người dùng</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
    isVisible: {
        type: Boolean,
        default: false
    },
    currentUserRole: {
        type: String,
        default: 'user'
    }
})

const emit = defineEmits(['close', 'save'])

const formData = ref({
    username: '',
    email: '',
    phone: '',
    password: '',
    role: '',
    gender: '',
    dateOfBirth: ''
})

const errors = ref({})
const isSubmitting = ref(false)
const showPassword = ref(false)

const closeModal = () => {
    resetForm()
    emit('close')
}

const resetForm = () => {
    formData.value = {
        username: '',
        email: '',
        phone: '',
        password: '',
        role: '',
        gender: '',
        dateOfBirth: ''
    }
    errors.value = {}
    isSubmitting.value = false
    showPassword.value = false
}

const togglePassword = () => {
    showPassword.value = !showPassword.value
}

const validateForm = () => {
    errors.value = {}
    let isValid = true

    // Validate username
    if (!formData.value.username.trim()) {
        errors.value.username = 'Tên người dùng không được để trống'
        isValid = false
    } else if (formData.value.username.length < 3) {
        errors.value.username = 'Tên người dùng phải có ít nhất 3 ký tự'
        isValid = false
    }

    // Validate email
    if (!formData.value.email.trim()) {
        errors.value.email = 'Email không được để trống'
        isValid = false
    } else {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
        if (!emailRegex.test(formData.value.email)) {
            errors.value.email = 'Email không hợp lệ'
            isValid = false
        }
    }

    // Validate password
    if (!formData.value.password) {
        errors.value.password = 'Mật khẩu không được để trống'
        isValid = false
    } else if (formData.value.password.length < 6) {
        errors.value.password = 'Mật khẩu phải có ít nhất 6 ký tự'
        isValid = false
    }

    // Validate role
    if (!formData.value.role) {
        errors.value.role = 'Vai trò là bắt buộc'
        isValid = false
    }

    // Validate phone (optional but if provided, must be valid)
    if (formData.value.phone && !/^[0-9+\-\s()]+$/.test(formData.value.phone)) {
        errors.value.phone = 'Số điện thoại không hợp lệ'
        isValid = false
    }

    return isValid
}

const handleSubmit = async () => {
    if (!validateForm()) {
        return
    }

    isSubmitting.value = true

    try {
        emit('save', { ...formData.value })
    } catch (error) {
        console.error('Error creating customer:', error)
    } finally {
        isSubmitting.value = false
    }
}

// Reset form when modal opens
watch(() => props.isVisible, (newValue) => {
    if (newValue) {
        resetForm()
    }
})
</script>

<style scoped>
.text-primary {
    color: #3bb77e;
}

.text-primary-dark {
    color: #2d9d6a;
}

.bg-primary {
    background-color: #3bb77e;
}

.bg-primary-dark {
    background-color: #2d9d6a;
}

.focus\:ring-primary:focus {
    --tw-ring-color: #3bb77e;
}
</style>