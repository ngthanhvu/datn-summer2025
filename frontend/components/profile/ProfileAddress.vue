<template>
    <div class="tw-bg-white tw-p-6 tw-rounded tw-shadow">
        <h2 class="tw-font-bold tw-text-lg tw-mb-6">Địa chỉ</h2>
        <form @submit.prevent="handleSubmit" class="tw-mb-8">
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-4">
                <div class="tw-mb-4">
                    <label class="tw-block tw-text-sm tw-font-medium tw-mb-2">Họ và tên</label>
                    <input v-model="form.full_name" type="text"
                        class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-[#81AACC]"
                        maxlength="100" placeholder="Nhập họ và tên">
                    <div v-if="errors.full_name" class="tw-text-red-500 tw-text-xs">{{ errors.full_name }}</div>
                </div>
                <div class="tw-mb-4">
                    <label class="tw-block tw-text-sm tw-font-medium tw-mb-2">Số điện thoại</label>
                    <input v-model="form.phone" type="tel"
                        class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-[#81AACC]"
                        maxlength="10" placeholder="Nhập số điện thoại">
                    <div v-if="errors.phone" class="tw-text-red-500 tw-text-xs">{{ errors.phone }}</div>
                </div>
                <div class="tw-mb-4">
                    <label class="tw-block tw-text-sm tw-font-medium tw-mb-2">Tỉnh/Thành phố</label>
                    <select v-model="form.province"
                        class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-[#81AACC]">
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
                        class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-[#81AACC]">
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
                        class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-[#81AACC]">
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
                        maxlength="100" placeholder="Nhập thôn/xóm">
                    <div v-if="errors.street" class="tw-text-red-500 tw-text-xs">{{ errors.street }}</div>
                </div>
            </div>
            <button type="submit"
                class="tw-bg-[#81AACC] tw-text-white tw-px-4 tw-py-2 tw-rounded-md hover:tw-bg-[#5b98ca] tw-transition-colors">
                Thêm địa chỉ
            </button>
        </form>

        <!-- Desktop Table -->
        <div class="tw-hidden md:tw-block tw-overflow-x-auto">
            <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
                <thead class="tw-bg-gray-50">
                    <tr>
                        <th
                            class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Họ và tên</th>
                        <th
                            class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Số điện thoại</th>
                        <th
                            class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Địa chỉ</th>
                        <th
                            class="tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Thao tác</th>
                    </tr>
                </thead>
                <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200">
                    <tr v-for="address in addresses" :key="address.id">
                        <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">{{ address.full_name }}</td>
                        <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">{{ address.phone }}</td>
                        <td class="tw-px-6 tw-py-4">{{ getFullAddress(address) }}</td>
                        <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">
                            <button @click="handleDeleteAddress(address.id)"
                                class="tw-text-red-600 hover:tw-text-red-800 tw-mr-2">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button @click="editAddress(address)" class="tw-text-blue-600 hover:tw-text-blue-800">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                    <tr v-if="addresses.length === 0">
                        <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap tw-text-center tw-text-gray-500 tw-text-[14px]"
                            colspan="4">
                            không có địa chỉ nào
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:tw-hidden tw-space-y-4">
            <div v-for="address in addresses" :key="address.id"
                class="tw-bg-white tw-border tw-rounded-lg tw-p-4 tw-space-y-2">
                <div class="tw-flex tw-justify-between tw-items-center">
                    <p class="tw-font-medium tw-text-sm">{{ address.full_name }}</p>
                    <div class="tw-flex tw-gap-2">
                        <button @click="handleDeleteAddress(address.id)"
                            class="tw-text-red-600 hover:tw-text-red-800 tw-text-lg">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button @click="editAddress(address)"
                            class="tw-text-blue-600 hover:tw-text-blue-800 tw-text-lg">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </div>
                <p class="tw-text-gray-700 tw-text-sm"><span class="tw-font-semibold">SĐT:</span> {{ address.phone }}
                </p>
                <p class="tw-text-gray-700 tw-text-sm"><span class="tw-font-semibold">Địa chỉ:</span> {{
                    getFullAddress(address) }}</p>
            </div>
            <div v-if="addresses.length === 0" class="tw-text-center tw-py-4 tw-text-gray-500 tw-text-sm">
                Không có địa chỉ nào
            </div>
        </div>
    </div>
</template>

<script setup>
useHead({
    title: 'Địa chỉ',
    meta: [
        {
            name: 'description',
            content: 'Địa chỉ',
        },
    ],
})
import { ref, onMounted, watch } from 'vue'
import { useAddress } from '~/composables/useAddress'
const notyf = useNuxtApp().$notyf

const {
    form,
    errors,
    getProvinces,
    getDistricts,
    getWards,
    getMyAddress,
    createAddress,
    deleteAddress,
    validateForm,
    getFullAddress,
    resetForm,
    setFormData
} = useAddress()

const addresses = ref([])
const provinces = ref([])
const districts = ref([])
const wards = ref([])

const fetchProvinces = async () => {
    provinces.value = await getProvinces()
}

const fetchDistricts = async () => {
    if (!form.value.province) return
    districts.value = await getDistricts(form.value.province)
    wards.value = []
    form.value.district = ''
    form.value.ward = ''
}

const fetchWards = async () => {
    if (!form.value.district) return
    wards.value = await getWards(form.value.district)
    form.value.ward = ''
}

const fetchAddresses = async () => {
    try {
        const res = await getMyAddress()
        addresses.value = Array.isArray(res) ? res : []
    } catch (error) {
        console.error('Error fetching addresses:', error)
        addresses.value = []
    }
}

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
        const res = await createAddress(addressData)
        addresses.value.push(res)
        resetForm()
        notyf.success('Thêm địa chỉ thành công!')
    } catch (error) {
        console.log(error)
    }
}

const handleDeleteAddress = async (id) => {
    const result = await deleteAddress(id)
    if (result) {
        addresses.value = addresses.value.filter(addr => addr.id !== id)
    }
}

const editAddress = (address) => {
    setFormData(address)
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