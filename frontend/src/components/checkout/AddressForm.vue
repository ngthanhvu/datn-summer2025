<template>
    <div v-if="show" class="fixed inset-0 bg-gray-600/70 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg w-full max-w-2xl mx-4">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold">
                        {{ editingIndex === null ? 'Thêm địa chỉ mới' : 'Chỉnh sửa địa chỉ' }}
                    </h3>
                    <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700 cursor-pointer">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Họ và tên</label>
                        <input v-model="form.fullName" type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#81aacc]"
                            :class="{ 'border-red-500': errors.full_name }" placeholder="Nhập họ và tên">
                        <p v-if="errors.full_name" class="text-red-500 text-sm mt-1">{{ errors.full_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Số điện thoại</label>
                        <input v-model="form.phone" type="tel"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#81aacc]"
                            :class="{ 'border-red-500': errors.phone }" placeholder="Nhập số điện thoại">
                        <p v-if="errors.phone" class="text-red-500 text-sm mt-1">{{ errors.phone }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Tỉnh/Thành</label>
                            <div class="relative">
                                <select v-model="form.province" @change="onProvinceChange" :disabled="isProvinceLoading"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#81aacc] disabled:opacity-50"
                                    :class="{ 'border-red-500': errors.province }">
                                    <option value="">Chọn tỉnh/thành</option>
                                    <option v-for="province in provinces" :key="province.ProvinceID"
                                        :value="province.ProvinceName">
                                        {{ province.ProvinceName }}
                                    </option>
                                </select>
                                <div v-if="isProvinceLoading && provinces.length === 0"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                    <div
                                        class="animate-spin rounded-full h-4 w-4 border-2 border-[#81aacc] border-t-transparent">
                                    </div>
                                </div>
                            </div>
                            <p v-if="errors.province" class="text-red-500 text-sm mt-1">{{ errors.province }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Quận/Huyện</label>
                            <div class="relative">
                                <select v-model="form.district" @change="onDistrictChange"
                                    :disabled="isProvinceLoading || !form.province"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#81aacc] disabled:opacity-50"
                                    :class="{ 'border-red-500': errors.district }">
                                    <option value="">{{ form.province ? 'Chọn quận/huyện' : 'Chọn tỉnh trước' }}
                                    </option>
                                    <option v-for="district in districts" :key="district.DistrictID"
                                        :value="district.DistrictName" :data-code="district.DistrictID">
                                        {{ district.DistrictName }}
                                    </option>
                                </select>
                                <div v-if="isProvinceLoading && form.province && districts.length === 0"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                    <div
                                        class="animate-spin rounded-full h-4 w-4 border-2 border-[#81aacc] border-t-transparent">
                                    </div>
                                </div>
                            </div>
                            <p v-if="errors.district" class="text-red-500 text-sm mt-1">{{ errors.district }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Phường/Xã</label>
                            <div class="relative">
                                <select v-model="form.ward" :disabled="isProvinceLoading || !form.district"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#81aacc] disabled:opacity-50"
                                    :class="{ 'border-red-500': errors.ward }">
                                    <option value="">{{ form.district ? 'Chọn phường/xã' : 'Chọn quận trước' }}</option>
                                    <option v-for="ward in wards" :key="ward.WardCode" :value="ward.WardName">
                                        {{ ward.WardName }}
                                    </option>
                                </select>
                                <div v-if="isProvinceLoading && form.district && wards.length === 0"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                    <div
                                        class="animate-spin rounded-full h-4 w-4 border-2 border-[#81aacc] border-t-transparent">
                                    </div>
                                </div>
                            </div>
                            <p v-if="errors.ward" class="text-red-500 text-sm mt-1">{{ errors.ward }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Thôn/Xóm</label>
                            <input v-model="form.hamlet" type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#81aacc]"
                                placeholder="Nhập thôn/xóm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Địa chỉ chi tiết</label>
                        <input v-model="form.detail" type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#81aacc]"
                            :class="{ 'border-red-500': errors.street }" placeholder="Số nhà, tên đường">
                        <p v-if="errors.street" class="text-red-500 text-sm mt-1">{{ errors.street }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Ghi chú</label>
                        <textarea v-model="form.note"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#81aacc]"
                            rows="3" placeholder="Ghi chú về địa chỉ giao hàng"></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button @click="handleSave" :disabled="props.isLoading"
                            class="flex-1 px-4 py-2 bg-[#81AACC] text-white rounded-md hover:bg-[#6387A6] cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                            <div v-if="props.isLoading" class="flex items-center justify-center gap-2">
                                <div
                                    class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent">
                                </div>
                                <span>{{ editingIndex === null ? 'Đang thêm...' : 'Đang cập nhật...' }}</span>
                            </div>
                            <span v-else>{{ editingIndex === null ? 'Thêm địa chỉ' : 'Cập nhật' }}</span>
                        </button>
                        <button @click="$emit('close')" :disabled="props.isLoading"
                            class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                            Hủy
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useAddress } from '../../composable/useAddress'
import { push } from 'notivue'

const addressService = useAddress()

const props = defineProps({
    show: {
        type: Boolean,
        required: true
    },
    editingIndex: {
        type: Number,
        default: null
    },
    address: {
        type: Object,
        default: () => ({})
    },
    isLoading: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['close', 'save', 'loading-change'])

const form = ref({
    fullName: '',
    phone: '',
    province: '',
    district: '',
    ward: '',
    hamlet: '',
    detail: '',
    note: ''
})

const errors = ref({})
const provinces = ref([])
const districts = ref([])
const wards = ref([])
const selectedProvinceCode = ref(null)
const selectedDistrictCode = ref(null)
const isProvinceLoading = ref(false) // Local loading for province/district/ward fetching

const fetchProvinces = async () => {
    try {
        isProvinceLoading.value = true
        provinces.value = await addressService.getProvinces()
    } catch (error) {
    } finally {
        isProvinceLoading.value = false
    }
}

const onProvinceChange = async () => {
    form.value.district = ''
    form.value.ward = ''
    districts.value = []
    wards.value = []

    const selectedProvince = provinces.value.find(p => p.ProvinceName === form.value.province)
    selectedProvinceCode.value = selectedProvince?.ProvinceID

    if (selectedProvinceCode.value) {
        try {
            isProvinceLoading.value = true
            districts.value = await addressService.getDistricts(selectedProvinceCode.value)
        } catch (error) {
        } finally {
            isProvinceLoading.value = false
        }
    }
}

const onDistrictChange = async () => {
    form.value.ward = ''
    wards.value = []

    const selectedDistrict = districts.value.find(d => d.DistrictName === form.value.district)
    selectedDistrictCode.value = selectedDistrict?.DistrictID

    if (selectedDistrictCode.value) {
        try {
            isProvinceLoading.value = true
            wards.value = await addressService.getWards(selectedDistrictCode.value)
        } catch (error) {
        } finally {
            isProvinceLoading.value = false
        }
    }
}

watch(() => props.address, (newAddress) => {
    if (newAddress) {
        form.value = {
            fullName: newAddress.fullName || '',
            phone: newAddress.phone || '',
            province: newAddress.province || '',
            district: newAddress.district || '',
            ward: newAddress.ward || '',
            hamlet: newAddress.hamlet || '',
            detail: newAddress.detail || '',
            note: newAddress.note || ''
        }

        if (newAddress.province) {
            onProvinceChange()
        }
        if (newAddress.district) {
            onDistrictChange()
        }
    }
}, { immediate: true })

const handleSave = async () => {
    if (props.isLoading) return // Prevent multiple clicks

    try {
        emit('loading-change', true)

        addressService.setFormData({
            full_name: form.value.fullName,
            phone: form.value.phone,
            province: form.value.province,
            district: form.value.district,
            ward: form.value.ward,
            street: form.value.detail
        })

        if (addressService.validateForm()) {
            const addressData = {
                ...form.value,
                fullAddress: `${form.value.detail}, ${form.value.hamlet}, ${form.value.ward}, ${form.value.district}, ${form.value.province}`
            }

            emit('save', addressData)
            push.success('Thêm địa chỉ giao hàng thành công!')
        } else {
            errors.value = addressService.errors.value
        }
    } catch (error) {
        console.error('Error saving address:', error)
    } finally {
        emit('loading-change', false)
    }
}

onMounted(() => {
    fetchProvinces()
})
</script>