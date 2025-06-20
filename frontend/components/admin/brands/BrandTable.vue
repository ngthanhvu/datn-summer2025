<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-4">
        <div class="tw-flex tw-justify-end tw-mb-4" v-if="brands.length > 0">
            <button v-if="selectedBrands.size > 0" @click="$emit('bulkDelete', selectedBrands)"
                class="tw-bg-red-500 tw-text-white tw-rounded tw-px-3 tw-py-1 hover:tw-bg-red-600 tw-flex tw-items-center tw-gap-2">
                <i class="fas fa-trash"></i>
                Xóa {{ selectedBrands.size }} mục đã chọn
            </button>
        </div>
        <div class="tw-overflow-x-auto">
            <table class="tw-w-full tw-text-sm">
                <thead>
                    <tr class="tw-border-b tw-bg-gray-50">
                        <th class="tw-px-3 tw-py-2 tw-text-left">
                            <div class="tw-flex tw-items-center tw-gap-2">
                                <input type="checkbox" :checked="selectedBrands.size === brands.length"
                                    @change="toggleSelectAll" class="tw-rounded">
                            </div>
                        </th>
                        <th class="tw-px-3 tw-py-2 tw-text-left">ID</th>
                        <th class="tw-px-3 tw-py-2 tw-text-left">Logo</th>
                        <th class="tw-px-3 tw-py-2 tw-text-left">Tên thương hiệu</th>
                        <th class="tw-px-3 tw-py-2 tw-text-left">Mô tả</th>
                        <th class="tw-px-3 tw-py-2 tw-text-left">Danh mục cha</th>
                        <th class="tw-px-3 tw-py-2 tw-text-left">Trạng thái</th>
                        <th class="tw-px-3 tw-py-2 tw-text-left">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-if="brands.length > 0">
                        <tr v-for="brand in brands" :key="brand.id" class="tw-border-b hover:tw-bg-gray-50">
                            <td class="tw-px-4 tw-py-3">
                                <input type="checkbox" :checked="selectedBrands.has(brand.id)"
                                    @change="toggleSelect(brand.id)" class="tw-rounded">
                            </td>
                            <td class="tw-px-4 tw-py-3">#{{ brand.id }}</td>
                            <td class="tw-px-4 tw-py-3">
                                <img :src="brand.image" :alt="brand.name"
                                    class="tw-w-10 tw-h-10 tw-object-cover tw-rounded">
                            </td>
                            <td class="tw-px-4 tw-py-3">{{ brand.name }}</td>
                            <td class="tw-px-4 tw-py-3">{{ brand.description }}</td>
                            <td class="tw-px-4 tw-py-3">{{ getParentBrandName(brand) }}</td>
                            <td class="tw-px-4 tw-py-3">
                                <span :class="getStatusClass(brand.is_active)">
                                    {{ getStatusText(brand.is_active) }}
                                </span>
                            </td>
                            <td class="tw-px-4 tw-py-3">
                                <div class="tw-flex tw-gap-2">
                                    <NuxtLink :to="`/admin/brands/${brand.id}/edit`"
                                        class="tw-bg-blue-500 tw-text-white tw-rounded tw-px-2 tw-py-1 hover:tw-bg-blue-600">
                                        <i class="fas fa-edit"></i>
                                    </NuxtLink>
                                    <button @click="handleDelete(brand)"
                                        class="tw-bg-red-500 tw-text-white tw-rounded tw-px-2 tw-py-1 hover:tw-bg-red-600">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr v-else>
                        <td colspan="8" class="tw-py-8">
                            <div class="tw-text-center tw-text-gray-500">
                                <i class="fas fa-box-open tw-text-4xl tw-mb-3"></i>
                                <p class="tw-text-lg">Không có thương hiệu nào</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import Swal from 'sweetalert2'

const props = defineProps({
    brands: {
        type: Array,
        required: true
    }
})

const selectedBrands = ref(new Set())

const toggleSelectAll = (event) => {
    if (event.target.checked) {
        selectedBrands.value = new Set(props.brands.map(brand => brand.id))
    } else {
        selectedBrands.value.clear()
    }
}

const toggleSelect = (brandId) => {
    if (selectedBrands.value.has(brandId)) {
        selectedBrands.value.delete(brandId)
    } else {
        selectedBrands.value.add(brandId)
    }
}

const getParentBrandName = (brand) => {
    if (!brand.parent_id) return 'Không có thương hiệu cha'
    const parentBrand = props.brands.find(b => b.id === brand.parent_id)
    return parentBrand ? parentBrand.name : 'Không có thương hiệu cha'
}

const getStatusClass = (isActive) => {
    return [
        'tw-px-2 tw-py-1 tw-rounded-full tw-text-xs',
        Number(isActive) === 1 ? 'tw-bg-green-100 tw-text-green-700' : 'tw-bg-red-100 tw-text-red-700'
    ]
}

const getStatusText = (isActive) => {
    return Number(isActive) === 1 ? 'Hoạt động' : 'Vô hiệu'
}

const handleDelete = async (brand) => {
    Swal.fire({
        title: 'Bạn có chắc chắn muốn xóa thương hiệu?',
        text: `Bạn có chắc chắn muốn xóa thương hiệu "${brand.name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Xóa',
    }).then(async (result) => {
        if (result.isConfirmed) {
            emit('delete', brand)
        }
    })
}

const emit = defineEmits(['delete', 'bulkDelete'])
</script>