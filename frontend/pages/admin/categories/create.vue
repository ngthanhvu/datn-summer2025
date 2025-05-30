<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
            <div>
                <h1 class="tw-text-2xl tw-font-semibold tw-text-gray-900">Thêm danh mục mới</h1>
                <p class="tw-text-gray-600">Điền thông tin để tạo danh mục mới</p>
            </div>
            <NuxtLink to="/admin/categories"
                class="tw-bg-gray-100 tw-text-gray-600 tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-gray-200">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </NuxtLink>
        </div>

        <Form :fields="formFields" :initial-data="formData" v-model="formData" @submit="handleSubmit" />

        <div class="tw-mt-6">
            <ImageUpload v-model="imageData" label="Hình ảnh" required />
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
useHead({
    title: 'Thêm danh mục'
})
definePageMeta({
    layout: 'admin',
    middleware: 'admin',
})

import { ref, onMounted, watch } from 'vue'
import Form from '~/components/admin/Form.vue'
import ImageUpload from '~/components/admin/ImageUpload.vue'

const { getCategories, createCategory } = useCategory()

const formData = ref({
    name: '',
    description: '',
    image: null,
    parent_id: null,
    is_active: 1
})

const imageData = ref(null)

const parentCategories = ref([])

const formFields = ref([
    {
        name: 'name',
        label: 'Tên danh mục',
        type: 'text',
        placeholder: 'Nhập tên danh mục',
        required: true
    },
    {
        name: 'description',
        label: 'Mô tả',
        type: 'textarea',
        placeholder: 'Nhập mô tả danh mục',
        rows: 4
    },
    {
        name: 'parent_id',
        label: 'Danh mục cha',
        type: 'select',
        placeholder: 'Chọn danh mục cha',
        options: []
    },
    {
        name: 'is_active',
        label: 'Trạng thái',
        type: 'toggle'
    }
])

// Watch for image changes
watch(imageData, (newValue) => {
    formData.value.image = newValue
}, { deep: true })

onMounted(async () => {
    try {
        const categories = await getCategories()
        if (categories) {
            formFields.value[2].options = categories.map(cat => ({
                label: cat.name,
                value: cat.id
            }))
        }
    } catch (error) {
        console.error('Error fetching categories:', error)
        alert('Không thể tải danh sách danh mục')
    }
})

const handleSubmit = async () => {
    try {
        if (!formData.value.name) {
            alert('Vui lòng nhập tên danh mục')
            return
        }

        if (!imageData.value) {
            alert('Vui lòng chọn hình ảnh')
            return
        }

        const formDataToSend = new FormData()
        formDataToSend.append('name', formData.value.name)
        formDataToSend.append('description', formData.value.description || '')
        formDataToSend.append('is_active', formData.value.is_active ? '1' : '0')

        if (formData.value.parent_id) {
            formDataToSend.append('parent_id', formData.value.parent_id)
        }

        console.log('Active status:', formData.value.is_active)

        if (imageData.value instanceof File) {
            formDataToSend.append('image', imageData.value)
        } else if (typeof imageData.value === 'string' && imageData.value.startsWith('data:')) {
            const response = await fetch(imageData.value)
            const blob = await response.blob()
            formDataToSend.append('image', blob, 'image.jpg')
        } else if (typeof imageData.value === 'string') {
            formDataToSend.append('image', imageData.value)
        }
        console.log(formDataToSend)

        const result = await createCategory(formDataToSend)

        if (result) {
            alert('Tạo danh mục thành công')
            await navigateTo('/admin/categories')
        }
    } catch (error) {
        console.error('Error creating category:', error)
        const errorMessage = error.response?.data?.message || 'Có lỗi xảy ra khi tạo danh mục'
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