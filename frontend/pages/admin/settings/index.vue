<template>
    <div class="settings-page">
        <div class="page-header">
            <h1>Cài đặt hệ thống</h1>
            <p class="text-gray-600">Quản lý cài đặt của cửa hàng</p>
        </div>

        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-6">
            <!-- General Settings -->
            <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
                <h2 class="tw-text-lg tw-font-semibold tw-mb-4">Thông tin cửa hàng</h2>
                <Form :fields="generalFields" :initial-data="generalSettings" v-model="generalSettings"
                    @submit="handleGeneralSubmit" />
            </div>

            <!-- Payment Settings -->
            <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
                <h2 class="tw-text-lg tw-font-semibold tw-mb-4">Cài đặt thanh toán</h2>
                <Form :fields="paymentFields" :initial-data="paymentSettings" v-model="paymentSettings"
                    @submit="handlePaymentSubmit" />
            </div>

            <!-- Shipping Settings -->
            <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
                <h2 class="tw-text-lg tw-font-semibold tw-mb-4">Cài đặt vận chuyển</h2>
                <Form :fields="shippingFields" :initial-data="shippingSettings" v-model="shippingSettings"
                    @submit="handleShippingSubmit" />
            </div>

            <!-- Email Settings -->
            <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
                <h2 class="tw-text-lg tw-font-semibold tw-mb-4">Cài đặt email</h2>
                <Form :fields="emailFields" :initial-data="emailSettings" v-model="emailSettings"
                    @submit="handleEmailSubmit" />
            </div>

            <!-- Notification Settings -->
            <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
                <h2 class="tw-text-lg tw-font-semibold tw-mb-4">Cài đặt thông báo</h2>
                <Form :fields="notificationFields" :initial-data="notificationSettings" v-model="notificationSettings"
                    @submit="handleNotificationSubmit" />
            </div>

            <!-- API Settings -->
            <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
                <h2 class="tw-text-lg tw-font-semibold tw-mb-4">Cài đặt API</h2>
                <Form :fields="apiFields" :initial-data="apiSettings" v-model="apiSettings" @submit="handleApiSubmit" />
            </div>
        </div>
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin'
})

import { ref } from 'vue'
import Form from '~/components/admin/Form.vue'

// General Settings
const generalFields = [
    {
        name: 'storeName',
        label: 'Tên cửa hàng',
        type: 'text',
        required: true
    },
    {
        name: 'address',
        label: 'Địa chỉ',
        type: 'textarea',
        rows: 2
    },
    {
        name: 'phone',
        label: 'Số điện thoại',
        type: 'text'
    },
    {
        name: 'email',
        label: 'Email',
        type: 'text'
    },
    {
        name: 'logo',
        label: 'Logo',
        type: 'image'
    }
]

const generalSettings = ref({
    storeName: 'Cửa hàng của tôi',
    address: '123 Đường ABC, Quận 1, TP.HCM',
    phone: '0123456789',
    email: 'contact@mystore.com',
    logo: 'https://via.placeholder.com/150'
})

// Payment Settings
const paymentFields = [
    {
        name: 'enableCod',
        label: 'Cho phép thanh toán khi nhận hàng',
        type: 'toggle'
    },
    {
        name: 'enableBankTransfer',
        label: 'Cho phép chuyển khoản',
        type: 'toggle'
    },
    {
        name: 'bankAccount',
        label: 'Thông tin tài khoản',
        type: 'textarea',
        rows: 3
    },
    {
        name: 'enableMomo',
        label: 'Cho phép thanh toán Momo',
        type: 'toggle'
    },
    {
        name: 'momoApiKey',
        label: 'Momo API Key',
        type: 'text'
    }
]

const paymentSettings = ref({
    enableCod: true,
    enableBankTransfer: true,
    bankAccount: 'Ngân hàng: VCB\nSố TK: 1234567890\nChủ TK: Nguyen Van A',
    enableMomo: false,
    momoApiKey: ''
})

// Shipping Settings
const shippingFields = [
    {
        name: 'freeShippingMinimum',
        label: 'Đơn hàng tối thiểu để miễn phí ship',
        type: 'number',
        min: 0,
        step: 1000
    },
    {
        name: 'defaultShippingFee',
        label: 'Phí ship mặc định',
        type: 'number',
        min: 0,
        step: 1000
    },
    {
        name: 'enableGhtk',
        label: 'Kích hoạt GHTK',
        type: 'toggle'
    },
    {
        name: 'ghtkApiKey',
        label: 'GHTK API Key',
        type: 'text'
    }
]

