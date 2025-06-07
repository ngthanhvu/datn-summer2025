<template>
    <div class="tw-bg-white tw-p-6 tw-rounded tw-shadow">
        <h2 class="tw-font-bold tw-text-lg tw-mb-6">Địa chỉ</h2>
        <form @submit.prevent="handleSubmit" class="tw-mb-8">
            <div class="tw-grid tw-grid-cols-2 tw-gap-4">
                <div class="tw-mb-4">
                    <label class="tw-block tw-text-sm tw-font-medium tw-mb-2">Họ và tên</label>
                    <input v-model="form.full_name" type="text"
                        class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-[#81AACC]"
                        required maxlength="100">
                    <div v-if="errors.full_name" class="tw-text-red-500 tw-text-xs">{{ errors.full_name }}</div>
                </div>
                <div class="tw-mb-4">
                    <label class="tw-block tw-text-sm tw-font-medium tw-mb-2">Số điện thoại</label>
                    <input v-model="form.phone" type="tel"
                        class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-[#81AACC]"
                        required>
                    <div v-if="errors.phone" class="tw-text-red-500 tw-text-xs">{{ errors.phone }}</div>
                </div>
                <div class="tw-mb-4">
                    <label class="tw-block tw-text-sm tw-font-medium tw-mb-2">Tỉnh/Thành phố</label>
                    <select v-model="form.province"
                        class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-[#81AACC]"
                        required>
                        <option value="">Chọn Tỉnh/Thành phố</option>
                        <option v-for="province in provinces" :key="province.code" :value="province.code">
                            {{ province.name }}
                        </option>
                    </select>
                    <div v-if="errors.province" class="tw-text-red-500 tw-text-xs">{{ errors.province }}</div>
                </div>
                <div class="tw-mb-4">
                    <label class="tw-block tw-text-sm tw-font-medium tw-mb-2">Quận/Huyện</label>
                    <select v-model="form.district"
                        class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-[#81AACC]"
                        required>
                        <option value="">Chọn Quận/Huyện</option>
                        <option v-for="district in districts" :key="district.code" :value="district.code">
                            {{ district.name }}
                        </option>
                    </select>
                    <div v-if="errors.district" class="tw-text-red-500 tw-text-xs">{{ errors.district }}</div>
                </div>
                <div class="tw-mb-4">
                    <label class="tw-block tw-text-sm tw-font-medium tw-mb-2">Xã/Phường</label>
                    <select v-model="form.ward"
                        class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-[#81AACC]"
                        required>
                        <option value="">Chọn Xã/Phường</option>
                        <option v-for="ward in wards" :key="ward.code" :value="ward.code">
                            {{ ward.name }}
                        </option>
                    </select>
                    <div v-if="errors.ward" class="tw-text-red-500 tw-text-xs">{{ errors.ward }}</div>
                </div>
                <div class="tw-mb-4">
                    <label class="tw-block tw-text-sm tw-font-medium tw-mb-2">Thôn/Xóm</label>
                    <input v-model="form.street" type="text"
                        class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-[#81AACC]"
                        required maxlength="100">
                    <div v-if="errors.street" class="tw-text-red-500 tw-text-xs">{{ errors.street }}</div>
                </div>
            </div>
            <button type="submit"
                class="tw-bg-[#81AACC] tw-text-white tw-px-4 tw-py-2 tw-rounded-md hover:tw-bg-green-600 tw-transition-colors">
                Thêm địa chỉ
            </button>
        </form>

        <div class="tw-overflow-x-auto">
            <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
                <thead class="tw-bg-gray-50">
                    <tr>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Họ và tên</th>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Số điện thoại</th>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Địa chỉ</th>
                        <th class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Thao tác</th>
                    </tr>
                </thead>
                <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                    <tr v-for="address in addresses" :key="address.id">
                        <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">{{ address.full_name }}</td>
                        <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">{{ address.phone }}</td>
                        <td class="tw-px-6 tw-py-4">{{ getFullAddress(address) }}</td>
                        <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                            <button @click="deleteAddress(address.id)"
                                class="tw-text-red-600 hover:tw-text-red-800 tw-mr-2">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button @click="editAddress(address)" class="tw-text-blue-600 hover:tw-text-blue-800">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'

const form = ref({
    full_name: '',
    phone: '',
    province: '',
    district: '',
    ward: '',
    street: ''
})

const addresses = ref([])
const provinces = ref([])
const districts = ref([])
const wards = ref([])
const editingAddress = ref(null)
const errors = ref({})

const apiBaseUrl = useRuntimeConfig().public.apiBaseUrl
const API = axios.create({
    baseURL: apiBaseUrl.endsWith('/') ? apiBaseUrl : apiBaseUrl + '/'
})

// Lấy tỉnh/thành từ open-api.vn
const fetchProvinces = async () => {
    const res = await axios.get('https://provinces.open-api.vn/api/p/')
    provinces.value = res.data
}

// Lấy quận/huyện từ open-api.vn
const fetchDistricts = async () => {
    if (!form.value.province) return
    const res = await axios.get(`https://provinces.open-api.vn/api/p/${form.value.province}?depth=2`)
    districts.value = res.data?.districts || []
    wards.value = []
    form.value.district = ''
    form.value.ward = ''
}

// Lấy xã/phường từ open-api.vn
const fetchWards = async () => {
    if (!form.value.district) return
    const res = await axios.get(`https://provinces.open-api.vn/api/d/${form.value.district}?depth=2`)
    wards.value = res.data?.wards || []
    form.value.ward = ''
}

// Lấy danh sách địa chỉ từ backend
const fetchAddresses = async () => {
    try {
        const res = await API.get('/api/addresses')
        addresses.value = res.data.data || []
    } catch (error) {
        alert('Không thể lấy danh sách địa chỉ. Bạn cần đăng nhập!')
    }
}

function validateForm() {
    const err = {}
    if (!form.value.full_name) err.full_name = 'Họ và tên không được để trống'
    else if (form.value.full_name.length > 100) err.full_name = 'Họ và tên tối đa 100 ký tự'
    if (!form.value.phone) err.phone = 'Số điện thoại không được để trống'
    else if (!/^(0|\+84)[1-9][0-9]{8,9}$/.test(form.value.phone)) err.phone = 'Số điện thoại không hợp lệ'
    if (!form.value.province) err.province = 'Vui lòng chọn tỉnh/thành phố'
    if (!form.value.district) err.district = 'Vui lòng chọn quận/huyện'
    if (!form.value.ward) err.ward = 'Vui lòng chọn xã/phường'
    if (!form.value.street) err.street = 'Thôn/xóm không được để trống'
    else if (form.value.street.length > 100) err.street = 'Thôn/xóm tối đa 100 ký tự'
    errors.value = err
    return Object.keys(err).length === 0
}

// Thêm địa chỉ vào DB
const handleSubmit = async () => {
    if (!validateForm()) return
    try {
        const addressData = {
            full_name: form.value.full_name,
            phone: form.value.phone,
            province: provinces.value.find(p => p.code == form.value.province)?.name || form.value.province,
            district: districts.value.find(d => d.code == form.value.district)?.name || form.value.district,
            ward: wards.value.find(w => w.code == form.value.ward)?.name || form.value.ward,
            street: form.value.street
        }
        const res = await API.post('/api/addresses', addressData)
        addresses.value.push(res.data)
        form.value = {
            full_name: '',
            phone: '',
            province: '',
            district: '',
            ward: '',
            street: ''
        }
        errors.value = {}
        alert('Thêm địa chỉ thành công!')
    } catch (error) {
        alert('Có lỗi xảy ra khi thêm địa chỉ: ' + (error.response?.data?.message || error.message))
    }
}

const getFullAddress = (address) => {
    return `${address.street}, ${address.ward}, ${address.district}, ${address.province}`
}

const deleteAddress = async (id) => {
    await API.delete(`/api/addresses/${id}`)
    addresses.value = addresses.value.filter(addr => addr.id !== id)
}

const editAddress = (address) => {
    form.value = { ...address }
}

watch(() => form.value.province, (newValue) => {
    if (newValue) fetchDistricts()
    else {
        districts.value = []
        form.value.district = ''
        form.value.ward = ''
    }
})

watch(() => form.value.district, (newValue) => {
    if (newValue) fetchWards()
    else {
        wards.value = []
        form.value.ward = ''
    }
})

onMounted(async () => {
    await fetchProvinces()
    await fetchAddresses()
})
</script>