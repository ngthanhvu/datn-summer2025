<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-4">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-3">
            <div class="tw-flex tw-gap-3">
                <div class="tw-relative">
                    <input type="text" v-model="searchQuery" placeholder="Tìm kiếm..." @input="handleSearch"
                        class="tw-border tw-rounded tw-px-3 tw-py-1.5 tw-pl-8 tw-w-56 tw-text-sm">
                    <i
                        class="fas fa-search tw-absolute tw-left-2 tw-top-1/2 tw-transform -tw-translate-y-1/2 tw-text-gray-400"></i>
                </div>

                <select v-if="categories.length" v-model="selectedCategory"
                    class="tw-border tw-rounded tw-px-3 tw-py-1.5 tw-w-48 tw-text-sm">
                    <option value="">Tất cả danh mục</option>
                    <option v-for="category in categories" :key="category.value" :value="category.value">
                        {{ category.label }}
                    </option>
                </select>

                <select v-if="brands.length" v-model="selectedBrand"
                    class="tw-border tw-rounded tw-px-3 tw-py-1.5 tw-w-48 tw-text-sm">
                    <option value="">Tất cả thương hiệu</option>
                    <option v-for="brand in brands" :key="brand.value" :value="brand.value">
                        {{ brand.label }}
                    </option>
                </select>

                <select v-model="selectedStatus" class="tw-border tw-rounded tw-px-3 tw-py-1.5 tw-w-48 tw-text-sm">
                    <option value="">Tất cả trạng thái</option>
                    <option value="1">Hoạt động</option>
                    <option value="0">Vô hiệu</option>
                </select>
            </div>

            <div class="tw-flex tw-gap-2">
                <button v-if="selectedRows.length > 0" @click="handleBulkDelete"
                    class="tw-bg-red-600 tw-text-white tw-rounded tw-px-3 tw-py-1.5 tw-flex tw-items-center tw-gap-2 tw-text-sm hover:tw-bg-red-700">
                    <i class="fas fa-trash"></i>
                    Xoá đã chọn
                </button>
                <button @click="showImportModal = true"
                    class="tw-bg-primary tw-text-white tw-rounded tw-px-3 tw-py-1.5 tw-flex tw-items-center tw-gap-2 tw-text-sm hover:tw-bg-primary-dark">
                    <i class="fa-solid fa-file-import"></i>Nhập excel
                </button>
                <NuxtLink to="/admin/products/create"
                    class="tw-bg-primary tw-text-white tw-rounded tw-px-3 tw-py-1.5 tw-flex tw-items-center tw-gap-2 tw-text-sm hover:tw-bg-primary-dark">
                    <i class="fas fa-plus"></i>
                    Thêm mới
                </NuxtLink>
            </div>
        </div>

        <div v-if="showImportModal"
            class="tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-items-center tw-justify-center tw-z-50">
            <div class="tw-bg-white tw-rounded-lg tw-p-6 tw-w-96">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
                    <h3 class="tw-text-lg tw-font-semibold">Nhập sản phẩm từ Excel</h3>
                    <button @click="showImportModal = false" class="tw-text-gray-500 hover:tw-text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="tw-space-y-4">
                    <div class="tw-border tw-rounded-lg tw-p-4">
                        <h4 class="tw-font-medium tw-mb-2">Tải file mẫu</h4>
                        <p class="tw-text-sm tw-text-gray-600 tw-mb-3">Tải file mẫu Excel để nhập dữ liệu sản phẩm</p>
                        <button @click="handleDownloadTemplate"
                            class="tw-w-full tw-bg-gray-100 tw-text-gray-700 tw-rounded tw-px-3 tw-py-2 tw-flex tw-items-center tw-justify-center tw-gap-2 hover:tw-bg-gray-200">
                            <i class="fa-solid fa-download"></i>
                            Tải file mẫu
                        </button>
                    </div>

                    <div class="tw-border tw-rounded-lg tw-p-4">
                        <h4 class="tw-font-medium tw-mb-2">Tải lên file Excel</h4>
                        <p class="tw-text-sm tw-text-gray-600 tw-mb-3">Chọn file Excel đã điền thông tin sản phẩm</p>
                        <div
                            class="tw-border-2 tw-border-dashed tw-border-gray-300 tw-rounded-lg tw-p-4 tw-text-center">
                            <input type="file" ref="fileInput" accept=".xlsx,.xls" class="tw-hidden"
                                @change="handleFileUpload">
                            <button @click="$refs.fileInput.click()"
                                class="tw-w-full tw-bg-gray-100 tw-text-gray-700 tw-rounded tw-px-3 tw-py-2 tw-flex tw-items-center tw-justify-center tw-gap-2 hover:tw-bg-gray-200">
                                <i class="fa-solid fa-upload"></i>
                                Chọn file
                            </button>
                            <p v-if="selectedFile" class="tw-mt-2 tw-text-sm tw-text-gray-600">
                                Đã chọn: {{ selectedFile.name }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="tw-mt-6 tw-flex tw-justify-end tw-gap-2">
                    <button @click="showImportModal = false"
                        class="tw-px-4 tw-py-2 tw-border tw-rounded hover:tw-bg-gray-50">
                        Hủy
                    </button>
                    <button @click="handleImport" :disabled="!selectedFile || isLoading"
                        class="tw-px-4 tw-py-2 tw-bg-primary tw-text-white tw-rounded hover:tw-bg-primary-dark disabled:tw-opacity-50 tw-flex tw-items-center tw-justify-center tw-gap-2">
                        <i v-if="isLoading" class="fas fa-spinner fa-spin"></i>
                        <span>{{ isLoading ? 'Đang xử lý...' : 'Nhập dữ liệu' }}</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="tw-overflow-x-auto">
            <table class="tw-w-full tw-text-left tw-text-sm">
                <thead>
                    <tr class="tw-border-b tw-bg-gray-50">
                        <th class="tw-px-3 tw-py-2">
                            <input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll" />
                        </th>
                        <th class="tw-px-3 tw-py-2">#</th>
                        <th v-for="column in columns" :key="column.key" class="tw-px-3 tw-py-2 tw-font-semibold"
                            @click="sortBy(column.key)">
                            {{ column.label }}
                            <i v-if="sortKey === column.key"
                                :class="['fas', sortOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down']"></i>
                        </th>
                        <th class="tw-px-3 tw-py-2 tw-font-semibold">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Skeleton loading -->
                    <tr v-if="props.isLoading" v-for="n in props.itemsPerPage" :key="'skeleton-' + n">
                        <td v-for="i in columns.length + 3" :key="i" class="tw-px-3 tw-py-2">
                            <div class="skeleton-loader"></div>
                        </td>
                    </tr>
                    <tr v-else v-for="(item, index) in paginatedData" :key="index"
                        class="tw-border-b hover:tw-bg-gray-50">
                        <td class="tw-px-3 tw-py-2">
                            <input type="checkbox" :checked="selectedRows.includes(item.id)"
                                @change="toggleSelectRow(item.id)" />
                        </td>
                        <td class="tw-px-3 tw-py-2">
                            {{ (currentPage - 1) * props.itemsPerPage + index + 1 }}
                        </td>
                        <td v-for="column in columns" :key="column.key" class="tw-px-3 tw-py-2">
                            <template v-if="column.type === 'main_image'">
                                <img :src="getMainImage(item.images)?.image_path"
                                    :alt="getMainImage(item.images)?.image_path"
                                    class="tw-w-10 tw-h-10 tw-object-cover tw-rounded" />
                            </template>
                            <template v-else-if="column.type === 'sub_images'">
                                <div class="tw-flex tw-gap-1">
                                    <img v-for="image in getSubImages(item.images)" :key="image.id"
                                        :src="image.image_path" :alt="image.image_path"
                                        class="tw-w-6 tw-h-6 tw-object-cover tw-rounded tw-cursor-pointer hover:tw-opacity-75"
                                        @click="handleImageClick(image)" />
                                </div>
                            </template>
                            <template v-else-if="column.type === 'brand'">
                                <span class="tw-text-xs">{{ item[column.key] }}</span>
                            </template>
                            <template v-else-if="column.type === 'category'">
                                <span class="tw-text-xs">{{ item[column.key] }}</span>
                            </template>
                            <template v-else-if="column.type === 'status'">
                                <span :class="getStatusBadgeClass(item[column.key])">
                                    {{ getStatusText(item[column.key]) }}
                                </span>
                            </template>
                            <template v-else-if="column.type === 'price'">
                                {{ formatPrice(item[column.key]) }}
                            </template>
                            <template v-else-if="column.type === 'variants'">
                                <Badges :variants="item[column.key]" />
                            </template>
                            <template v-else>
                                {{ item[column.key] }}
                            </template>
                        </td>
                        <td class="tw-px-3 tw-py-2">
                            <div class="tw-flex tw-items-center tw-gap-2">
                                <NuxtLink :to="`/admin/products/${item.id}/edit`"
                                    class="tw-inline-flex tw-items-center tw-p-1.5 tw-text-blue-600 hover:tw-text-blue-900 hover:tw-bg-blue-50 tw-rounded-lg tw-transition-colors tw-duration-150"
                                    title="Chỉnh sửa sản phẩm">
                                    <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                </NuxtLink>
                                <button @click="$emit('delete', item)"
                                    class="tw-inline-flex tw-items-center tw-p-1.5 tw-text-red-600 hover:tw-text-red-900 hover:tw-bg-red-50 tw-rounded-lg tw-transition-colors tw-duration-150"
                                    title="Xóa sản phẩm">
                                    <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!props.isLoading && paginatedData.length === 0">
                        <td :colspan="columns.length + 3" class="tw-px-3 tw-py-2 tw-text-center tw-text-gray-500">
                            Không có dữ liệu
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="tw-flex tw-justify-between tw-items-center tw-mt-3">
            <div class="tw-text-xs tw-text-gray-600">
                Hiển thị {{ paginatedData.length }} trên tổng số {{ filteredData.length }} bản ghi
            </div>
            <div class="tw-flex tw-gap-1">
                <button :disabled="currentPage === 1" @click="currentPage--"
                    class="tw-px-2 tw-py-1 tw-border tw-rounded hover:tw-bg-gray-50 disabled:tw-opacity-50 tw-text-sm">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span class="tw-px-2 tw-py-1 tw-text-sm">
                    Trang {{ currentPage }} / {{ totalPages }}
                </span>
                <button :disabled="currentPage === totalPages" @click="currentPage++"
                    class="tw-px-2 tw-py-1 tw-border tw-rounded hover:tw-bg-gray-50 disabled:tw-opacity-50 tw-text-sm">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import Badges from './Badges.vue'
import { useProducts } from '~/composables/useProducts'
import Swal from 'sweetalert2'

const notyf = useNuxtApp().$notyf

const props = defineProps({
    data: {
        type: Array,
        required: true,
        default: () => []
    },
    columns: {
        type: Array,
        required: true,
        default: () => []
    },
    categories: {
        type: Array,
        default: () => []
    },
    brands: {
        type: Array,
        default: () => []
    },
    itemsPerPage: {
        type: Number,
        default: 10
    },
    isLoading: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['delete', 'filter-change', 'refresh'])

const searchQuery = ref('')
const selectedCategory = ref('')
const selectedBrand = ref('')
const selectedStatus = ref('')
const currentPage = ref(1)
const sortKey = ref('')
const sortOrder = ref('asc')

const showImportModal = ref(false)
const selectedFile = ref(null)
const fileInput = ref(null)
const isLoading = ref(false)
const { getTemplateSheet, importFile, getProducts, bulkDeleteProducts } = useProducts()

const selectedRows = ref([])

const filteredData = computed(() => {
    let result = [...props.data]

    if (searchQuery.value) {
        result = result.filter(item =>
            Object.values(item).some(val =>
                String(val).toLowerCase().includes(searchQuery.value.toLowerCase())
            )
        )
    }

    if (selectedCategory.value) {
        result = result.filter(item => item.category === selectedCategory.value)
    }

    if (selectedBrand.value) {
        result = result.filter(item => item.brand === selectedBrand.value)
    }

    if (selectedStatus.value) {
        result = result.filter(item => item.is_active === parseInt(selectedStatus.value))
    }

    if (sortKey.value) {
        result.sort((a, b) => {
            const aVal = a[sortKey.value]
            const bVal = b[sortKey.value]
            if (sortOrder.value === 'asc') {
                return aVal > bVal ? 1 : -1
            } else {
                return aVal < bVal ? 1 : -1
            }
        })
    }

    return result
})

const totalPages = computed(() =>
    Math.ceil(filteredData.value.length / props.itemsPerPage)
)

const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * props.itemsPerPage
    const end = start + props.itemsPerPage
    return filteredData.value.slice(start, end)
})

