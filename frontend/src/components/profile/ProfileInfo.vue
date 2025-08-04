<template>
    <div class="bg-white rounded-lg shadow-sm border border-gray-300 p-6">
        <h2 class="font-bold text-xl mb-6 text-gray-900">Thông tin cá nhân</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Cột trái: Thông tin cá nhân -->
            <div class="space-y-5">
                <div>
                    <label class="block font-medium mb-2 text-gray-700">Tên người dùng</label>
                    <input v-model="formData.username"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#81AACC] transition-colors"
                        placeholder="Nhập tên người dùng" />
                </div>
                <div>
                    <label class="block font-medium mb-2 text-gray-700">Email</label>
                    <input v-model="formData.email"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-500"
                        placeholder="Nhập email" disabled />
                </div>
                <div>
                    <label class="block font-medium mb-2 text-gray-700">Số điện thoại</label>
                    <input v-model="formData.phone" type="tel"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#81AACC] transition-colors"
                        placeholder="Nhập số điện thoại" />
                </div>
                <div>
                    <label class="block font-medium mb-2 text-gray-700">Giới tính</label>
                    <div
                        class="flex bg-white rounded-lg border border-gray-300 overflow-hidden divide-x divide-gray-200">
                        <label
                            class="flex items-center justify-center gap-2 px-4 py-3 cursor-pointer flex-1 transition-colors"
                            :class="formData.gender === 'male' ? 'border-[#81aacc] border-2 bg-[#eaf3fa]' : 'hover:bg-gray-50'">
                            <input type="radio" value="male" v-model="formData.gender"
                                class="form-radio h-4 w-4 focus:ring-0" />
                            <span class="text-sm font-medium">Nam</span>
                        </label>
                        <label
                            class="flex items-center justify-center gap-2 px-4 py-3 cursor-pointer flex-1 transition-colors"
                            :class="formData.gender === 'female' ? 'border-[#81aacc] border-2 bg-[#eaf3fa]' : 'hover:bg-gray-50'">
                            <input type="radio" value="female" v-model="formData.gender"
                                class="form-radio h-4 w-4 focus:ring-0" />
                            <span class="text-sm font-medium">Nữ</span>
                        </label>
                        <label
                            class="flex items-center justify-center gap-2 px-4 py-3 cursor-pointer flex-1 transition-colors"
                            :class="formData.gender === 'other' ? 'border-[#81aacc] border-2 bg-[#eaf3fa]' : 'hover:bg-gray-50'">
                            <input type="radio" value="other" v-model="formData.gender"
                                class="form-radio h-4 w-4 focus:ring-0" />
                            <span class="text-sm font-medium">Khác</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block font-medium mb-2 text-gray-700">Ngày sinh</label>
                    <div class="flex gap-3">
                        <select v-model="dateOfBirth.day"
                            class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#81AACC] focus:border-[#81AACC] flex-1">
                            <option value="">Ngày</option>
                            <option v-for="d in 31" :key="d" :value="String(d).padStart(2, '0')">{{ d }}</option>
                        </select>
                        <select v-model="dateOfBirth.month"
                            class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#81AACC] focus:border-[#81AACC] flex-1">
                            <option value="">Tháng</option>
                            <option v-for="m in 12" :key="m" :value="String(m).padStart(2, '0')">{{ m }}</option>
                        </select>
                        <select v-model="dateOfBirth.year"
                            class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#81AACC] focus:border-[#81AACC] flex-1">
                            <option value="">Năm</option>
                            <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                        </select>
                    </div>
                </div>
                <button @click="updateProfile"
                    class="w-full bg-[#81AACC] text-white px-6 py-3 rounded-lg hover:bg-[#5b98ca] transition-colors font-medium"
                    :disabled="loading">
                    <span v-if="loading">Đang cập nhật...</span>
                    <span v-else>Lưu thay đổi</span>
                </button>
            </div>

            <!-- Cột phải: Avatar -->
            <div class="flex flex-col items-center justify-center lg:justify-start">
                <div class="relative mb-6">
                    <img :src="userAvatar" alt="Avatar"
                        class="w-32 h-32 rounded-full object-cover border-4 border-gray-200 shadow-lg" />
                    <label
                        class="absolute bottom-0 right-0 bg-[#81AACC] text-white p-3 rounded-full hover:bg-[#5b98ca] cursor-pointer shadow-lg transition-colors">
                        <input type="file" class="hidden" @change="handleAvatarChange" accept="image/*" />
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </label>
                </div>
                <div class="text-center">
                    <h3 class="font-medium mb-2 text-gray-900">Ảnh đại diện</h3>
                    <p class="text-sm text-gray-500">JPG, GIF hoặc PNG. Tối đa 2MB.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useAuth } from '../../composable/useAuth'
import Swal from 'sweetalert2'
import { useHead } from '@vueuse/head'

useHead({
    title: "Thông tin cá nhân | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Thông tin cá nhân"
        }
    ]
})

const { user, getUser, updateUserProfile } = useAuth()

const loading = ref(false)
const avatarFile = ref(null)

const formData = ref({
    username: '',
    email: '',
    phone: '',
    gender: '',
    dateOfBirth: ''
})

const dateOfBirth = ref({
    day: '',
    month: '',
    year: ''
})

const years = computed(() => {
    const currentYear = new Date().getFullYear()
    return Array.from({ length: currentYear - 1899 }, (_, i) => String(currentYear - i))
})

const userAvatar = computed(() => {
    if (avatarFile.value) {
        return URL.createObjectURL(avatarFile.value)
    }
    if (user.value?.avatar) {
        if (user.value.avatar.startsWith('http')) {
            return user.value.avatar
        }
        return `${import.meta.env.VITE_API_BASE_URL}${user.value.avatar}`
    }
    return 'https://placehold.co/50'
})

onMounted(async () => {
    await getUser()
    formData.value = {
        username: user.value?.username || '',
        email: user.value?.email || '',
        phone: user.value?.phone || '',
        gender: user.value?.gender || '',
        dateOfBirth: user.value?.dateOfBirth || ''
    }

    if (formData.value.dateOfBirth) {
        const [year, month, day] = formData.value.dateOfBirth.split('-')
        dateOfBirth.value = {
            day: day || '',
            month: month || '',
            year: year || ''
        }
    }
})

watch(
    dateOfBirth,
    (val) => {
        if (val.day && val.month && val.year) {
            formData.value.dateOfBirth = `${val.year}-${val.month}-${val.day}`
        } else {
            formData.value.dateOfBirth = ''
        }
    },
    { deep: true }
)

const handleAvatarChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            Swal.fire('Lỗi', 'Kích thước file quá lớn (tối đa 2MB)', 'error')
            return
        }
        avatarFile.value = file
    }
}

const updateProfile = async () => {
    try {
        loading.value = true
        const formDataToSend = new FormData()
        formDataToSend.append('username', formData.value.username)
        formDataToSend.append('phone', formData.value.phone)
        formDataToSend.append('gender', formData.value.gender)
        formDataToSend.append('dateOfBirth', formData.value.dateOfBirth)

        if (avatarFile.value) {
            formDataToSend.append('avatar', avatarFile.value)
        }

        await updateUserProfile(formDataToSend)
        await getUser()
        avatarFile.value = null

        Swal.fire('Thành công', 'Cập nhật thông tin thành công', 'success')
    } catch (error) {
        console.error('Lỗi khi cập nhật thông tin:', error.response?.data || error.message)
        Swal.fire('Lỗi', error.response?.data?.error || 'Có lỗi xảy ra khi cập nhật thông tin', 'error')
    } finally {
        loading.value = false
    }
}
</script>