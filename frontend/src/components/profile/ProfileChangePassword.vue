<template>
    <div class="bg-white rounded-lg shadow-sm border border-gray-300 p-6">
        <h2 class="font-bold text-lg mb-4">Đổi mật khẩu</h2>
        <div class="space-y-4">
            <div>
                <label class="block font-medium mb-1">Mật khẩu hiện tại</label>
                <input type="password"
                    class="w-full px-3 py-2 border border-gray-300 focus:outline-none focus:ring-1 focus:ring-[#81AACC] rounded bg-gray-100"
                    v-model="currentPassword" placeholder="Nhập mật khẩu hiện tại" :disabled="isLoading" />
            </div>
            <div>
                <label class="block font-medium mb-1">Mật khẩu mới</label>
                <input type="password"
                    class="w-full px-3 py-2 border border-gray-300 focus:outline-none focus:ring-1 focus:ring-[#81AACC] rounded bg-gray-100"
                    v-model="newPassword" placeholder="Nhập mật khẩu mới" :disabled="isLoading" />
            </div>
            <div>
                <label class="block font-medium mb-1">Xác nhận mật khẩu mới</label>
                <input type="password"
                    class="w-full px-3 py-2 border border-gray-300 focus:outline-none focus:ring-1 focus:ring-[#81AACC] rounded bg-gray-100"
                    v-model="newPasswordConfirmation" placeholder="Nhập lại mật khẩu mới" :disabled="isLoading" />
            </div>
            <button @click="handleChangePassword"
                class="mt-2 bg-[#81AACC] text-white px-4 py-2 rounded hover:bg-[#5b98ca] disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="isLoading">
                {{ isLoading ? 'Đang xử lý...' : 'Đổi mật khẩu' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuth } from '../../composable/useAuth'
import Swal from 'sweetalert2'

const { resetPasswordProfile } = useAuth()

const currentPassword = ref('')
const newPassword = ref('')
const newPasswordConfirmation = ref('')
const isLoading = ref(false)

const handleChangePassword = async () => {
    try {
        if (!currentPassword.value || !newPassword.value || !newPasswordConfirmation.value) {
            return Swal.fire({
                title: 'Lỗi',
                text: 'Vui lòng nhập đầy đủ thông tin',
                icon: 'error',
                confirmButtonText: 'Đóng'
            })
        }

        if (newPassword.value !== newPasswordConfirmation.value) {
            return Swal.fire({
                title: 'Lỗi',
                text: 'Mật khẩu mới và xác nhận mật khẩu không khớp',
                icon: 'error',
                confirmButtonText: 'Đóng'
            })
        }

        isLoading.value = true

        await resetPasswordProfile(
            currentPassword.value,
            newPassword.value,
            newPasswordConfirmation.value
        )

        await Swal.fire({
            title: 'Thành công',
            text: 'Đổi mật khẩu thành công',
            icon: 'success',
            confirmButtonText: 'Đóng'
        })

        // Reset form
        currentPassword.value = ''
        newPassword.value = ''
        newPasswordConfirmation.value = ''

    } catch (error) {
        Swal.fire({
            title: 'Lỗi',
            text: error.response?.data?.error || 'Có lỗi xảy ra khi đổi mật khẩu',
            icon: 'error',
            confirmButtonText: 'Đóng'
        })
    } finally {
        isLoading.value = false
    }
}
</script>