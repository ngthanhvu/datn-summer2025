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
                        <th class="tw-px-3 tw-py-2 tw-text-center">
                            <div class="tw-flex tw-items-center">
                                <input type="checkbox" :checked="selectedBrands.size === brands.length"
                                    @change="toggleSelectAll" class="tw-rounded">
                            </div>
                        </th>
                        <th class="tw-px-3 tw-py-2 tw-text-center">#</th>
                        <th class="tw-px-3 tw-py-2 tw-text-center">Logo</th>
                        <th class="tw-px-3 tw-py-2 tw-text-center">Tên thương hiệu</th>
                        <th class="tw-px-3 tw-py-2 tw-text-center">Mô tả</th>
                        <th class="tw-px-3 tw-py-2 tw-text-center">Danh mục cha</th>
                        <th class="tw-px-3 tw-py-2 tw-text-center">Số lượng sản phẩm</th>
                        <th class="tw-px-3 tw-py-2 tw-text-center">Trạng thái</th>
                        <th class="tw-px-3 tw-py-2 tw-text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Skeleton loading -->
                    <tr v-if="props.isLoading" v-for="n in 8" :key="'skeleton-' + n">
                        <td v-for="i in 8" :key="i" class="tw-px-4 tw-py-3 text-center">
                            <div class="skeleton-loader"></div>
                        </td>
                    </tr>
                    <template v-else-if="brands.length > 0">
                        <tr v-for="(brand, index) in brands" :key="brand.id" class="tw-border-b hover:tw-bg-gray-50">
                            <td class="tw-px-3 tw-py-2">
                                <input type="checkbox" :checked="selectedBrands.has(brand.id)"
                                    @change="toggleSelect(brand.id)" class="tw-rounded">
                            </td>
                            <td class="tw-px-4 tw-py-3 text-center">{{ index + 1 }}</td>
                            <td class="tw-px-4 tw-py-3 text-center">
                                <img :src="brand.image" :alt="brand.name"
                                    class="tw-w-10 tw-h-10 tw-object-cover tw-rounded">
                            </td>
                            <td class="tw-px-4 tw-py-3 text-center">{{ brand.name }}</td>
                            <td class="tw-px-4 tw-py-3 text-center">{{ brand.description }}</td>
                            <td class="tw-px-4 tw-py-3 text-center">{{ getParentBrandName(brand) }}</td>
                            <td class="tw-px-4 tw-py-3 text-center">{{ brand.products_count }}</td>
                            <td class="tw-px-4 tw-py-3 text-center">
                                <span :class="getStatusClass(brand.is_active)">
                                    {{ getStatusText(brand.is_active) }}
                                </span>
                            </td>
                            <td class="tw-px-4 tw-py-3 text-center">
                                <div class="tw-flex tw-items-center tw-gap-2">
                                    <NuxtLink :to="`/admin/brands/${brand.id}/edit`"
                                        class="tw-inline-flex tw-items-center tw-p-1.5 tw-text-blue-600 hover:tw-text-blue-900 hover:tw-bg-blue-50 tw-rounded-lg tw-transition-colors tw-duration-150"
                                        title="Chỉnh sửa thương hiệu">
                                        <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                    </NuxtLink>
                                    <button @click="handleDelete(brand)"
                                        class="tw-inline-flex tw-items-center tw-p-1.5 tw-text-red-600 hover:tw-text-red-900 hover:tw-bg-red-50 tw-rounded-lg tw-transition-colors tw-duration-150"
                                        title="Xóa thương hiệu">
                                        <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr v-else>
                        <td colspan="8" class="tw-px-3 text-center tw-py-2 tw-text-center tw-text-gray-500">
                            Không có dữ liệu
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
    },
    isLoading: {
        type: Boolean,
        default: false
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

<style scoped>
.skeleton-loader {
    height: 20px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 37%, #f0f0f0 63%);
    border-radius: 4px;
    animation: skeleton-loading 3.2s infinite;
}

@keyframes skeleton-loading {
    0% {
        background-position: -200px 0;
    }

    100% {
        background-position: calc(200px + 100%) 0;
    }
}
</style>