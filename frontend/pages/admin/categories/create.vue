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
            <ImageUpload v-model="formData.image" label="Hình ảnh" required />
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
definePageMeta({
    layout: 'admin'
})

import { ref } from 'vue'
import Form from '~/components/admin/Form.vue'
import ImageUpload from '~/components/admin/ImageUpload.vue'

// Fetch parent categories
const { data: categories } = await useFetch('/api/categories')

const formFields = [
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
        options: categories.value?.map(cat => ({
            label: cat.name,
            value: cat.id
        })) || []
    },
    {
        name: 'status',
        label: 'Trạng thái',
        type: 'toggle'
    }
]

const formData = ref({
    name: '',
    description: '',
    image: null,
    parent_id: null,
    status: true
})

const handleSubmit = async () => {
    try {
        const formDataToSend = new FormData()
        formDataToSend.append('name', formData.value.name)
        formDataToSend.append('description', formData.value.description)
        if (formData.value.image) {
            formDataToSend.append('image', formData.value.image)
        }
        if (formData.value.parent_id) {
            formDataToSend.append('parent_id', formData.value.parent_id)
        }
        formDataToSend.append('status', formData.value.status)

        const { data } = await useFetch('/api/categories', {
            method: 'POST',
            body: formDataToSend
        })

        // Show success message
        useToast().success('Tạo danh mục thành công')

        // Navigate back to categories list
        await navigateTo('/admin/categories')
    } catch (error) {
        console.error('Error creating category:', error)
        useToast().error('Có lỗi xảy ra khi tạo danh mục')
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