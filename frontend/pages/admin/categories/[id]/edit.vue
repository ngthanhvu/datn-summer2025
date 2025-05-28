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

        <Form v-if="category" :fields="formFields" :initial-data="formData" v-model="formData" @submit="handleSubmit" />
        <div v-else class="tw-text-center tw-py-8">
            <p class="tw-text-gray-500">Đang tải...</p>
        </div>

        <div class="tw-flex tw-justify-end tw-gap-4 tw-mt-6">
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
</template>

<script setup>
definePageMeta({
    layout: 'admin'
})

import { ref, onMounted } from 'vue'
import Form from '~/components/admin/Form.vue'

const route = useRoute()
const category = ref(null)
const formData = ref({
    name: '',
    description: '',
    icon: '',
    status: true
})

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
        name: 'icon',
        label: 'Icon',
        type: 'text',
        placeholder: 'Nhập class icon (ví dụ: fas fa-mobile)'
    },
    {
        name: 'status',
        label: 'Trạng thái',
        type: 'toggle'
    }
]

onMounted(async () => {
    try {
        // TODO: Call API to get category by ID
        // For now, use mock data
        const mockCategory = {
            id: route.params.id,
            name: 'Điện thoại',
            description: 'Các loại điện thoại di động',
            icon: 'fas fa-mobile',
            status: true,
            productCount: 15
        }

        category.value = mockCategory
        formData.value = { ...mockCategory }
    } catch (error) {
        console.error('Error fetching category:', error)
    }
})

const handleSubmit = async () => {
    try {
        // TODO: Call API to update category
        console.log('Update category:', formData.value)

        // Navigate back to categories list
        await navigateTo('/admin/categories')
    } catch (error) {
        console.error('Error updating category:', error)
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