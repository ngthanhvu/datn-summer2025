<template>
    <div class="tw-flex tw-justify-between tw-items-center tw-mb-6 tw-pt-6 tw-pl-6 mx-auto">
        <div>
            <h1 class="tw-text-2xl tw-font-semibold tw-text-gray-900">Thêm danh mục mới</h1>
            <p class="tw-text-gray-600">Điền thông tin để tạo danh mục mới</p>
        </div>
    </div>
    <div class="tw-bg-white tw-p-10 tw-w-[50%] mx-auto tw-rounded-[10px] tw-border tw-border-gray-250">
        <div class="tw-mb-4">
            <label class="tw-block tw-font-medium tw-mb-1">Tên danh mục <span class="tw-text-red-500">*</span></label>
            <input v-model="formData.name" type="text"
                class="tw-w-full tw-border tw-rounded tw-px-3 tw-py-2 focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                placeholder="Nhập tên danh mục" />
            <div v-if="formErrors.name" class="tw-text-red-500 tw-text-sm">{{ formErrors.name }}</div>
        </div>

        <div class="tw-mb-4">
            <label class="tw-block tw-font-medium tw-mb-1">Mô tả</label>
            <textarea v-model="formData.description"
                class="tw-w-full tw-border tw-rounded tw-px-3 tw-py-2 focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                placeholder="Nhập mô tả danh mục" rows="4"></textarea>
            <div v-if="formErrors.description" class="tw-text-red-500 tw-text-sm">{{ formErrors.description }}</div>
        </div>

        <div class="tw-mb-4">
            <label class="tw-block tw-font-medium tw-mb-1">Danh mục cha</label>
            <select v-model="formData.parent_id"
                class="tw-w-full tw-border tw-rounded tw-px-3 tw-py-2 focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100">
                <option value="">Chọn danh mục cha</option>
                <option v-for="option in parentOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                </option>
            </select>
            <div v-if="formErrors.parent_id" class="tw-text-red-500 tw-text-sm">{{ formErrors.parent_id }}</div>
        </div>

        <div class="tw-mb-4 tw-flex tw-items-center">
            <label class="tw-block tw-font-medium tw-mb-1 tw-mr-2">Trạng thái</label>
            <button @click="formData.is_active = !formData.is_active" :class="[
                'tw-relative tw-inline-flex tw-h-6 tw-w-11 tw-items-center tw-rounded-full tw-transition-colors tw-focus:outline-none tw-focus:ring-2 tw-focus:ring-blue-500 tw-focus:ring-offset-2',
                formData.is_active ? 'tw-bg-[#3BB77E]' : 'tw-bg-gray-200'
            ]">
                <span :class="[
                    'tw-inline-block tw-h-4 tw-w-4 tw-transform tw-rounded-full tw-bg-white tw-transition-transform',
                    formData.is_active ? 'tw-translate-x-6' : 'tw-translate-x-1'
                ]"></span>
            </button>
            <span class="tw-ml-2">{{ formData.is_active ? 'Kích hoạt' : 'Ẩn' }}</span>
        </div>

        <div class="tw-mb-4">
            <label class="tw-block tw-font-medium tw-mb-1">
                Hình ảnh <span class="tw-text-red-500">*</span>
            </label>

            <div class="tw-relative tw-border-2 tw-border-dashed tw-border-gray-300 tw-rounded-lg tw-p-6 tw-text-center hover:tw-border-primary tw-cursor-pointer"
                @click="$refs.imageInput.click()">
                <input ref="imageInput" type="file" class="tw-hidden" accept="image/png, image/jpeg, image/gif"
                    @change="onImageChange" />
                <div class="tw-flex tw-flex-col tw-items-center">
                    <i class="fas fa-cloud-upload-alt tw-text-3xl tw-text-gray-400"></i>
                    <p class="tw-mt-2 tw-text-gray-600">Click để tải ảnh lên</p>
                    <p class="tw-text-xs tw-text-gray-400">PNG, JPG, GIF (tối đa 2MB)</p>
                </div>
            </div>

            <div v-if="formErrors.image" class="tw-text-red-500 tw-text-sm tw-mt-1">
                {{ formErrors.image }}
            </div>

            <div v-if="imagePreview" class="tw-mt-4 tw-relative tw-inline-block">
                <img :src="imagePreview" alt="Preview" class="tw-max-h-40 tw-rounded-lg tw-shadow tw-object-cover" />
                <button @click="removeImage"
                    class="tw-absolute tw-top-1 tw-right-1 tw-bg-white tw-rounded-full tw-p-1 tw-shadow hover:tw-bg-gray-100"
                    title="Xóa ảnh">
                    <i class="fas fa-times tw-text-red-500"></i>
                </button>
            </div>
        </div>

        <div class="tw-flex tw-justify-end tw-gap-4 tw-mt-6">
            <NuxtLink to="/admin/categories"
                class="tw-px-4 tw-py-2 tw-border tw-rounded tw-text-gray-600 hover:tw-bg-gray-50">
                Hủy
            </NuxtLink>
            <button @click="handleSubmit"
                class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 hover:tw-bg-primary-dark">
                Tạo danh mục
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useCategoryStore } from '~/stores/useCategoryStore.js'
import { useNuxtApp, navigateTo } from '#app'

