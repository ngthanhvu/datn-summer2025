<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
            <div>
                <h1 class="tw-text-2xl tw-font-semibold tw-text-gray-900">Thêm khách hàng mới</h1>
                <p class="tw-text-gray-600">Điền thông tin để tạo khách hàng mới</p>
            </div>
            <NuxtLink to="/admin/customers"
                class="tw-bg-gray-100 tw-text-gray-600 tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-gray-200">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </NuxtLink>
        </div>

        <Form :fields="formFields" :initial-data="formData" v-model="formData" @submit="handleSubmit" />

        <div class="tw-flex tw-justify-end tw-gap-4 tw-mt-6">
            <NuxtLink to="/admin/customers"
                class="tw-px-4 tw-py-2 tw-border tw-rounded tw-text-gray-600 hover:tw-bg-gray-50">
                Hủy
            </NuxtLink>
            <button @click="handleSubmit"
                class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 hover:tw-bg-primary-dark">
                Tạo khách hàng
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
        name: 'avatar',
        label: 'Ảnh đại diện',
        type: 'image'
    },
    {
        name: 'name',
        label: 'Họ tên',
        type: 'text',
        required: true,
        placeholder: 'Nhập họ tên khách hàng'
    },
    {
        name: 'email',
        label: 'Email',
        type: 'email',
        required: true,
        placeholder: 'Nhập địa chỉ email'
    },
    {
        name: 'phone',
        label: 'Số điện thoại',
        type: 'text',
        required: true,
        placeholder: 'Nhập số điện thoại'
    },
    {
        name: 'address',
        label: 'Địa chỉ',
        type: 'textarea',
        rows: 3,
        placeholder: 'Nhập địa chỉ'
    },
    {
        name: 'birthday',
        label: 'Ngày sinh',
        type: 'date'
    },
    {
        name: 'gender',
        label: 'Giới tính',
        type: 'select',
        options: [
            { value: 'male', label: 'Nam' },
            { value: 'female', label: 'Nữ' },
            { value: 'other', label: 'Khác' }
        ]
    },
    {
        name: 'note',
        label: 'Ghi chú',
        type: 'textarea',
        rows: 3,
        placeholder: 'Nhập ghi chú'
    },
    {
        name: 'status',
        label: 'Trạng thái',
        type: 'toggle'
    }
]

const formData = ref({
    avatar: null,
    name: '',
    email: '',
    phone: '',
    address: '',
    birthday: '',
    gender: '',
    note: '',
    status: true
})

const handleSubmit = async () => {
    try {
        // TODO: Call API to create customer
        console.log('Create customer:', formData.value)

        // Navigate back to customers list
        await navigateTo('/admin/customers')
    } catch (error) {
        console.error('Error creating customer:', error)
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