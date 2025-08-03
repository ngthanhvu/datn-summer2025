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
                            <!-- Trong phần select tỉnh/thành -->
                            <select v-model="form.province" @change="onProvinceChange"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#81aacc]"
                                :class="{ 'border-red-500': errors.province }">
                                <option value="">Chọn tỉnh/thành</option>
                                <option v-for="province in provinces" :key="province.code" :value="province.name">
                                    {{ province.name }}
                                </option>
                            </select>
                            <p v-if="errors.province" class="text-red-500 text-sm mt-1">{{ errors.province }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Quận/Huyện</label>
                            <select v-model="form.district" @change="onDistrictChange"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#81aacc]"
                                :class="{ 'border-red-500': errors.district }">
                                <option value="">Chọn quận/huyện</option>
                                <option v-for="district in districts" :key="district.code" :value="district.name"
                                    :data-code="district.code">
                                    {{ district.name }}
                                </option>
                            </select>
                            <p v-if="errors.district" class="text-red-500 text-sm mt-1">{{ errors.district }}
                            </p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Phường/Xã</label>
                            <select v-model="form.ward"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-[#81aacc]"
                                :class="{ 'border-red-500': errors.ward }">
                                <option value="">Chọn phường/xã</option>
                                <option v-for="ward in wards" :key="ward.code" :value="ward.name">
                                    {{ ward.name }}
                                </option>
                            </select>
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
                        <button @click="handleSave"
                            class="flex-1 px-4 py-2 bg-[#81AACC] text-white rounded-md hover:bg-[#6387A6] cursor-pointer">
                            {{ editingIndex === null ? 'Thêm địa chỉ' : 'Cập nhật' }}
                        </button>
                        <button @click="$emit('close')"
                            class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 cursor-pointer">
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
    }
})

const emit = defineEmits(['close', 'save'])

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
const isLoading = ref(false)

const fetchProvinces = async () => {
    try {
        isLoading.value = true
        provinces.value = await addressService.getProvinces()
    } catch (error) {
        console.error('Error fetching provinces:', error)
    } finally {
        isLoading.value = false
    }
}

const onProvinceChange = async () => {
    form.value.district = ''
    form.value.ward = ''
    districts.value = []
    wards.value = []

    const selectedProvince = provinces.value.find(p => p.name === form.value.province)
    selectedProvinceCode.value = selectedProvince?.code

    if (selectedProvinceCode.value) {
        try {
            isLoading.value = true
            districts.value = await addressService.getDistricts(selectedProvinceCode.value)
        } catch (error) {
            console.error('Error fetching districts:', error)
        } finally {
            isLoading.value = false
        }
    }
}

const onDistrictChange = async () => {
    form.value.ward = ''
    wards.value = []

    const selectedDistrict = districts.value.find(d => d.name === form.value.district)
    selectedDistrictCode.value = selectedDistrict?.code

    if (selectedDistrictCode.value) {
        try {
            isLoading.value = true
            wards.value = await addressService.getWards(selectedDistrictCode.value)
        } catch (error) {
            console.error('Error fetching wards:', error)
        } finally {
            isLoading.value = false
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

const handleSave = () => {
    addressService.setFormData({
        full_name: form.value.fullName,
        phone: form.value.phone,
        province: form.value.province,
        district: form.value.district,
        ward: form.value.ward,
        street: form.value.detail
    })

    if (addressService.validateForm()) {
        emit('save', {
            ...form.value,
            fullAddress: `${form.value.detail}, ${form.value.hamlet}, ${form.value.ward}, ${form.value.district}, ${form.value.province}`
        })
    } else {
        errors.value = addressService.errors.value
    }
}

onMounted(() => {
    fetchProvinces()
})
</script>