<template>
    <div class="settings-page">
        <div class="page-header">
            <h1>Cài đặt hệ thống</h1>
            <p class="text-gray-600">Quản lý cài đặt của cửa hàng</p>
        </div>

        <!-- Layout responsive -->
        <div class="settings-layout flex flex-col lg:flex-row">
            <!-- Sidebar - horizontal on mobile, vertical on desktop -->
            <div class="settings-sidebar w-full lg:w-1/4 lg:pr-4 lg:border-r lg:border-gray-200 mb-4 lg:mb-0">
                <div class="flex flex-row lg:flex-col gap-2 overflow-x-auto lg:overflow-x-visible">
                    <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key" :class="[
                        'px-4 py-2 rounded-md lg:rounded-l-md lg:rounded-r-none text-left cursor-pointer whitespace-nowrap lg:whitespace-normal',
                        activeTab === tab.key
                            ? 'bg-white border-l-0 lg:border-l-4 border-[#3BB77E] font-medium shadow-sm lg:shadow-none'
                            : 'bg-gray-100 hover:bg-gray-200'
                    ]">
                        {{ tab.label }}
                    </button>
                </div>
            </div>
            <!-- Nội dung - full width on mobile -->
            <div class="settings-content w-full lg:w-3/4 lg:pl-4">
                <div class="rounded-md border border-gray-300 bg-white">
                    <SettingCard v-if="activeTab === 'general'" title="Thông tin cửa hàng" :fields="generalFields"
                        v-model="generalSettings" />
                    <SettingCard v-if="activeTab === 'payment'" title="Cài đặt thanh toán" :fields="paymentFields"
                        v-model="paymentSettings" />
                    <SettingCard v-if="activeTab === 'shipping'" title="Cài đặt vận chuyển" :fields="shippingFields"
                        v-model="shippingSettings" />
                    <SettingCard v-if="activeTab === 'email'" title="Cài đặt email" :fields="emailFields"
                        v-model="emailSettings" />
                    <SettingCard v-if="activeTab === 'notification'" title="Cài đặt thông báo"
                        :fields="notificationFields" v-model="notificationSettings" />
                    <SettingCard v-if="activeTab === 'api'" title="Cài đặt API" :fields="apiFields"
                        v-model="apiSettings" />
                    <SettingCard v-if="activeTab === 'banner'" title="Cài đặt banner" :fields="bannerFields"
                        v-model="bannerSettings" />
                </div>
                <div class="mt-6 text-center lg:text-right">
                    <button @click="handleSaveAll"
                        class="w-full sm:w-auto bg-[#3BB77E] hover:bg-green-700 text-white font-medium px-6 py-2 rounded cursor-pointer">
                        Lưu thay đổi
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>



<script setup>
import { ref, onMounted, computed } from 'vue'
import SettingCard from './SettingsCard.vue'
import useSettings from '../../../composable/useSettingsApi'
import { push } from 'notivue'

const { settings, fetchSettings, updateSettings } = useSettings()

const generalSettings = ref({})
const paymentSettings = ref({})
const shippingSettings = ref({})
const emailSettings = ref({})
const notificationSettings = ref({})
const apiSettings = ref({})
const activeTab = ref('general')
const bannerSettings = ref({})

const tabs = [
    { key: 'general', label: 'Tổng quan' },
    { key: 'payment', label: 'Thanh toán' },
    { key: 'shipping', label: 'Giao hàng' },
    { key: 'email', label: 'Email' },
    { key: 'notification', label: 'Thông báo' },
    { key: 'banner', label: 'Banner' },
    { key: 'api', label: 'API' }
]
onMounted(async () => {
    await fetchSettings()
    generalSettings.value = extractSettings(['storeName', 'address', 'phone', 'email', 'logo', 'siteIcon'])
    paymentSettings.value = extractSettings([
        'enableCod',
        'enableMomo', 'momoPartnerCode', 'momoAccessKey', 'momoSecretKey', 'momoUrl',
        'enableVnpay', 'vnpayTmnCode', 'vnpayHashSecret', 'vnpayUrl',
    ])

    shippingSettings.value = extractSettings(['GHN_BASE_URL', 'GHN_API_TOKEN', 'GHN_SHOP_ID'])
    emailSettings.value = extractSettings(['smtpHost', 'smtpPort', 'smtpUser', 'smtpPass', 'emailFrom'])
    notificationSettings.value = extractSettings(['enableEmailNotification', 'enableSmsNotification', 'smsApiKey', 'notifyOnNewOrder', 'notifyOnOrderStatus'])
    apiSettings.value = extractSettings(['enableApi', 'apiKey', 'allowedOrigins'])
    bannerSettings.value = extractSettings(['banners'])
})