const notyf = useNuxtApp().$notyf
const categoryStore = useCategoryStore()

const formData = ref({
    name: '',
    description: '',
    image: null,
    parent_id: '',
    is_active: true
})
const imageData = ref(null)
const imagePreview = ref(null)

const parentOptions = ref([])
const formErrors = ref({
    name: '',
    description: '',
    parent_id: '',
    is_active: '',
    image: ''
})

function onImageChange(e) {
    const file = e.target.files[0]
    if (file) {
        if (file.size > 2 * 1024 * 1024) { // 2MB
            formErrors.value.image = 'Dung lượng ảnh tối đa 2MB'
            return
        }
        imageData.value = file
        imagePreview.value = URL.createObjectURL(file)
        formErrors.value.image = ''
    }
}

onMounted(async () => {
    try {
        await categoryStore.fetchCategories()
        parentOptions.value = categoryStore.categories.map(cat => ({
            value: cat.id,
            label: cat.name
        }))
    } catch (e) {
        parentOptions.value = []
    }
})

const validateForm = () => {
    const errors = { ...formErrors.value }
    let hasError = false

    if (!formData.value.name) {
        errors.name = 'Vui lòng nhập tên danh mục'
        hasError = true
    } else if (formData.value.name.length < 3) {
        errors.name = 'Tên danh mục phải có ít nhất 3 ký tự'
        hasError = true
    }

    if (!formData.value.description) {
        errors.description = 'Vui lòng nhập mô tả danh mục'
        hasError = true
    }

    if (!imageData.value) {
        errors.image = 'Vui lòng chọn hình ảnh'
        hasError = true
    }

    formErrors.value = errors
    return !hasError
}

const handleSubmit = async () => {
    if (!validateForm()) {
        return
    }

    const formDataToSend = new FormData()
    formDataToSend.append('name', formData.value.name)
    formDataToSend.append('description', formData.value.description || '')

    const isActive = formData.value.is_active === undefined ? true : Boolean(formData.value.is_active)
    formDataToSend.append('is_active', isActive ? '1' : '0')

    const parentId = formData.value.parent_id
    if (parentId && parentId !== '') {
        formDataToSend.append('parent_id', parentId.toString())
    }

    if (imageData.value instanceof File) {
        formDataToSend.append('image', imageData.value)
    } else if (typeof imageData.value === 'string' && imageData.value.startsWith('data:')) {
        const response = await fetch(imageData.value)
        const blob = await response.blob()
        formDataToSend.append('image', blob, 'image.jpg')
    } else if (typeof imageData.value === 'string') {
        formDataToSend.append('image', imageData.value)
    }

    try {
        const result = await categoryStore.createCategory(formDataToSend)
        if (result) {
            notyf.success('Tạo danh mục thành công')
            await navigateTo('/admin/categories')
        }
    } catch (error) {
        console.error('Error creating category:', error)
        const errorMessage = error.response?.data?.message || 'Có lỗi xảy ra khi tạo danh mục'
        notyf.error(errorMessage)
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

.tw-border-primary {
    border-color: #3bb77e;
}
</style>