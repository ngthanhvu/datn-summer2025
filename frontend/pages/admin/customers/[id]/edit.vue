<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
            <div>
                <h1 class="tw-text-2xl tw-font-semibold tw-text-gray-900">Chỉnh sửa khách hàng</h1>
                <p class="tw-text-gray-600">Cập nhật thông tin khách hàng</p>
            </div>
            <NuxtLink to="/admin/customers"
                class="tw-bg-gray-100 tw-text-gray-600 tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-gray-200">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </NuxtLink>
        </div>

        <Form v-if="customer" :fields="formFields" :initial-data="formData" v-model="formData" @submit="handleSubmit" />
        <div v-else class="tw-text-center tw-py-8">
            <p class="tw-text-gray-500">Đang tải...</p>
        </div>

        <div class="tw-flex tw-justify-end tw-gap-4 tw-mt-6">
            <NuxtLink to="/admin/customers"
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
const customer = ref(null)

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

onMounted(async () => {
    try {
        // TODO: Call API to get customer by ID
        // For now, use mock data
        const mockCustomer = {
            id: route.params.id,
            avatar: 'https://via.placeholder.com/150',
            name: 'Nguyễn Văn A',
            email: 'nguyenvana@email.com',
            phone: '0123456789',
            address: '123 Đường ABC, Quận 1, TP.HCM',
            birthday: '1990-01-01',
            gender: 'male',
            note: 'Khách hàng thân thiết',
            status: true
        }

        customer.value = mockCustomer
        formData.value = { ...mockCustomer }
    } catch (error) {
        console.error('Error fetching customer:', error)
    }
})

const handleSubmit = async () => {
    try {
        // TODO: Call API to update customer
        console.log('Update customer:', formData.value)

        // Navigate back to customers list
        await navigateTo('/admin/customers')
    } catch (error) {
        console.error('Error updating customer:', error)
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