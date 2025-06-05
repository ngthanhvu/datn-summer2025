<template>
    <div class="tw-bg-white tw-p-6 tw-rounded tw-shadow">
        <h2 class="tw-font-bold tw-text-lg tw-mb-6">Địa chỉ</h2>

        <!-- Form thêm địa chỉ -->
        <form @submit.prevent="handleSubmit" class="tw-mb-8">
            <div class="tw-grid tw-grid-cols-2 tw-gap-4">
                <div class="tw-mb-4">
                    <label class="tw-block tw-text-sm tw-font-medium tw-mb-2">Họ và tên</label>
                    <input v-model="form.name" type="text"
                        class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-[#81AACC]"
                        required>
                </div>

                <div class="tw-mb-4">
                    <label class="tw-block tw-text-sm tw-font-medium tw-mb-2">Số điện thoại</label>
                    <input v-model="form.phone" type="tel"
                        class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-[#81AACC]"
                        required>
                </div>

                <div class="tw-mb-4">
                    <label class="tw-block tw-text-sm tw-font-medium tw-mb-2">Tỉnh/Thành phố</label>
                    <select v-model="form.province"
                        class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-[#81AACC]"
                        required>
                        <option value="">Chọn Tỉnh/Thành phố</option>
                        <option v-for="province in provinces" :key="province.id" :value="province.id">
                            {{ province.name }}
                        </option>
                    </select>
                </div>

                <div class="tw-mb-4">
                    <label class="tw-block tw-text-sm tw-font-medium tw-mb-2">Quận/Huyện</label>
                    <select v-model="form.district"
                        class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-[#81AACC]"
                        required>
                        <option value="">Chọn Quận/Huyện</option>
                        <option v-for="district in districts" :key="district.id" :value="district.id">
                            {{ district.name }}
                        </option>
                    </select>
                </div>

                <div class="tw-mb-4">
                    <label class="tw-block tw-text-sm tw-font-medium tw-mb-2">Xã/Phường</label>
                    <select v-model="form.ward"
                        class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-[#81AACC]"
                        required>
                        <option value="">Chọn Xã/Phường</option>
                        <option v-for="ward in wards" :key="ward.id" :value="ward.id">
                            {{ ward.name }}
                        </option>
                    </select>
                </div>

                <div class="tw-mb-4">
                    <label class="tw-block tw-text-sm tw-font-medium tw-mb-2">Thôn/Xóm</label>
                    <input v-model="form.street" type="text"
                        class="tw-w-full tw-px-3 tw-py-2 tw-border tw-rounded-md focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-[#81AACC]"
                        required>
                </div>
            </div>

            <button type="submit"
                class="tw-bg-[#81AACC] tw-text-white tw-px-4 tw-py-2 tw-rounded-md hover:tw-bg-green-600 tw-transition-colors">
                Thêm địa chỉ
            </button>
        </form>

        <!-- Bảng danh sách địa chỉ -->
        <div class="tw-overflow-x-auto">
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
                        <td class="tw-px-6 tw-py-4 tw-whitespace-nowrap">{{ address.name }}</td>
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
const form = ref({
    name: '',
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

// Hàm xử lý khi submit form
const handleSubmit = () => {
    // Thêm địa chỉ mới vào danh sách
    addresses.value.push({
        id: Date.now(),
        ...form.value
    })

    // Reset form
    form.value = {
        name: '',
        phone: '',
        province: '',
        district: '',
        ward: '',
        street: ''
    }
}

// Hàm lấy địa chỉ đầy đủ
const getFullAddress = (address) => {
    return `${address.street}, ${address.ward}, ${address.district}, ${address.province}`
}

// Hàm xóa địa chỉ
const deleteAddress = (id) => {
    addresses.value = addresses.value.filter(addr => addr.id !== id)
}

// Hàm chỉnh sửa địa chỉ
const editAddress = (address) => {
    form.value = { ...address }
}

// Theo dõi thay đổi của tỉnh/thành phố để cập nhật quận/huyện
watch(() => form.value.province, (newValue) => {
    if (newValue) {
        // Gọi API lấy danh sách quận/huyện
        // fetchDistricts(newValue)
    } else {
        districts.value = []
        form.value.district = ''
        form.value.ward = ''
    }
})

// Theo dõi thay đổi của quận/huyện để cập nhật xã/phường
watch(() => form.value.district, (newValue) => {
    if (newValue) {
        // Gọi API lấy danh sách xã/phường
        // fetchWards(newValue)
    } else {
        wards.value = []
        form.value.ward = ''
    }
})

// Khi component được tạo
onMounted(async () => {
    // Gọi API lấy danh sách tỉnh/thành phố
    // const data = await fetchProvinces()
    // provinces.value = data
})
</script>