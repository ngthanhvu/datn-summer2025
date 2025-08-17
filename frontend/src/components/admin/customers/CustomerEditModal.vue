<template>
    <div v-if="isVisible" class="fixed inset-0 bg-gray-900/70 bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-auto max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    Chỉnh sửa thông tin khách hàng
                </h3>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleSubmit" class="p-4 sm:p-6">
                <div class="space-y-4">
                    <!-- Avatar -->
                    <div class="flex justify-center mb-4">
                        <div class="relative">
                            <img :src="formData.avatar || defaultAvatar" :alt="formData.username"
                                class="w-20 h-20 rounded-full object-cover border-2 border-gray-200" />
                            <button type="button" @click="changeAvatar"
                                class="absolute bottom-0 right-0 bg-primary text-white rounded-full p-1 hover:bg-primary-dark transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                            Tên người dùng <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="username" v-model="formData.username" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            :class="{ 'border-red-500': errors.username }" />
                        <p v-if="errors.username" class="text-red-500 text-xs mt-1">{{ errors.username }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" v-model="formData.email" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            :class="{ 'border-red-500': errors.email }" />
                        <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</p>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                            Số điện thoại
                        </label>
                        <input type="tel" id="phone" v-model="formData.phone"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            :class="{ 'border-red-500': errors.phone }" />
                        <p v-if="errors.phone" class="text-red-500 text-xs mt-1">{{ errors.phone }}</p>
                    </div>

                    <!-- Role -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                            Vai trò <span class="text-red-500">*</span>
                        </label>
                        <select id="role" v-model="formData.role" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            :class="{ 'border-red-500': errors.role }">
                            <option value="">Chọn vai trò</option>
                            <option value="user">Người dùng</option>
                            <option value="admin">Quản trị viên</option>
                            <option v-if="currentUserRole === 'master_admin'" value="master_admin">Master Admin</option>
                        </select>
                        <p v-if="errors.role" class="text-red-500 text-xs mt-1">{{ errors.role }}</p>
                        <p class="text-gray-500 text-xs mt-1">Chọn vai trò cho người dùng này</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Mật khẩu mới
                        </label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" id="password" v-model="formData.password"
                                class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                :class="{ 'border-red-500': errors.password }" />
                            <button type="button" @click="togglePassword"
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
                        <p class="text-gray-500 text-xs mt-1">Để trống nếu không muốn thay đổi mật khẩu</p>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Trạng thái tài khoản
                        </label>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 space-y-2 sm:space-y-0">
                            <label class="flex items-center">
                                <input type="radio" v-model="formData.status" :value="1"
                                    class="w-4 h-4 text-primary border-gray-300 focus:ring-primary cursor-pointer" />
                                <span class="ml-2 text-sm text-gray-700">Đang hoạt động</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" v-model="formData.status" :value="0"
                                    class="w-4 h-4 text-primary border-gray-300 focus:ring-primary cursor-pointer" />
                                <span class="ml-2 text-sm text-gray-700">Đã khóa</span>
                            </label>
                        </div>
                    </div>

                    <!-- Note - Only show when status is 0 (banned) -->
                    <div v-if="formData.status === 0">
                        <label for="note" class="block text-sm font-medium text-gray-700 mb-1">
                            Lý do khóa tài khoản <span class="text-red-500">*</span>
                        </label>
                        <textarea id="note" v-model="formData.note" rows="3" required
                            placeholder="Nhập lý do khóa tài khoản..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
                            :class="{ 'border-red-500': errors.note }"></textarea>
                        <p v-if="errors.note" class="text-red-500 text-xs mt-1">{{ errors.note }}</p>
                        <p class="text-gray-500 text-xs mt-1">Lý do khóa tài khoản là bắt buộc</p>
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
                        <span v-if="isSubmitting">Đang lưu...</span>
                        <span v-else>Lưu thay đổi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'

const props = defineProps({
    isVisible: {
        type: Boolean,
        default: false
    },
    customer: {
        type: Object,
        default: () => ({})
    },
    currentUserRole: {
        type: String,
        default: 'user'
    }
})

const emit = defineEmits(['close', 'save'])

const defaultAvatar = ref('https://img.freepik.com/premium-vector/error-404-found-glitch-effect_8024-4.jpg')
const showPassword = ref(false)
const isSubmitting = ref(false)

const formData = reactive({
    username: '',
    email: '',
    phone: '',
    password: '',
    status: 1, // 1 = active, 0 = inactive
    avatar: '',
    note: '', // Added for note field
    role: '' // Added for role field
})

const errors = reactive({
    username: '',
    email: '',
    phone: '',
    password: '',
    note: '',
    role: '' // Added for role error
})

// Watch for customer changes to populate form
watch(() => props.customer, (newCustomer) => {
    if (newCustomer && Object.keys(newCustomer).length > 0) {
        formData.username = newCustomer.username || ''
        formData.email = newCustomer.email || ''
        formData.phone = newCustomer.phone || ''
        formData.status = newCustomer.status !== undefined ? newCustomer.status : 1
        formData.avatar = newCustomer.avatar || ''
        formData.password = '' // Reset password field
        formData.note = newCustomer.note || '' // Populate note field
        formData.role = newCustomer.role || '' // Populate role field
    }
}, { immediate: true })

const closeModal = () => {
    emit('close')
    resetForm()
}

const resetForm = () => {
    Object.keys(formData).forEach(key => {
        if (key !== 'status') {
            formData[key] = ''
        }
    })
    formData.status = 1
    formData.note = '' // Reset note field
    formData.role = '' // Reset role field
    Object.keys(errors).forEach(key => {
        errors[key] = ''
    })
    showPassword.value = false
    isSubmitting.value = false
}

const togglePassword = () => {
    showPassword.value = !showPassword.value
}

const changeAvatar = () => {
}

const validateForm = () => {
    let isValid = true

    Object.keys(errors).forEach(key => {
        errors[key] = ''
    })

    if (!formData.username.trim()) {
        errors.username = 'Tên người dùng không được để trống'
        isValid = false
    } else if (formData.username.length < 3) {
        errors.username = 'Tên người dùng phải có ít nhất 3 ký tự'
        isValid = false
    }

    if (!formData.email.trim()) {
        errors.email = 'Email không được để trống'
        isValid = false
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
        errors.email = 'Email không hợp lệ'
        isValid = false
    }

    if (formData.phone && !/^[0-9+\-\s()]+$/.test(formData.phone)) {
        errors.phone = 'Số điện thoại không hợp lệ'
        isValid = false
    }

    if (!formData.role) {
        errors.role = 'Vai trò là bắt buộc'
        isValid = false
    }

    if (formData.password && formData.password.length < 6) {
        errors.password = 'Mật khẩu phải có ít nhất 6 ký tự'
        isValid = false
    }

    if (formData.status === 0 && !formData.note.trim()) {
        errors.note = 'Lý do khóa tài khoản là bắt buộc'
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
        const updateData = {
            id: props.customer.id,
            username: formData.username.trim(),
            email: formData.email.trim(),
            phone: formData.phone.trim() || null,
            status: formData.status,
            role: formData.role // Include role in update data
        }

        if (formData.status === 0) {
            updateData.note = formData.note.trim()
        }

        if (formData.password.trim()) {
            updateData.password = formData.password
        }

        emit('save', updateData)
        closeModal()
    } catch (error) {
        console.error('Error updating customer:', error)
    } finally {
        isSubmitting.value = false
    }
}
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