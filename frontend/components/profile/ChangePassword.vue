<template>
    <div class="tw-bg-white tw-p-6 tw-rounded tw-shadow">
        <h2 class="tw-font-bold tw-text-lg tw-mb-4">Đổi mật khẩu</h2>
        <div class="tw-space-y-4">
            <div>
                <label class="tw-block tw-font-medium tw-mb-1">Mật khẩu hiện tại</label>
                <input type="password" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded tw-bg-gray-100"
                    v-model="currentPassword" placeholder="Nhập mật khẩu hiện tại" :disabled="isLoading" />
            </div>
            <div>
                <label class="tw-block tw-font-medium tw-mb-1">Mật khẩu mới</label>
                <input type="password" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded tw-bg-gray-100"
                    v-model="newPassword" placeholder="Nhập mật khẩu mới" :disabled="isLoading" />
            </div>
            <div>
                <label class="tw-block tw-font-medium tw-mb-1">Xác nhận mật khẩu mới</label>
                <input type="password" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded tw-bg-gray-100"
                    v-model="newPasswordConfirmation" placeholder="Nhập lại mật khẩu mới" :disabled="isLoading" />
            </div>
            <button @click="handleChangePassword" 
                class="tw-mt-2 tw-bg-[#81AACC] tw-text-white tw-px-4 tw-py-2 tw-rounded hover:tw-bg-[#5b98ca] disabled:tw-opacity-50 disabled:tw-cursor-not-allowed"
                :disabled="isLoading">
                {{ isLoading ? 'Đang xử lý...' : 'Đổi mật khẩu' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuth } from '~/composables/useAuth'
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