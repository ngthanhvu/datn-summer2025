<template>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex justify-end mb-4" v-if="brands.length > 0">
            <button v-if="selectedBrands.size > 0" @click="$emit('bulkDelete', selectedBrands)"
                class="bg-red-500 text-white rounded px-3 py-1 hover:bg-red-600 flex items-center gap-2">
                <i class="fas fa-trash"></i>
                Xóa {{ selectedBrands.size }} mục đã chọn
            </button>
        </div>
        <div class="overflow-x-auto overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="px-3 py-2 text-center">
                            <div class="flex items-center">
                                <input type="checkbox" :checked="selectedBrands.size === paginatedBrands.length"
                                    @change="toggleSelectAll" class="rounded">
                            </div>
                        </th>
                        <th class="px-6 py-3 whitespace-nowrap">#</th>
                        <th class="px-6 py-3 text-left">Logo</th>
                        <th class="px-6 py-3 whitespace-nowrap">Tên thương hiệu</th>
                        <th class="px-6 py-3 whitespace-nowrap">Mô tả</th>
                        <th class="px-6 py-3 whitespace-nowrap">Danh mục cha</th>
                        <th class="px-6 py-3 whitespace-nowrap">Số lượng sản phẩm</th>
                        <th class="px-6 py-3 whitespace-nowrap">Trạng thái</th>
                        <th class="px-6 py-3 text-left">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Skeleton loading -->
                    <tr v-if="props.isLoading" v-for="n in 8" :key="'skeleton-' + n">
                        <td v-for="i in 8" :key="i" class="px-4 py-3 text-center">
                            <div class="skeleton-loader"></div>
                        </td>
                    </tr>
                    <template v-else-if="brands.length > 0">
                        <tr v-for="(brand, index) in paginatedBrands" :key="brand.id"
                            class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-3 py-2">
                                <input type="checkbox" :checked="selectedBrands.has(brand.id)"
                                    @change="toggleSelect(brand.id)" class="rounded">
                            </td>
                            <td class="px-4 py-3 text-center">{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                            <td class="px-4 py-3 text-center">
                                <img :src="brand.image" :alt="brand.name" class="w-10 h-10 object-cover rounded">
                            </td>
                            <td class="px-4 py-3 text-center">{{ brand.name }}</td>
                            <td class="px-4 py-3 text-center">{{ brand.description }}</td>
                            <td class="px-4 py-3 text-center">{{ getParentBrandName(brand) }}</td>
                            <td class="px-4 py-3 text-center">{{ brand.products_count }}</td>
                            <td class="px-4 py-3 text-center">
                                <button
                                    :class="['w-10 h-6 rounded-full relative transition-colors', Number(brand.is_active) === 1 ? 'bg-primary' : 'bg-gray-300']"
                                    @click="toggleStatus(brand)" :aria-pressed="Number(brand.is_active) === 1"
                                    style="background-color: #3bb77e">
                                    <span
                                        :class="['absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform', Number(brand.is_active) === 1 ? 'translate-x-4' : '']"></span>
                                </button>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <router-link :to="`/admin/brands/edit/${brand.id}`"
                                        class="inline-flex items-center p-1.5 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-colors duration-150"
                                        title="Chỉnh sửa thương hiệu">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                    </router-link>
                                    <button @click="handleDelete(brand)"
                                        class="inline-flex items-center p-1.5 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors duration-150"
                                        title="Xóa thương hiệu">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <td colspan="9" class="px-3 text-center py-2 text-gray-500">
                            Không có dữ liệu
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="!props.isLoading && totalPages > 1" class="flex justify-between items-center mt-6">
            <div class="text-sm text-gray-600">
                Hiển thị {{ paginatedBrands.length }} trên tổng số {{ brands.length }} bản ghi
            </div>
            <div class="flex gap-2">
                <button :disabled="currentPage === 1" @click="goToPage(currentPage - 1)"
                    class="px-3 py-1 border border-gray-400 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span class="px-3 py-1">
                    Trang {{ currentPage }} / {{ totalPages }}
                </span>
                <button :disabled="currentPage === totalPages" @click="goToPage(currentPage + 1)"
                    class="px-3 py-1 border border-gray-400 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors cursor-pointer">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
const props = defineProps({
    brands: {
        type: Array,
        default: () => []
    },
    isLoading: {
        type: Boolean,
        default: false
    },
    currentPage: {
        type: Number,
        default: 1
    },
    itemsPerPage: {
        type: Number,
        default: 10
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
        'px-2 py-1 rounded-full text-xs',
        Number(isActive) === 1 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
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

const paginatedBrands = computed(() => {
    const start = (props.currentPage - 1) * props.itemsPerPage
    const end = start + props.itemsPerPage
    return props.brands.slice(start, end)
})

const totalPages = computed(() => Math.ceil(props.brands.length / props.itemsPerPage))

const emit = defineEmits(['update:currentPage', 'delete', 'bulkDelete'])

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        emit('update:currentPage', page)
    }
}

const toggleStatus = async (brand) => {
    const newStatus = Number(brand.is_active) === 1 ? 0 : 1
    try {
        await updateBrandStatus(brand.id, newStatus)
        brand.is_active = newStatus
        // Nếu có notyf hoặc emit refresh thì gọi ở đây
    } catch (e) {
        // Nếu có notyf thì báo lỗi ở đây
    }
}

// Hàm giả lập gọi API cập nhật trạng thái
const updateBrandStatus = async (id, status) => {
    // TODO: Thay bằng gọi API thực tế
    return new Promise((resolve) => setTimeout(resolve, 500))
}
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