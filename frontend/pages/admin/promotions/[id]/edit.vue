<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
            <div>
                <h1 class="tw-text-2xl tw-font-semibold tw-text-gray-900">Chỉnh sửa khuyến mãi</h1>
                <p class="tw-text-gray-600">Cập nhật thông tin chương trình khuyến mãi</p>
            </div>
            <NuxtLink to="/admin/promotions"
                class="tw-bg-gray-100 tw-text-gray-600 tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-gray-200">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </NuxtLink>
        </div>

        <Form v-if="promotion" :fields="formFields" :initial-data="formData" v-model="formData"
            @submit="handleSubmit" />
        <div v-else class="tw-text-center tw-py-8">
            <p class="tw-text-gray-500">Đang tải...</p>
        </div>

        <div class="tw-flex tw-justify-end tw-gap-4 tw-mt-6">
            <NuxtLink to="/admin/promotions"
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
const promotion = ref(null)

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

onMounted(async () => {
    try {
        // TODO: Call API to get promotion by ID
        // For now, use mock data
        const mockPromotion = {
            id: route.params.id,
            name: 'Giảm giá mùa hè',
            code: 'SUMMER2024',
            type: 'percentage',
            value: 20,
            minSpend: 1000000,
            maxDiscount: 500000,
            usageLimit: 100,
            usageCount: 45,
            startDate: '2024-06-01T00:00',
            endDate: '2024-08-31T23:59',
            description: 'Chương trình giảm giá mùa hè 2024',
            status: true
        }

        promotion.value = mockPromotion
        formData.value = { ...mockPromotion }
    } catch (error) {
        console.error('Error fetching promotion:', error)
    }
})

const handleSubmit = async () => {
    try {
        // TODO: Call API to update promotion
        console.log('Update promotion:', formData.value)

        // Navigate back to promotions list
        await navigateTo('/admin/promotions')
    } catch (error) {
        console.error('Error updating promotion:', error)
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