<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
            <div>
                <h1 class="tw-text-2xl tw-font-semibold tw-text-gray-900">Thêm thương hiệu mới</h1>
                <p class="tw-text-gray-600">Điền thông tin để tạo thương hiệu mới</p>
            </div>
            <NuxtLink to="/admin/brands"
                class="tw-bg-gray-100 tw-text-gray-600 tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-gray-200">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </NuxtLink>
        </div>

        <Form :fields="formFields" :initial-data="formData" v-model="formData" @submit="handleSubmit" />

        <div class="tw-flex tw-justify-end tw-gap-4 tw-mt-6">
            <NuxtLink to="/admin/brands"
                class="tw-px-4 tw-py-2 tw-border tw-rounded tw-text-gray-600 hover:tw-bg-gray-50">
                Hủy
            </NuxtLink>
            <button @click="handleSubmit"
                class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 hover:tw-bg-primary-dark">
                Tạo thương hiệu
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

const formFields = [
    {
        name: 'name',
        label: 'Tên thương hiệu',
        type: 'text',
        placeholder: 'Nhập tên thương hiệu',
        required: true
    },
    {
        name: 'description',
        label: 'Mô tả',
        type: 'textarea',
        placeholder: 'Nhập mô tả thương hiệu',
        rows: 4
    },
    {
        name: 'logo',
        label: 'Logo',
        type: 'image'
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
    logo: '',
    status: true
})

const handleSubmit = async () => {
    try {
        // TODO: Call API to create brand
        console.log('Create brand:', formData.value)

        // Navigate back to brands list
        await navigateTo('/admin/brands')
    } catch (error) {
        console.error('Error creating brand:', error)
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