<template>
    <div class="bg-white p-6 rounded shadow">
        <h2 class="font-bold text-lg mb-6">Địa chỉ</h2>
        <form @submit.prevent="handleSubmit" class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Họ và tên</label>
                    <input v-model="form.full_name" type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-[#81AACC]"
                        maxlength="100" placeholder="Nhập họ và tên">
                    <div v-if="errors.full_name" class="text-red-500 text-xs">{{ errors.full_name }}</div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Số điện thoại</label>
                    <input v-model="form.phone" type="tel"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-[#81AACC]"
                        maxlength="10" placeholder="Nhập số điện thoại">
                    <div v-if="errors.phone" class="text-red-500 text-xs">{{ errors.phone }}</div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Tỉnh/Thành phố</label>
                    <select v-model="form.province" @change="onProvinceChange"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-[#81AACC]">
                        <option value="">Chọn Tỉnh/Thành phố</option>
                        <option v-for="province in provinces" :key="province.ProvinceID" :value="province.ProvinceName">
                            {{ province.ProvinceName }}
                        </option>
                    </select>
                    <div v-if="errors.province" class="text-red-500 text-xs">{{ errors.province }}</div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Quận/Huyện</label>
                    <select v-model="form.district" @change="onDistrictChange"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-[#81AACC]">
                        <option value="">Chọn Quận/Huyện</option>
                        <option v-for="district in districts" :key="district.DistrictID" :value="district.DistrictName">
                            {{ district.DistrictName }}
                        </option>
                    </select>
                    <div v-if="errors.district" class="text-red-500 text-xs">{{ errors.district }}</div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Xã/Phường</label>
                    <select v-model="form.ward"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-[#81AACC]">
                        <option value="">Chọn Xã/Phường</option>
                        <option v-for="ward in wards" :key="ward.WardCode" :value="ward.WardName">
                            {{ ward.WardName }}
                        </option>
                    </select>
                    <div v-if="errors.ward" class="text-red-500 text-xs">{{ errors.ward }}</div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Địa chỉ chi tiết</label>
                    <input v-model="form.street" type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-[#81AACC]"
                        maxlength="100" placeholder="Số nhà, tên đường">
                    <div v-if="errors.street" class="text-red-500 text-xs">{{ errors.street }}</div>
                </div>
            </div>
            <button type="submit"
                class="bg-[#81AACC] text-white px-4 py-2 rounded-md hover:bg-[#5b98ca] transition-colors">
                Thêm địa chỉ
            </button>
        </form>

        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            Họ và tên</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            Số điện thoại</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            Địa chỉ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="address in addresses" :key="address.id">
                        <td class="px-6 py-4 whitespace-nowrap">{{ address.full_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ address.phone }}</td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                <div class="font-medium">{{ address.street }}</div>
                                <div class="text-gray-600">{{ address.ward }}, {{ address.district }}, {{
                                    address.province }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button @click="handleDeleteAddress(address.id)"
                                class="text-red-600 hover:text-red-800 mr-2">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button @click="editAddress(address)" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                    <tr v-if="addresses.length === 0">
                        <td class="px-6 py-4 whitespace-nowrap text-center text-gray-500 text-[14px]" colspan="4">
                            không có địa chỉ nào
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden space-y-4">
            <div v-for="address in addresses" :key="address.id"
                class="bg-white border border-gray-300 rounded-lg p-4 space-y-2">
                <div class="flex justify-between items-center">
                    <p class="font-medium text-sm">{{ address.full_name }}</p>
                    <div class="flex gap-2">
                        <button @click="handleDeleteAddress(address.id)"
                            class="text-red-600 hover:text-red-800 text-lg">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button @click="editAddress(address)" class="text-blue-600 hover:text-blue-800 text-lg">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </div>
                <p class="text-gray-700 text-sm"><span class="font-semibold">SĐT:</span> {{ address.phone }}
                </p>
                <div class="text-gray-700 text-sm">
                    <div class="font-semibold">Địa chỉ:</div>
                    <div class="mt-1">{{ address.street }}</div>
                    <div class="text-gray-600">{{ address.ward }}, {{ address.district }}, {{ address.province }}</div>
                </div>
            </div>
            <div v-if="addresses.length === 0" class="text-center py-4 text-gray-500 text-sm">
                Không có địa chỉ nào
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import { useHead } from "@vueuse/head";
import { useAddress } from "../../composable/useAddress";
import { push } from "notivue";

useHead({
    title: "Địa chỉ của tôi | DEVGANG",
    meta: [{ name: "description", content: "Địa chỉ" }],
});


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
    setFormData,
} = useAddress();

const addresses = ref([]);
const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);

const fetchProvinces = async () => {
    provinces.value = await getProvinces();
};

const fetchDistricts = async () => {
    if (!form.value.province) return;

    // Tìm ProvinceID từ ProvinceName
    const selectedProvince = provinces.value.find((p) => p.ProvinceName === form.value.province);
    if (!selectedProvince) return;

    districts.value = await getDistricts(selectedProvince.ProvinceID);
    wards.value = [];
    form.value.district = "";
    form.value.ward = "";
};

const fetchWards = async () => {
    if (!form.value.district) return;

    // Tìm DistrictID từ DistrictName
    const selectedDistrict = districts.value.find((d) => d.DistrictName === form.value.district);
    if (!selectedDistrict) return;

    wards.value = await getWards(selectedDistrict.DistrictID);
    form.value.ward = "";
};

const fetchAddresses = async () => {
    try {
        const res = await getMyAddress();
        addresses.value = Array.isArray(res) ? res : [];
    } catch (error) {
        addresses.value = [];
        push.error("Không thể tải danh sách địa chỉ!");
    }
};

const handleSubmit = async () => {
    if (!validateForm()) return;
    try {
        const addressData = {
            full_name: form.value.full_name,
            phone: form.value.phone,
            province: form.value.province,
            district: form.value.district,
            ward: form.value.ward,
            street: form.value.street,
        };

        const res = await createAddress(addressData);
        addresses.value.push(res);
        resetForm();
        push.success("Thêm địa chỉ thành công!");
    } catch (error) {
        console.error(error);
    }
};

const handleDeleteAddress = async (id) => {
    try {
        const result = await deleteAddress(id);
        if (result) {
            addresses.value = addresses.value.filter((addr) => addr.id !== id);
            push.success("Xóa địa chỉ thành công!");
        } else {
            push.error("Xóa địa chỉ thất bại!");
        }
    } catch (error) {
        console.error("Error deleting address:", error);
    }
};

const editAddress = (address) => {
    setFormData(address);

    if (address.province) {
        const selectedProvince = provinces.value.find((p) => p.ProvinceName === address.province);
        if (selectedProvince) {
            form.value.province = address.province;
            fetchDistricts().then(() => {
                if (address.district) {
                    const selectedDistrict = districts.value.find((d) => d.DistrictName === address.district);
                    if (selectedDistrict) {
                        form.value.district = address.district;
                        fetchWards().then(() => {
                            if (address.ward) {
                                form.value.ward = address.ward;
                            }
                        });
                    }
                }
            });
        }
    }
};

const onProvinceChange = () => {
    form.value.district = "";
    form.value.ward = "";
    districts.value = [];
    wards.value = [];
    fetchDistricts();
};

const onDistrictChange = () => {
    form.value.ward = "";
    wards.value = [];
    fetchWards();
};

watch(
    () => form.value.province,
    (newValue) => {
        if (!newValue) {
            districts.value = [];
            form.value.district = "";
            form.value.ward = "";
        }
    }
);

watch(
    () => form.value.district,
    (newValue) => {
        if (!newValue) {
            wards.value = [];
            form.value.ward = "";
        }
    }
);

onMounted(async () => {
    await fetchProvinces();
    await fetchAddresses();
});
</script>
