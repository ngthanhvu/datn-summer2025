<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
            <div>
                <h1 class="tw-text-2xl tw-font-semibold tw-text-gray-900">Chỉnh sửa danh mục</h1>
                <p class="tw-text-gray-600">Cập nhật thông tin danh mục</p>
            </div>
            <NuxtLink to="/admin/categories"
                class="tw-bg-gray-100 tw-text-gray-600 tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-gray-200">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </NuxtLink>
        </div>

        <div v-if="category">
            <div class="tw-mb-6">
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Tên danh mục *</label>
                <input type="text" v-model="formData.name" required
                    class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-primary"
                    placeholder="Nhập tên danh mục">
            </div>

            <div class="tw-mb-6">
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Mô tả</label>
                <textarea v-model="formData.description" rows="4"
                    class="tw-w-full tw-px-3 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-primary"
                    placeholder="Nhập mô tả danh mục"></textarea>
            </div>

            <div class="tw-mb-6">
                <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-2">Ảnh danh mục</label>
                <div class="tw-flex tw-items-start tw-gap-4">
                    <img v-if="formData.image" :src="formData.image" alt="Current image"
                        class="tw-w-32 tw-h-32 tw-object-cover tw-rounded">
                    <div>
                        <input type="file" @change="handleImageChange" accept="image/*"
                            class="tw-block tw-w-full tw-text-sm tw-text-gray-500 file:tw-mr-4 file:tw-py-2 file:tw-px-4 file:tw-rounded-md file:tw-border-0 file:tw-text-sm file:tw-font-semibold file:tw-bg-primary file:tw-text-white hover:file:tw-bg-primary-dark">
                        <p class="tw-mt-1 tw-text-sm tw-text-gray-500">PNG, JPG, GIF tối đa 2MB</p>
                    </div>
                </div>
            </div>

            <div class="tw-mb-6">
                <label class="tw-flex tw-items-center tw-gap-2">
                    <input type="checkbox" v-model="formData.is_active"
                        class="tw-rounded tw-text-primary focus:tw-ring-primary">
                    <span class="tw-text-sm tw-font-medium tw-text-gray-700">Kích hoạt</span>
                </label>
            </div>

            <div class="tw-flex tw-justify-end tw-gap-4">
                <NuxtLink to="/admin/categories"
                    class="tw-px-4 tw-py-2 tw-border tw-rounded tw-text-gray-600 hover:tw-bg-gray-50">
                    Hủy
                </NuxtLink>
                <button @click="handleSubmit"
                    class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 hover:tw-bg-primary-dark">
                    Lưu thay đổi
                </button>
            </div>
        </div>
        <div v-else class="tw-text-center tw-py-8">
            <p class="tw-text-gray-500">Đang tải...</p>
        </div>
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin'
})

import { ref, onMounted } from 'vue'

const route = useRoute()
const category = ref(null)
const imageFile = ref(null)
const formData = ref({
    name: '',
    description: '',
    image: '',
    is_active: true
})

const { getCategoryById, updateCategory } = useCategory()

const handleImageChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        imageFile.value = file
        // Tạo URL preview cho ảnh đã chọn
        formData.value.image = URL.createObjectURL(file)
    }
}

onMounted(async () => {
    try {
        const categoryData = await getCategoryById(route.params.id)
        if (categoryData) {
            category.value = categoryData
            formData.value = {
                name: categoryData.name || '',
                description: categoryData.description || '',
                image: categoryData.image || '',
                is_active: !!categoryData.is_active
            }
            console.log('Loaded category data:', formData.value)
        }
    } catch (error) {
        console.error('Error fetching category:', error)
        alert('Không thể tải thông tin danh mục')
    }
})

const handleSubmit = async () => {
    try {
        if (!formData.value.name?.trim()) {
            alert('Vui lòng nhập tên danh mục')
            return
        }

        const formDataToSend = new FormData()
        formDataToSend.append('name', formData.value.name.trim())
        formDataToSend.append('description', formData.value.description?.trim() || '')
        formDataToSend.append('is_active', formData.value.is_active ? '1' : '0')

        if (imageFile.value) {
            formDataToSend.append('image', imageFile.value)
        }

        // Log dữ liệu trước khi gửi
        console.log('Form data before sending:')
        for (let [key, value] of formDataToSend.entries()) {
            console.log(`${key}:`, value)
        }

        const result = await updateCategory(route.params.id, formDataToSend)
        console.log('Update result:', result)

        if (result) {
            alert('Cập nhật danh mục thành công!')
            await navigateTo('/admin/categories')
        }
    } catch (error) {
        console.error('Error updating category:', error.response?.data || error)
        const errorMessage = error.response?.data?.error || 'Có lỗi xảy ra khi cập nhật danh mục'
        alert(errorMessage)
    }
}
</script>

<style scoped>
.tw-bg-primary {
    background-color: #3bb77e;
}

.tw-bg-primary-dark {
    background-color: #2ea16d;
}
</style>