const handleSearch = () => {
    currentPage.value = 1
}

const sortBy = (key) => {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortKey.value = key
        sortOrder.value = 'asc'
    }
}

watch([selectedCategory, selectedBrand, selectedStatus], () => {
    currentPage.value = 1
    emit('filter-change', {
        category: selectedCategory.value,
        brand: selectedBrand.value,
        status: selectedStatus.value
    })
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}

const getStatusBadgeClass = (status) => {
    return status === 1
        ? 'tw-bg-green-100 tw-text-green-700 tw-px-2 tw-py-1 tw-rounded-full tw-text-xs'
        : 'tw-bg-red-100 tw-text-red-700 tw-px-2 tw-py-1 tw-rounded-full tw-text-xs'
}

const getStatusText = (status) => {
    return status === 1 ? 'Hoạt động' : 'Vô hiệu'
}

const handleImageClick = (image) => {
    console.log('Image clicked:', image)
}

const getMainImage = (images) => {
    return images?.find(img => img.is_main === 1)
}

const getSubImages = (images) => {
    return images?.filter(img => img.is_main === 0) || []
}

const handleDownloadTemplate = async () => {
    try {
        const response = await getTemplateSheet()
        const blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
        const url = window.URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = url
        link.download = 'product_template.xlsx'
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        window.URL.revokeObjectURL(url)
    } catch (error) {
        console.error('Error downloading template:', error)
    }
}

const handleFileUpload = (event) => {
    const file = event.target.files[0]
    if (file) {
        selectedFile.value = file
    }
}

const handleImport = async () => {
    if (!selectedFile.value) return

    try {
        isLoading.value = true
        const formData = new FormData()
        formData.append('file', selectedFile.value)

        await importFile(formData)
        selectedFile.value = null
        showImportModal.value = false
        notyf.success("Import sản phẩm thành công")
        await getProducts()
        emit('refresh')
    } catch (error) {
        console.error('Error importing file:', error)
    } finally {
        isLoading.value = false
    }
}

const isAllSelected = computed(() => {
    return paginatedData.value.length > 0 && paginatedData.value.every(item => selectedRows.value.includes(item.id))
})

const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selectedRows.value = []
    } else {
        selectedRows.value = paginatedData.value.map(item => item.id)
    }
}

const toggleSelectRow = (id) => {
    if (selectedRows.value.includes(id)) {
        selectedRows.value = selectedRows.value.filter(rowId => rowId !== id)
    } else {
        selectedRows.value.push(id)
    }
}

const handleBulkDelete = async () => {
    if (selectedRows.value.length === 0) return
    const result = await Swal.fire({
        title: 'Bạn có chắc muốn xoá các sản phẩm đã chọn?',
        text: 'Hành động này không thể hoàn tác!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Xoá',
        cancelButtonText: 'Huỷ'
    })
    if (!result.isConfirmed) return
    try {
        await bulkDeleteProducts(selectedRows.value)
        notyf.success('Xoá sản phẩm thành công')
        selectedRows.value = []
        await getProducts()
        emit('refresh')
    } catch (e) {
        notyf.error('Xoá sản phẩm thất bại')
    }
}
</script>

<style scoped>
.tw-bg-primary {
    background-color: #3bb77e;
}

.tw-bg-primary-dark {
    background-color: #2ea16d;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

.fa-spinner {
    animation: spin 1s linear infinite;
}

.skeleton-loader {
    height: 20px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 37%, #f0f0f0 63%);
    border-radius: 4px;
    animation: skeleton-loading 2.2s infinite;
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