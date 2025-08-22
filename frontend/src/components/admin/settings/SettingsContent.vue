<template>
    <div class="settings-page">
        <div class="page-header">
            <h1>Cài đặt hệ thống</h1>
            <p class="text-gray-600">Quản lý cài đặt của cửa hàng</p>
        </div>

        <!-- Layout responsive -->
        <div class="settings-layout flex flex-col">
            <!-- Sidebar - horizontal tabs like in the image -->
            <div class="settings-sidebar w-full mb-6">
                <div class="flex flex-row gap-1 overflow-x-auto border-b border-gray-200">
                    <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key" :class="[
                        'px-6 py-3 text-sm font-medium cursor-pointer whitespace-nowrap transition-colors duration-200',
                        activeTab === tab.key
                            ? 'bg-gray-100 text-gray-900 border-b-2 border-[#3BB77E]'
                            : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'
                    ]">
                        {{ tab.label }}
                    </button>
                </div>
            </div>
            <!-- Nội dung - full width -->
            <div class="settings-content w-full">
                <div class="rounded-md border border-gray-300 bg-white">
                    <!-- Tab Tổng quan với layout 2 cột -->
                    <div v-if="activeTab === 'general'" class="p-6">
                        <h2 class="text-lg font-semibold mb-6">Thông tin cửa hàng</h2>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Cột trái - thông tin cơ bản -->
                            <div class="space-y-4">
                                <div class="form-group">
                                    <label for="storeName">Tên cửa hàng <span class="text-red-500">*</span></label>
                                    <input id="storeName" v-model="generalSettings.storeName"
                                        placeholder="Nhập tên cửa hàng" type="text" required />
                                </div>
                                <div class="form-group">
                                    <label for="address">Địa chỉ <span class="text-red-500">*</span></label>
                                    <textarea id="address" placeholder="Nhập địa chỉ cửa hàng"
                                        v-model="generalSettings.address" rows="2"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Số điện thoại <span class="text-red-500">*</span></label>
                                    <input id="phone" placeholder="Nhập sđt cửa hàng"
                                        v-model="generalSettings.phone" type="text" />
                                </div>
                                <div class="form-group">
                                    <label for="email">Email <span class="text-red-500">*</span></label>
                                    <input id="email" placeholder="Nhập email cửa hàng"
                                        v-model="generalSettings.email" type="text" />
                                </div>
                            </div>

                            <!-- Cột phải - logo và biểu tượng -->
                            <div class="space-y-4">
                                <div class="form-group">
                                    <label for="logo">Logo <span class="text-red-500">*</span></label>
                                    <div class="image-upload-container">
                                        <div v-if="generalSettings.logo" class="relative inline-block mb-2">
                                            <img :src="generalSettings.logo" alt="Logo"
                                                class="max-h-24 sm:max-h-32 rounded-md border border-gray-300" />
                                            <button type="button" @click="generalSettings.logo = null"
                                                class="absolute top-1 right-1 bg-white text-red-600 text-xs px-2 py-1 rounded hover:bg-red-100">
                                                Xoá ảnh
                                            </button>
                                        </div>
                                        <label v-else for="logo"
                                            class="block border-2 border-dashed border-gray-300 rounded-md py-6 sm:py-10 text-center cursor-pointer hover:bg-gray-50">
                                            <div class="text-gray-400 mb-2">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="w-6 h-6 sm:w-8 sm:h-8 mx-auto" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12v9m0 0l-3-3m3 3l3-3m6-3V5a2 2 0 00-2-2H6a2 2 0 00-2 2v11" />
                                                </svg>
                                            </div>
                                            <p class="text-gray-600 text-sm sm:text-base">Click để tải logo lên</p>
                                            <p class="text-gray-400 text-xs">PNG, JPG, GIF (tối đa 2MB)</p>
                                        </label>
                                        <input type="file" class="hidden" id="logo" accept="image/*"
                                            @change="(e) => handleImageUpload(e, 'logo')" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="siteIcon">Biểu tượng trang web (favicon) <span
                                            class="text-red-500">*</span></label>
                                    <div class="image-upload-container">
                                        <div v-if="generalSettings.siteIcon" class="relative inline-block mb-2">
                                            <img :src="generalSettings.siteIcon" alt="Favicon"
                                                class="max-h-16 sm:max-h-20 rounded-md border border-gray-300" />
                                            <button type="button" @click="generalSettings.siteIcon = null"
                                                class="absolute top-1 right-1 bg-white text-red-600 text-xs px-2 py-1 rounded hover:bg-red-100">
                                                Xoá ảnh
                                            </button>
                                        </div>
                                        <label v-else for="siteIcon"
                                            class="block border-2 border-dashed border-gray-300 rounded-md py-4 sm:py-6 text-center cursor-pointer hover:bg-gray-50">
                                            <div class="text-gray-400 mb-2">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="w-5 h-5 sm:w-6 sm:h-6 mx-auto" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12v9m0 0l-3-3m3 3l3-3m6-3V5a2 2 0 00-2-2H6a2 2 0 00-2 2v11" />
                                                </svg>
                                            </div>
                                            <p class="text-gray-600 text-sm">Click để tải favicon lên</p>
                                            <p class="text-gray-400 text-xs">ICO, PNG (16x16, 32x32)</p>
                                        </label>
                                        <input type="file" class="hidden" id="siteIcon" accept="image/*"
                                            @change="(e) => handleImageUpload(e, 'siteIcon')" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <SettingCard v-if="activeTab === 'payment'" title="Cài đặt thanh toán" :fields="paymentFields"
                        v-model="paymentSettings" />
                    <SettingCard v-if="activeTab === 'shipping'" title="Cài đặt vận chuyển" :fields="shippingFields"
                        v-model="shippingSettings" />
                    <SettingCard v-if="activeTab === 'email'" title="Cài đặt email" :fields="emailFields"
                        v-model="emailSettings" />
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
const activeTab = ref('general')
const bannerSettings = ref({})

