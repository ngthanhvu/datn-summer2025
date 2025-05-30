<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
            <div>
                <h1 class="tw-text-2xl tw-font-semibold tw-text-gray-900">Chỉnh sửa sản phẩm</h1>
                <p class="tw-text-gray-600">Cập nhật thông tin sản phẩm</p>
            </div>
            <NuxtLink to="/admin/products"
                class="tw-bg-gray-100 tw-text-gray-600 tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-gray-200">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </NuxtLink>
        </div>

        <Form v-if="product" :fields="formFields" :initial-data="formData" v-model="formData" @submit="handleSubmit" />
        <div v-else class="tw-text-center tw-py-8">
            <p class="tw-text-gray-500">Đang tải...</p>
        </div>

        <div class="tw-flex tw-justify-end tw-gap-4 tw-mt-6">
            <NuxtLink to="/admin/products"
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
const product = ref(null)
const formData = ref({
    name: '',
    price: 0,
    category: '',
    brand: '',
    description: '',
    status: true,
    image: null
})

const formFields = [
    {
        name: 'name',
        label: 'Tên sản phẩm',
        type: 'text',
        placeholder: 'Nhập tên sản phẩm',
        required: true
    },
    {
        name: 'price',
        label: 'Giá',
        type: 'number',
        placeholder: 'Nhập giá sản phẩm',
        required: true,
        min: 0,
        step: 1000
    },
    {
        name: 'category',
        label: 'Danh mục',
        type: 'select',
        placeholder: 'Chọn danh mục',
        required: true,
        options: [
            { value: 'phone', label: 'Điện thoại' },
            { value: 'laptop', label: 'Laptop' },
            { value: 'tablet', label: 'Máy tính bảng' }
        ]
    },
    {
        name: 'brand',
        label: 'Thương hiệu',
        type: 'select',
        placeholder: 'Chọn thương hiệu',
        required: true,
        options: [
            { value: 'apple', label: 'Apple' },
            { value: 'samsung', label: 'Samsung' },
            { value: 'sony', label: 'Sony' },
            { value: 'lg', label: 'LG' }
        ]
    },
    {
        name: 'description',
        label: 'Mô tả',
        type: 'textarea',
        placeholder: 'Nhập mô tả sản phẩm',
        rows: 4
    },
    {
        name: 'image',
        label: 'Hình ảnh',
        type: 'image'
    },
    {
        name: 'status',
        label: 'Trạng thái',
        type: 'toggle'
    }
]

onMounted(async () => {
    try {
        // TODO: Call API to get product by ID
        // For now, use mock data
        const mockProduct = {
            id: route.params.id,
            name: 'iPhone 13 Pro Max',
            price: 30990000,
            category: 'phone',
            brand: 'apple',
            description: 'iPhone 13 Pro Max 128GB',
            status: true,
            image: 'https://via.placeholder.com/150'
        }

        product.value = mockProduct
        formData.value = { ...mockProduct }
    } catch (error) {
        console.error('Error fetching product:', error)
    }
})

const handleSubmit = async () => {
    try {
        // TODO: Call API to update product
        console.log('Update product:', formData.value)

        // Navigate back to products list
        await navigateTo('/admin/products')
    } catch (error) {
        console.error('Error updating product:', error)
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