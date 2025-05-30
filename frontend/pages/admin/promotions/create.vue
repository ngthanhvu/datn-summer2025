<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
            <div>
                <h1 class="tw-text-2xl tw-font-semibold tw-text-gray-900">Thêm khuyến mãi mới</h1>
                <p class="tw-text-gray-600">Điền thông tin để tạo chương trình khuyến mãi mới</p>
            </div>
            <NuxtLink to="/admin/promotions"
                class="tw-bg-gray-100 tw-text-gray-600 tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-gray-200">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </NuxtLink>
        </div>

        <Form :fields="formFields" :initial-data="formData" v-model="formData" @submit="handleSubmit" />

        <div class="tw-flex tw-justify-end tw-gap-4 tw-mt-6">
            <NuxtLink to="/admin/promotions"
                class="tw-px-4 tw-py-2 tw-border tw-rounded tw-text-gray-600 hover:tw-bg-gray-50">
                Hủy
            </NuxtLink>
            <button @click="handleSubmit"
                class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 hover:tw-bg-primary-dark">
                Tạo khuyến mãi
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
        label: 'Tên chương trình',
        type: 'text',
        required: true,
        placeholder: 'Nhập tên chương trình khuyến mãi'
    },
    {
        name: 'code',
        label: 'Mã giảm giá',
        type: 'text',
        required: true,
        placeholder: 'Nhập mã giảm giá'
    },
    {
        name: 'type',
        label: 'Loại giảm giá',
        type: 'select',
        required: true,
        options: [
            { value: 'percentage', label: 'Giảm theo phần trăm' },
            { value: 'fixed', label: 'Giảm số tiền cố định' }
        ]
    },
    {
        name: 'value',
        label: 'Giá trị giảm',
        type: 'number',
        required: true,
        min: 0,
        step: formData => formData.type === 'percentage' ? 1 : 1000,
        max: formData => formData.type === 'percentage' ? 100 : undefined,
        suffix: formData => formData.type === 'percentage' ? '%' : 'đ'
    },
    {
        name: 'minSpend',
        label: 'Đơn hàng tối thiểu',
        type: 'number',
        required: true,
        min: 0,
        step: 1000
    },
    {
        name: 'maxDiscount',
        label: 'Giảm tối đa',
        type: 'number',
        required: true,
        min: 0,
        step: 1000
    },
    {
        name: 'usageLimit',
        label: 'Giới hạn sử dụng',
        type: 'number',
        min: 0,
        step: 1,
        placeholder: '0 = không giới hạn'
    },
    {
        name: 'startDate',
        label: 'Ngày bắt đầu',
        type: 'datetime-local',
        required: true
    },
    {
        name: 'endDate',
        label: 'Ngày kết thúc',
        type: 'datetime-local',
        required: true
    },
    {
        name: 'description',
        label: 'Mô tả',
        type: 'textarea',
        rows: 3,
        placeholder: 'Nhập mô tả chương trình khuyến mãi'
    },
    {
        name: 'status',
        label: 'Trạng thái',
        type: 'toggle'
    }
]

const formData = ref({
    name: '',
    code: '',
    type: 'percentage',
    value: 0,
    minSpend: 0,
    maxDiscount: 0,
    usageLimit: 0,
    startDate: '',
    endDate: '',
    description: '',
    status: true
})

const handleSubmit = async () => {
    try {
        // TODO: Call API to create promotion
        console.log('Create promotion:', formData.value)

        // Navigate back to promotions list
        await navigateTo('/admin/promotions')
    } catch (error) {
        console.error('Error creating promotion:', error)
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