const mergedSettings = computed(() => ({
    ...generalSettings.value,
    ...paymentSettings.value,
    ...shippingSettings.value,
    ...emailSettings.value,
    ...notificationSettings.value,
    ...apiSettings.value,
    ...bannerSettings.value,
}))

const handleSaveAll = async () => {
    try {
        const normalized = {}
        for (const [key, val] of Object.entries(mergedSettings.value)) {
            if (typeof val === 'boolean') {
                normalized[key] = val ? 1 : 0
            } else {
                normalized[key] = val
            }
        }
        await updateSettings(normalized)
        push.success(' Đã lưu cài đặt thành công.')
    } catch (err) {
        console.log(err)
    }
}


const extractSettings = (keys) => {
    const result = {}
    keys.forEach(key => {
        const val = settings.value[key]
        if (val === '1' || val === 1) result[key] = true
        else if (val === '0' || val === 0) result[key] = false
        else result[key] = val ?? ''
    })
    return result
}


const generalFields = [
    { name: 'storeName', label: 'Tên cửa hàng', type: 'text', required: true },
    { name: 'address', label: 'Địa chỉ', type: 'textarea', rows: 2 },
    { name: 'phone', label: 'Số điện thoại', type: 'text' },
    { name: 'email', label: 'Email', type: 'text' },
    { name: 'logo', label: 'Logo', type: 'image' },
    { name: 'siteIcon', label: 'Biểu tượng trang web (favicon)', type: 'image' }

]

const paymentFields = [
    { name: 'enableCod', label: 'Cho phép thanh toán khi nhận hàng', type: 'toggle' },

    { name: 'enableMomo', label: 'Cho phép thanh toán Momo', type: 'toggle' },
    { name: 'momoPartnerCode', label: 'Momo Partner Code', type: 'text' },
    { name: 'momoAccessKey', label: 'Momo Access Key', type: 'text' },
    { name: 'momoSecretKey', label: 'Momo Secret Key', type: 'text' },
    { name: 'momoUrl', label: 'Momo URL', type: 'text' },

    { name: 'enableVnpay', label: 'Cho phép thanh toán VNPAY', type: 'toggle' },
    { name: 'vnpayTmnCode', label: 'VNPAY TMN Code', type: 'text' },
    { name: 'vnpayHashSecret', label: 'VNPAY Hash Secret', type: 'text' },
    { name: 'vnpayUrl', label: 'VNPAY URL', type: 'text' },
]



const shippingFields = [
    { name: 'GHN_BASE_URL', label: 'GHN Base URL', type: 'text' },
    { name: 'GHN_API_TOKEN', label: 'GHN API Token', type: 'text' },
    { name: 'GHN_SHOP_ID', label: 'GHN Shop ID', type: 'text' }
]



const emailFields = [
    { name: 'smtpHost', label: 'SMTP Host (MAIL_HOST)', type: 'text' },
    { name: 'smtpPort', label: 'SMTP Port (MAIL_PORT)', type: 'number' },
    { name: 'smtpUser', label: 'SMTP User (MAIL_USERNAME)', type: 'text' },
    { name: 'smtpPass', label: 'SMTP Password (MAIL_PASSWORD)', type: 'password' },
    { name: 'emailFrom', label: 'Email From', type: 'text' }
]

const notificationFields = [
    { name: 'enableEmailNotification', label: 'Gửi thông báo qua email', type: 'toggle' },
    { name: 'enableSmsNotification', label: 'Gửi thông báo qua SMS', type: 'toggle' },
    { name: 'smsApiKey', label: 'SMS API Key', type: 'text' },
    { name: 'notifyOnNewOrder', label: 'Thông báo khi có đơn hàng mới', type: 'toggle' },
    { name: 'notifyOnOrderStatus', label: 'Thông báo khi cập nhật trạng thái đơn hàng', type: 'toggle' }
]

const apiFields = [
    { name: 'enableApi', label: 'Kích hoạt API', type: 'toggle' },
    { name: 'apiKey', label: 'API Key', type: 'text', readonly: true },
    { name: 'allowedOrigins', label: 'Allowed Origins', type: 'textarea', placeholder: 'Mỗi domain một dòng', rows: 3 }
]

const bannerFields = [
    {
        name: 'banners',
        label: 'Ảnh banner',
        type: 'images',
        multiple: true
    }
]
</script>


<style scoped>
.settings-page {
    padding: 1rem;
}

@media (min-width: 640px) {
    .settings-page {
        padding: 1.5rem;
    }
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.5rem;
}

@media (min-width: 640px) {
    .page-header h1 {
        font-size: 1.875rem;
    }
}

/* Custom scrollbar for mobile sidebar */
.settings-sidebar::-webkit-scrollbar {
    height: 4px;
}

.settings-sidebar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

.settings-sidebar::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 2px;
}

.settings-sidebar::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