const tabs = [
    { key: 'general', label: 'Tổng quan' },
    { key: 'payment', label: 'Thanh toán' },
    { key: 'shipping', label: 'Giao hàng' },
    { key: 'email', label: 'Email' },
    { key: 'banner', label: 'Banner' },
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
    bannerSettings.value = extractSettings(['banners'])
})

const mergedSettings = computed(() => ({
    ...generalSettings.value,
    ...paymentSettings.value,
    ...shippingSettings.value,
    ...emailSettings.value,
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

const handleImageUpload = (event, fieldName) => {
    const file = event.target.files[0]
    if (file) {
        const reader = new FileReader()
        reader.onload = (e) => {
            if (fieldName === 'logo') {
                generalSettings.value.logo = e.target.result
            } else if (fieldName === 'siteIcon') {
                generalSettings.value.siteIcon = e.target.result
            }
        }
        reader.readAsDataURL(file)
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

const paymentFields = [
    { name: 'enableCod', label: 'Cho phép thanh toán khi nhận hàng', type: 'toggle' },

    { name: 'enableMomo', label: 'Cho phép thanh toán Momo', type: 'toggle' },
    { name: 'momoPartnerCode', label: 'Momo Partner Code', type: 'text', placeholder: 'MOMO_PARTNER_CODE' },
    { name: 'momoAccessKey', label: 'Momo Access Key', type: 'text', placeholder: 'MOMO_ACCESS_KEY' },
    { name: 'momoSecretKey', label: 'Momo Secret Key', type: 'text', placeholder: 'MOMO_SECRET_KEY' },
    { name: 'momoUrl', label: 'Momo URL', type: 'text', placeholder: 'MOMO_URL' },

    { name: 'enableVnpay', label: 'Cho phép thanh toán VNPAY', type: 'toggle' },
    { name: 'vnpayTmnCode', label: 'VNPAY TMN Code', type: 'text', placeholder: 'VNPAY_TMN_CODE' },
    { name: 'vnpayHashSecret', label: 'VNPAY Hash Secret', type: 'text', placeholder: 'VNPAY_HASH_SECRET' },
    { name: 'vnpayUrl', label: 'VNPAY URL', type: 'text', placeholder: 'VNPAY_URL' },
]

const shippingFields = [
    { name: 'GHN_BASE_URL', label: 'GHN Base URL', type: 'text', placeholder: 'GHN_BASE_URL' },
    { name: 'GHN_API_TOKEN', label: 'GHN API Token', type: 'text', placeholder: 'GHN_API_TOKEN' },
    { name: 'GHN_SHOP_ID', label: 'GHN Shop ID', type: 'text', placeholder: 'GHN_SHOP_ID' },
]

const emailFields = [
    { name: 'smtpHost', label: 'SMTP Host (MAIL_HOST)', type: 'text', placeholder: 'smtp.gmail.com' },
    { name: 'smtpPort', label: 'SMTP Port (MAIL_PORT)', type: 'number', placeholder: '587' },
    { name: 'smtpUser', label: 'SMTP User (MAIL_USERNAME)', type: 'text', placeholder: 'example@example.com' },
    { name: 'smtpPass', label: 'SMTP Password (MAIL_PASSWORD)', type: 'password', placeholder: 'password' },
    { name: 'emailFrom', label: 'Email From', type: 'text', placeholder: 'example@example.com' },
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

/* Form styling for general tab */
.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    font-weight: 500;
    color: #374151;
    font-size: 0.875rem;
}

.form-group input[type="text"],
.form-group textarea {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 0.875rem;
    transition: border-color 0.2s;
}

.form-group input[type="text"]:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3BB77E;
    box-shadow: 0 0 0 3px rgba(59, 183, 126, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 60px;
}

.image-upload-container {
    width: 100%;
}

.image-upload-container img {
    display: block;
    max-width: 100%;
    height: auto;
}
</style>
