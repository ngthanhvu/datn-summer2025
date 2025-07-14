<template>
    <div class="tw-bg-white tw-p-6 tw-rounded tw-shadow">
        <h2 class="tw-font-bold tw-text-lg tw-mb-4">Thông tin cá nhân</h2>
        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8">
            <!-- Cột trái: Thông tin cá nhân -->
            <div class="tw-space-y-4">
                <div>
                    <label class="tw-block tw-font-medium tw-mb-1">Tên người dùng</label>
                    <input v-model="formData.username" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded"
                        placeholder="Nhập tên người dùng" />
                </div>
                <div>
                    <label class="tw-block tw-font-medium tw-mb-1">Email</label>
                    <input v-model="formData.email" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded"
                        placeholder="Nhập email" disabled />
                </div>
                <div>
                    <label class="tw-block tw-font-medium tw-mb-1">Số điện thoại</label>
                    <input v-model="formData.phone" type="tel" class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded"
                        placeholder="Nhập số điện thoại" />
                </div>
                <div>
                    <label class="tw-block tw-font-medium tw-mb-1">Giới tính</label>
                    <div
                        class="tw-flex tw-bg-white tw-rounded tw-border tw-overflow-hidden tw-divide-x tw-divide-gray-200 tw-mt-1">
                        <label
                            class="tw-flex tw-items-center tw-justify-center tw-gap-2 tw-px-3 tw-py-2 tw-cursor-pointer tw-flex-1"
                            :class="formData.gender === 'male' ? 'tw-border-[#81aacc] tw-border-2 tw-bg-[#eaf3fa]' : ''">
                            <input type="radio" value="male" v-model="formData.gender"
                                class="tw-form-radio tw-h-5 tw-w-5 accent-[#81aacc] focus:tw-ring-0" />
                            <span class="tw-text-base">Nam</span>
                        </label>
                        <label
                            class="tw-flex tw-items-center tw-justify-center tw-gap-2 tw-px-3 tw-py-2 tw-cursor-pointer tw-flex-1"
                            :class="formData.gender === 'female' ? 'tw-border-[#81aacc] tw-border-2 tw-bg-[#eaf3fa]' : ''">
                            <input type="radio" value="female" v-model="formData.gender"
                                class="tw-form-radio tw-h-5 tw-w-5 accent-[#81aacc] focus:tw-ring-0" />
                            <span class="tw-text-base">Nữ</span>
                        </label>
                        <label
                            class="tw-flex tw-items-center tw-justify-center tw-gap-2 tw-px-3 tw-py-2 tw-cursor-pointer tw-flex-1"
                            :class="formData.gender === 'other' ? 'tw-border-[#81aacc] tw-border-2 tw-bg-[#eaf3fa]' : ''">
                            <input type="radio" value="other" v-model="formData.gender"
                                class="tw-form-radio tw-h-5 tw-w-5 accent-[#81aacc] focus:tw-ring-0" />
                            <span class="tw-text-base">Khác</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="tw-block tw-font-medium tw-mb-1">Ngày sinh</label>
                    <div class="tw-flex tw-gap-2">
                        <select v-model="dateOfBirth.day" class="tw-px-3 tw-py-2 tw-border tw-rounded tw-w-1/3">
                            <option value="">Ngày</option>
                            <option v-for="d in 31" :key="d" :value="String(d).padStart(2, '0')">{{ d }}</option>
                        </select>
                        <select v-model="dateOfBirth.month" class="tw-px-3 tw-py-2 tw-border tw-rounded tw-w-1/3">
                            <option value="">Tháng</option>
                            <option v-for="m in 12" :key="m" :value="String(m).padStart(2, '0')">{{ m }}</option>
                        </select>
                        <select v-model="dateOfBirth.year" class="tw-px-3 tw-py-2 tw-border tw-rounded tw-w-1/3">
                            <option value="">Năm</option>
                            <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                        </select>
                    </div>
                </div>
                <button @click="updateProfile"
                    class="tw-mt-2 tw-bg-[#81AACC] tw-text-white tw-px-4 tw-py-2 tw-rounded hover:tw-bg-[#5b98ca]"
                    :disabled="loading">
                    <span v-if="loading">Đang cập nhật...</span>
                    <span v-else>Lưu thay đổi</span>
                </button>
            </div>

            <!-- Cột phải: Avatar -->
            <div class="tw-flex tw-flex-col tw-items-center tw-justify-center">
                <div class="tw-relative tw-mb-4">
                    <img :src="userAvatar" alt="Avatar"
                        class="tw-w-32 tw-h-32 tw-rounded-full tw-object-cover tw-border-4 tw-border-gray-200" />
                    <label
                        class="tw-absolute tw-bottom-0 tw-right-0 tw-bg-[#81AACC] tw-text-white tw-p-2 tw-rounded-full hover:tw-bg-[#5b98ca] tw-cursor-pointer tw-shadow-lg">
                        <input type="file" class="tw-hidden" @change="handleAvatarChange" accept="image/*" />
                        <svg xmlns="http://www.w3.org/2000/svg" class="tw-w-4 tw-h-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </label>
                </div>
                <div class="tw-text-center">
                    <h3 class="tw-font-medium tw-mb-1">Ảnh đại diện</h3>
                    <p class="tw-text-sm tw-text-gray-500">JPG, GIF hoặc PNG. Tối đa 2MB.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
useHead({
    title: 'Thông tin cá nhân',
    meta: [
        {
            name: 'description',
            content: 'Cập nhật thông tin cá nhân',
        },
    ],
})
import { ref, computed, onMounted, watch } from 'vue'

const { user, getUser, updateUserProfile } = useAuth()
const loading = ref(false)
const avatarFile = ref(null)
const config = useRuntimeConfig()
const notyf = useNuxtApp().$notyf

const formData = ref({
    username: '',
    email: '',
    phone: '',
    gender: '',
    dateOfBirth: '',
})

const dateOfBirth = ref({
    day: '',
    month: '',
    year: '',
})

const years = computed(() => {
    const currentYear = new Date().getFullYear()
    const arr = []
    for (let y = currentYear; y >= 1900; y--) {
        arr.push(String(y))
    }
    return arr
})

const userAvatar = computed(() => {
    if (avatarFile.value) {
        return URL.createObjectURL(avatarFile.value)
    }
    if (user.value?.avatar) {
        if (user.value.avatar.startsWith('http')) {
            return user.value.avatar
        }
        return `${config.public.apiBaseUrl}${user.value.avatar}`
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
        dateOfBirth: user.value?.dateOfBirth || '',
    }
    // Parse dateOfBirth to day/month/year
    if (formData.value.dateOfBirth) {
        const [year, month, day] = formData.value.dateOfBirth.split('-')
        dateOfBirth.value = {
            day: day || '',
            month: month || '',
            year: year || '',
        }
    }
})

watch(dateOfBirth, (val) => {
    if (val.day && val.month && val.year) {
        formData.value.dateOfBirth = `${val.year}-${val.month}-${val.day}`
    } else {
        formData.value.dateOfBirth = ''
    }
}, { deep: true })

const handleAvatarChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            notyf.error('Kích thước file quá lớn. Vui lòng chọn file nhỏ hơn 2MB.')
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

        const { $notyf } = useNuxtApp()
        $notyf.success('Cập nhật thông tin thành công')
    } catch (error) {
        console.error('Lỗi khi cập nhật thông tin:', error.response?.data || error.message)
        const { $notyf } = useNuxtApp()
        $notyf.error(error.response?.data?.error || 'Có lỗi xảy ra khi cập nhật thông tin')
    } finally {
        loading.value = false
    }
}

</script>