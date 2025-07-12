<template>
    <div v-if="show"
        class="tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-items-center tw-justify-center tw-z-50">
        <div class="tw-bg-white tw-rounded-lg tw-w-full tw-max-w-2xl tw-mx-4">
            <div class="tw-p-6">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
                    <h3 class="tw-text-lg tw-font-semibold">
                        {{ editingIndex === null ? 'Thêm địa chỉ mới' : 'Chỉnh sửa địa chỉ' }}
                    </h3>
                    <button @click="$emit('close')" class="tw-text-gray-500 hover:tw-text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="tw-space-y-4">
                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Họ và tên</label>
                        <input v-model="form.fullName" type="text"
                            class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md focus:tw-outline-none focus:tw-border-[#81aacc]"
                            :class="{ 'tw-border-red-500': errors.full_name }" placeholder="Nhập họ và tên">
                        <p v-if="errors.full_name" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ errors.full_name }}</p>
                    </div>
                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Số điện thoại</label>
                        <input v-model="form.phone" type="tel"
                            class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md focus:tw-outline-none focus:tw-border-[#81aacc]"
                            :class="{ 'tw-border-red-500': errors.phone }" placeholder="Nhập số điện thoại">
                        <p v-if="errors.phone" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ errors.phone }}</p>
                    </div>
                    <div class="tw-grid tw-grid-cols-2 tw-gap-4">
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Tỉnh/Thành</label>
                            <!-- Trong phần select tỉnh/thành -->
                            <select v-model="form.province" @change="onProvinceChange"
                                class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md focus:tw-outline-none focus:tw-border-[#81aacc]"
                                :class="{ 'tw-border-red-500': errors.province }">
                                <option value="">Chọn tỉnh/thành</option>
                                <option v-for="province in provinces" :key="province.code" :value="province.name">
                                    {{ province.name }}
                                </option>
                            </select>
                            <p v-if="errors.province" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ errors.province }}
                            </p>
                        </div>
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Quận/Huyện</label>
                            <select v-model="form.district" @change="onDistrictChange"
                                class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md focus:tw-outline-none focus:tw-border-[#81aacc]"
                                :class="{ 'tw-border-red-500': errors.district }">
                                <option value="">Chọn quận/huyện</option>
                                <option v-for="district in districts" :key="district.code" :value="district.name"
                                    :data-code="district.code">
                                    {{ district.name }}
                                </option>
                            </select>
                            <p v-if="errors.district" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ errors.district }}
                            </p>
                        </div>
                    </div>
                    <div class="tw-grid tw-grid-cols-2 tw-gap-4">
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Phường/Xã</label>
                            <select v-model="form.ward"
                                class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md focus:tw-outline-none focus:tw-border-[#81aacc]"
                                :class="{ 'tw-border-red-500': errors.ward }">
                                <option value="">Chọn phường/xã</option>
                                <option v-for="ward in wards" :key="ward.code" :value="ward.name">
                                    {{ ward.name }}
                                </option>
                            </select>
                            <p v-if="errors.ward" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ errors.ward }}</p>
                        </div>
                        <div>
                            <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Thôn/Xóm</label>
                            <input v-model="form.hamlet" type="text"
                                class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md focus:tw-outline-none focus:tw-border-[#81aacc]"
                                placeholder="Nhập thôn/xóm">
                        </div>
                    </div>
                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Địa chỉ chi tiết</label>
                        <input v-model="form.detail" type="text"
                            class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md focus:tw-outline-none focus:tw-border-[#81aacc]"
                            :class="{ 'tw-border-red-500': errors.street }" placeholder="Số nhà, tên đường">
                        <p v-if="errors.street" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ errors.street }}</p>
                    </div>
                    <div>
                        <label class="tw-block tw-text-sm tw-font-medium tw-mb-1">Ghi chú</label>
                        <textarea v-model="form.note"
                            class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md focus:tw-outline-none focus:tw-border-[#81aacc]"
                            rows="3" placeholder="Ghi chú về địa chỉ giao hàng"></textarea>
                    </div>
                    <div class="tw-flex tw-gap-3">
                        <button @click="handleSave"
                            class="tw-flex-1 tw-px-4 tw-py-2 tw-bg-[#81AACC] tw-text-white tw-rounded-md hover:tw-bg-[#6387A6]">
                            {{ editingIndex === null ? 'Thêm địa chỉ' : 'Cập nhật' }}
                        </button>
                        <button @click="$emit('close')"
                            class="tw-px-4 tw-py-2 tw-border tw-rounded-md hover:tw-bg-gray-50">
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
import { useAddress } from '~/composables/useAddress'

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