const shippingSettings = ref({
    freeShippingMinimum: 500000,
    defaultShippingFee: 30000,
    enableGhtk: false,
    ghtkApiKey: ''
})

// Email Settings
const emailFields = [
    {
        name: 'smtpHost',
        label: 'SMTP Host',
        type: 'text'
    },
    {
        name: 'smtpPort',
        label: 'SMTP Port',
        type: 'number'
    },
    {
        name: 'smtpUser',
        label: 'SMTP User',
        type: 'text'
    },
    {
        name: 'smtpPass',
        label: 'SMTP Password',
        type: 'password'
    },
    {
        name: 'emailFrom',
        label: 'Email From',
        type: 'text'
    }
]

const emailSettings = ref({
    smtpHost: 'smtp.gmail.com',
    smtpPort: 587,
    smtpUser: 'your-email@gmail.com',
    smtpPass: '',
    emailFrom: 'Cửa hàng của tôi <your-email@gmail.com>'
})

// Notification Settings
const notificationFields = [
    {
        name: 'enableEmailNotification',
        label: 'Gửi thông báo qua email',
        type: 'toggle'
    },
    {
        name: 'enableSmsNotification',
        label: 'Gửi thông báo qua SMS',
        type: 'toggle'
    },
    {
        name: 'smsApiKey',
        label: 'SMS API Key',
        type: 'text'
    },
    {
        name: 'notifyOnNewOrder',
        label: 'Thông báo khi có đơn hàng mới',
        type: 'toggle'
    },
    {
        name: 'notifyOnOrderStatus',
        label: 'Thông báo khi cập nhật trạng thái đơn hàng',
        type: 'toggle'
    }
]

const notificationSettings = ref({
    enableEmailNotification: true,
    enableSmsNotification: false,
    smsApiKey: '',
    notifyOnNewOrder: true,
    notifyOnOrderStatus: true
})

// API Settings
const apiFields = [
    {
        name: 'enableApi',
        label: 'Kích hoạt API',
        type: 'toggle'
    },
    {
        name: 'apiKey',
        label: 'API Key',
        type: 'text',
        readonly: true
    },
    {
        name: 'allowedOrigins',
        label: 'Allowed Origins',
        type: 'textarea',
        placeholder: 'Mỗi domain một dòng',
        rows: 3
    }
]

const apiSettings = ref({
    enableApi: false,
    apiKey: 'sk_test_1234567890',
    allowedOrigins: 'http://localhost:3000\nhttps://mystore.com'
})

// Submit handlers
const handleGeneralSubmit = async () => {
    try {
        // TODO: Call API to update general settings
        console.log('Update general settings:', generalSettings.value)
    } catch (error) {
        console.error('Error updating general settings:', error)
    }
}

const handlePaymentSubmit = async () => {
    try {
        // TODO: Call API to update payment settings
        console.log('Update payment settings:', paymentSettings.value)
    } catch (error) {
        console.error('Error updating payment settings:', error)
    }
}

const handleShippingSubmit = async () => {
    try {
        // TODO: Call API to update shipping settings
        console.log('Update shipping settings:', shippingSettings.value)
    } catch (error) {
        console.error('Error updating shipping settings:', error)
    }
}

const handleEmailSubmit = async () => {
    try {
        // TODO: Call API to update email settings
        console.log('Update email settings:', emailSettings.value)
    } catch (error) {
        console.error('Error updating email settings:', error)
    }
}

const handleNotificationSubmit = async () => {
    try {
        // TODO: Call API to update notification settings
        console.log('Update notification settings:', notificationSettings.value)
    } catch (error) {
        console.error('Error updating notification settings:', error)
    }
}

const handleApiSubmit = async () => {
    try {
        // TODO: Call API to update API settings
        console.log('Update API settings:', apiSettings.value)
    } catch (error) {
        console.error('Error updating API settings:', error)
    }
}
</script>

<style scoped>
.settings-page {
    padding: 1.5rem;
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    font-size: 1.875rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.5rem;
}
</style>