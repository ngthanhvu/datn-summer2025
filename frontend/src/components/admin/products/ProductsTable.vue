<template>
    <div class="bg-white rounded-lg shadow p-4">
        <!-- Filters Section -->
        <div class="filters-section">
            <!-- Search and Filters Row -->
            <div class="filters-row">
                <div class="search-container">
                    <div class="relative">
                        <input type="text" v-model="searchQuery" placeholder="Tìm kiếm..." @input="handleSearch"
                            class="search-input">
                    </div>
                </div>

                <div class="filters-container">
                    <select v-model="selectedCategory" class="filter-select border border-gray-300">
                        <option value="">Tất cả danh mục</option>
                        <option v-if="categories.length === 0" value="" disabled>Đang tải...</option>
                        <option v-for="category in categories" :key="category.value" :value="category.value">
                            {{ category.label }}
                        </option>
                    </select>

                    <select v-model="selectedBrand" class="filter-select border border-gray-300">
                        <option value="">Tất cả thương hiệu</option>
                        <option v-if="brands.length === 0" value="" disabled>Đang tải...</option>
                        <option v-for="brand in brands" :key="brand.value" :value="brand.value">
                            {{ brand.label }}
                        </option>
                    </select>

                    <select v-model="selectedStatus" class="filter-select border border-gray-300">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1">Hoạt động</option>
                        <option value="0">Vô hiệu</option>
                    </select>
                </div>
            </div>

            <!-- Actions Row -->
            <div class="actions-row">
                <div class="bulk-actions">
                    <button v-if="selectedRows.length > 0" @click="handleBulkDelete" class="bulk-delete-btn">
                        <i class="fas fa-trash"></i>
                        <span class="hidden sm:inline">Xoá đã chọn</span>
                    </button>
                </div>

                <div class="action-buttons">
                    <button @click="$emit('refresh')" class="action-btn refresh-btn">
                        <i class="fas fa-sync-alt"></i>
                        <span class="hidden sm:inline">Tải lại</span>
                    </button>
                    <button @click="showImportModal = true" class="action-btn import-btn">
                        <i class="fa-solid fa-file-import"></i>
                        <span class="hidden sm:inline">Nhập excel</span>
                    </button>
                    <router-link to="/admin/products/create" class="action-btn add-btn">
                        <i class="fas fa-plus"></i>
                        <span class="hidden sm:inline">Thêm mới</span>
                    </router-link>
                </div>
            </div>
        </div>

        <!-- Import Modal -->
        <div v-if="showImportModal" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Nhập sản phẩm từ Excel</h3>
                    <button @click="showImportModal = false" class="modal-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="template-section">
                        <h4 class="section-title">Tải file mẫu</h4>
                        <p class="section-description">Tải file mẫu Excel để nhập dữ liệu sản phẩm</p>
                        <button @click="handleDownloadTemplate" class="template-btn">
                            <i class="fa-solid fa-download"></i>
                            Tải file mẫu
                        </button>
                    </div>

                    <div class="upload-section">
                        <h4 class="section-title">Tải lên file Excel</h4>
                        <p class="section-description">Chọn file Excel đã điền thông tin sản phẩm</p>
                        <div class="upload-area">
                            <input type="file" ref="fileInput" accept=".xlsx,.xls" class="hidden"
                                @change="handleFileUpload">
                            <button @click="$refs.fileInput.click()" class="upload-btn">
                                <i class="fa-solid fa-upload"></i>
                                Chọn file
                            </button>
                            <p v-if="selectedFile" class="selected-file">
                                Đã chọn: {{ selectedFile.name }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button @click="showImportModal = false" class="cancel-btn">
                        Hủy
                    </button>
                    <button @click="handleImport" :disabled="!selectedFile || isLoading" class="import-submit-btn">
                        <i v-if="isLoading" class="fas fa-spinner fa-spin"></i>
                        <span>{{ isLoading ? 'Đang xử lý...' : 'Nhập dữ liệu' }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="table-container">
            <!-- Desktop Table -->
            <div class="desktop-table">
                <div class="overflow-x-auto overflow-hidden rounded-2xl border border-gray-200 bg-white">
                    <table class="w-full text-center text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="px-6 py-3">
                                    <input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll" />
                                </th>
                                <th class="px-3 py-2">#</th>
                                <th v-for="column in columns" :key="column.key" class="px-3 py-2 font-semibold"
                                    @click="sortBy(column.key)">
                                    {{ column.label }}
                                    <i v-if="sortKey === column.key"
                                        :class="['fas', sortOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down']"></i>
                                </th>
                                <th class="px-3 py-2 font-semibold text-left">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Skeleton loading -->
                            <tr v-if="props.isLoading" v-for="n in props.itemsPerPage" :key="'skeleton-' + n">
                                <td v-for="i in columns.length + 3" :key="i" class="px-6 py-3">
                                    <div class="skeleton-loader"></div>
                                </td>
                            </tr>
                            <tr v-else v-for="(item, index) in filteredData" :key="index"
                                class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="px-3 py-2">
                                    <input type="checkbox" :checked="selectedRows.includes(item.id)"
                                        @change="toggleSelectRow(item.id)" />
                                </td>
                                <td class="px-3 py-2">
                                    {{ (currentPageLocal - 1) * props.itemsPerPage + index + 1 }}
                                </td>
                                <td v-for="column in columns" :key="column.key" class="px-3 py-2 text-center">
                                    <template v-if="column.type === 'main_image'">
                                        <img :src="getMainImage(item.images)?.image_path"
                                            :alt="getMainImage(item.images)?.image_path"
                                            class="w-10 h-10 object-cover rounded" />
                                    </template>
                                    <template v-else-if="column.type === 'sub_images'">
                                        <div class="flex items-center gap-1">
                                            <!-- Chỉ lặp tối đa 3 ảnh -->
                                            <img v-for="(image, index) in getSubImages(item.images).slice(0, 3)"
                                                :key="image.id" :src="image.image_path" :alt="image.image_path"
                                                class="w-6 h-6 object-cover rounded cursor-pointer hover:opacity-75"
                                                @click="handleImageClick(image)" />

                                            <!-- Nếu còn ảnh dư thì hiện dấu ba chấm -->
                                            <button v-if="getSubImages(item.images).length > 3"
                                                class="p-1 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100 cursor-pointer">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </button>
                                        </div>
                                    </template>
                                    <template v-else-if="column.type === 'brand'">
                                        <span class="text-xs">{{ item[column.key] }}</span>
                                    </template>
                                    <template v-else-if="column.type === 'category'">
                                        <span class="text-xs">{{ item[column.key] }}</span>
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
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        <router-link :to="`/admin/products/edit/${item.id}`"
                                            class="action-link edit-link" title="Chỉnh sửa sản phẩm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </router-link>
                                        <button @click="$emit('delete', item)" class="action-link delete-link"
                                            title="Xóa sản phẩm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!props.isLoading && filteredData.length === 0">
                                <td :colspan="columns.length + 3" class="px-3 py-2 text-center text-gray-500">
                                    Không có dữ liệu
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile Cards -->
            <div class="mobile-cards">
                <div v-if="props.isLoading" class="mobile-skeleton">
                    <div v-for="n in props.itemsPerPage" :key="'mobile-skeleton-' + n" class="mobile-card skeleton">
                        <div class="skeleton-loader"></div>
                    </div>
                </div>
                <div v-else v-for="(item, index) in filteredData" :key="'mobile-' + index" class="mobile-card">
                    <div class="card-header">
                        <div class="card-checkbox">
                            <input type="checkbox" :checked="selectedRows.includes(item.id)"
                                @change="toggleSelectRow(item.id)" />
                        </div>
                        <div class="card-number">{{ (currentPageLocal - 1) * props.itemsPerPage + index + 1 }}</div>
                    </div>

                    <div class="card-content">
                        <div class="product-image">
                            <img :src="getMainImage(item.images)?.image_path"
                                :alt="getMainImage(item.images)?.image_path" class="w-16 h-16 object-cover rounded" />
                        </div>

                        <div class="product-info">
                            <h3 class="product-name">{{ item.name }}</h3>
                            <div class="product-details">
                                <span class="detail-item">
                                    <i class="fas fa-tag text-gray-400"></i>
                                    {{ item.category }}
                                </span>
                                <span class="detail-item">
                                    <i class="fas fa-copyright text-gray-400"></i>
                                    {{ item.brand }}
                                </span>
                                <span class="detail-item">
                                    <i class="fas fa-dollar-sign text-gray-400"></i>
                                    {{ formatPrice(item.price) }}
                                </span>
                            </div>
                        </div>

                        <div class="product-status">
                            <span :class="getStatusBadgeClass(item.is_active)">
                                {{ getStatusText(item.is_active) }}
                            </span>
                        </div>
                    </div>

                    <div class="card-actions">
                        <router-link :to="`/admin/products/edit/${item.id}`" class="mobile-action-btn edit-btn">
                            <i class="fas fa-edit"></i>
                            <span>Sửa</span>
                        </router-link>
                        <button @click="$emit('delete', item)" class="mobile-action-btn delete-btn">
                            <i class="fas fa-trash"></i>
                            <span>Xóa</span>
                        </button>
                    </div>
                </div>

                <div v-if="!props.isLoading && filteredData.length === 0" class="empty-state">
                    <i class="fas fa-box-open text-gray-400 text-4xl mb-2"></i>
                    <p class="text-gray-500">Không có dữ liệu</p>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="pagination-section">
            <div class="pagination-info">
                Hiển thị {{ paginationInfo.from || 0 }} - {{ paginationInfo.to || 0 }} trên tổng số {{
                    paginationInfo.total || 0
                }} bản ghi
            </div>
            <div class="pagination-controls">
                <button :disabled="currentPage === 1" @click="handlePageChange(currentPage - 1)" class="pagination-btn">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span class="pagination-text">
                    Trang {{ currentPage }} / {{ totalPages }}
                </span>
                <button :disabled="currentPage === totalPages" @click="handlePageChange(currentPage + 1)"
                    class="pagination-btn">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import Badges from './Badges.vue'
import { useProducts } from '../../../composable/useProducts'
import { push } from 'notivue'
import Swal from 'sweetalert2'

const { getTemplateSheet, importFile, getProducts, bulkDeleteProducts } = useProducts()

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
    },
    pagination: {
        type: Object,
        default: () => ({
            current_page: 1,
            last_page: 1,
            per_page: 10,
            total: 0,
            from: null,
            to: null
        })
    },
    currentPage: {
        type: Number,
        default: 1
    }
})

const emit = defineEmits(['delete', 'filter-change', 'refresh', 'page-change'])

const searchQuery = ref('')
const selectedCategory = ref('')
const selectedBrand = ref('')
const selectedStatus = ref('')
const sortKey = ref('')
const sortOrder = ref('asc')

const showImportModal = ref(false)
const selectedFile = ref(null)
const fileInput = ref(null)
const isLoading = ref(false)

const selectedRows = ref([])

// Computed properties for pagination từ server
const totalPages = computed(() => props.pagination.last_page || 1)
const paginationInfo = computed(() => props.pagination)

// Computed properties cho dữ liệu đã lọc (chỉ lọc local, không phân trang)
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

const handleSearch = () => {
    // Search được xử lý bởi watch searchQuery
}

const sortBy = (key) => {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortKey.value = key
        sortOrder.value = 'asc'
    }
}

const handlePageChange = (page) => {
    emit('page-change', page)
}

// Lọc chỉ hoạt động local, không gọi API
// Khi filter thay đổi, chỉ cần reset page local về 1
const currentPageLocal = ref(1)

watch([selectedCategory, selectedBrand, selectedStatus, searchQuery], () => {
    // Reset page local về 1 khi filter thay đổi
    currentPageLocal.value = 1
})

// Cập nhật currentPageLocal khi currentPage từ server thay đổi
watch(() => props.currentPage, (newPage) => {
    currentPageLocal.value = newPage
}, { immediate: true })

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}

const getStatusBadgeClass = (status) => {
    return status === 1
        ? 'status-badge active'
        : 'status-badge inactive'
}

const getStatusText = (status) => {
    return status === 1 ? 'Hoạt động' : 'Vô hiệu'
}

const handleImageClick = (image) => {
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
        push.success("Import sản phẩm thành công")
        emit('refresh')
    } catch (error) {
        console.error('Error importing file:', error)
    } finally {
        isLoading.value = false
    }
}

const isAllSelected = computed(() => {
    return filteredData.value.length > 0 && filteredData.value.every(item => selectedRows.value.includes(item.id))
})

const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selectedRows.value = []
    } else {
        selectedRows.value = filteredData.value.map(item => item.id)
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
        push.success('Xoá sản phẩm thành công')
        selectedRows.value = []
        emit('refresh')
    } catch (e) {
        push.error('Xoá sản phẩm thất bại')
    }
}
</script>

<style scoped>
/* Filters Section */
.filters-section {
    margin-bottom: 1.5rem;
}

.filters-row {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 1rem;
}

@media (min-width: 768px) {
    .filters-row {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }
}

.search-container {
    flex: 1;
}

.search-input {
    width: 100%;
    padding: 0.5rem 2.5rem 0.5rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    background: #f9fafb;
}

.search-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

.filters-container {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

@media (min-width: 768px) {
    .filters-container {
        flex-direction: row;
        gap: 0.75rem;
    }
}

.filter-select {
    padding: 0.5rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    background: white;
    min-width: 120px;
}

@media (min-width: 768px) {
    .filter-select {
        min-width: 150px;
    }
}

.actions-row {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
}

@media (min-width: 768px) {
    .actions-row {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
}

.bulk-actions {
    display: flex;
    justify-content: flex-start;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

@media (max-width: 640px) {
    .action-buttons {
        gap: 0.25rem;
    }

    .action-btn {
        padding: 0.5rem 0.75rem;
        font-size: 0.8rem;
    }
}

.bulk-delete-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background-color: #dc2626;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    cursor: pointer;
    transition: background-color 0.2s;
}

.bulk-delete-btn:hover {
    background-color: #b91c1c;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}

.refresh-btn {
    background-color: #364153;
    color: white;
}

.refresh-btn:hover {
    background-color: #4338ca;
}

.refresh-btn i {
    transition: transform 0.3s ease;
}

.refresh-btn:active i {
    transform: rotate(180deg);
}

.import-btn {
    background-color: #3bb77e;
    color: white;
}

.import-btn:hover {
    background-color: #2ea16d;
}

.add-btn {
    background-color: #3bb77e;
    color: white;
}

.add-btn:hover {
    background-color: #2ea16d;
}

/* Modal Responsive */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
}

.modal-content {
    background: white;
    border-radius: 0.75rem;
    width: 100%;
    max-width: 28rem;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.modal-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.25rem;
    color: #6b7280;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 0.25rem;
    transition: color 0.2s;
}

.modal-close:hover {
    color: #374151;
}

.modal-body {
    padding: 1.5rem;
}

.template-section,
.upload-section {
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1rem;
}

.section-title {
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: #111827;
}

.section-description {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 0.75rem;
}

.template-btn,
.upload-btn {
    width: 100%;
    padding: 0.75rem;
    background-color: #f3f4f6;
    color: #374151;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    cursor: pointer;
    transition: background-color 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.template-btn:hover,
.upload-btn:hover {
    background-color: #e5e7eb;
}

.upload-area {
    border: 2px dashed #d1d5db;
    border-radius: 0.5rem;
    padding: 1rem;
    text-align: center;
}

.selected-file {
    margin-top: 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

@media (max-width: 640px) {
    .modal-footer {
        flex-direction: column;
    }

    .modal-footer button {
        width: 100%;
    }
}

.cancel-btn {
    padding: 0.5rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    background: white;
    color: #374151;
    font-size: 0.875rem;
    cursor: pointer;
    transition: background-color 0.2s;
}

.cancel-btn:hover {
    background-color: #f9fafb;
}

.import-submit-btn {
    padding: 0.5rem 1rem;
    background-color: #3bb77e;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    cursor: pointer;
    transition: background-color 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.import-submit-btn:hover:not(:disabled) {
    background-color: #2ea16d;
}

.import-submit-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Table Styles */
.table-container {
    margin-bottom: 1.5rem;
}

.desktop-table {
    display: none;
}

@media (min-width: 1024px) {
    .desktop-table {
        display: block;
    }
}

.mobile-cards {
    display: block;
}

@media (min-width: 1024px) {
    .mobile-cards {
        display: none;
    }
}

.mobile-card {
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 1rem;
    margin-bottom: 1rem;
    background: white;
    transition: box-shadow 0.2s;
}

.mobile-card:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.card-checkbox {
    display: flex;
    align-items: center;
}

.card-number {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
}

.card-content {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.product-image {
    flex-shrink: 0;
}

.product-info {
    flex: 1;
}

.product-name {
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.5rem;
    font-size: 1rem;
    line-height: 1.4;
}

.product-details {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.detail-item i {
    width: 1rem;
}

.product-status {
    flex-shrink: 0;
}

/* Status Badge Styles */
.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
    text-align: center;
    display: inline-block;
    min-width: 80px;
}

.status-badge.active {
    background-color: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.status-badge.inactive {
    background-color: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

/* Remove old toggle styles */
.status-toggle {
    display: none;
}

.toggle-slider {
    display: none;
}

.card-actions {
    display: flex;
    gap: 0.5rem;
}

.mobile-action-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}

.edit-btn {
    background-color: #dbeafe;
    color: #1d4ed8;
}

.edit-btn:hover {
    background-color: #bfdbfe;
}

.delete-btn {
    background-color: #fee2e2;
    color: #dc2626;
}

.delete-btn:hover {
    background-color: #fecaca;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6b7280;
}

/* Desktop Table Styles */
.action-link {
    display: inline-flex;
    align-items: center;
    padding: 0.375rem;
    border-radius: 0.5rem;
    transition: all 0.15s;
    text-decoration: none;
}

.edit-link {
    color: #2563eb;
}

.edit-link:hover {
    color: #1d4ed8;
    background-color: #dbeafe;
}

.delete-link {
    color: #dc2626;
}

.delete-link:hover {
    color: #b91c1c;
    background-color: #fee2e2;
}

/* Pagination */
.pagination-section {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    align-items: center;
}

@media (min-width: 768px) {
    .pagination-section {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
}

.pagination-info {
    font-size: 0.875rem;
    color: #6b7280;
    text-align: center;
}

@media (min-width: 768px) {
    .pagination-info {
        text-align: left;
    }
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.pagination-btn {
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    background: white;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pagination-btn:hover:not(:disabled) {
    background-color: #f9fafb;
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination-text {
    font-size: 0.875rem;
    color: #374151;
    padding: 0 0.5rem;
}

/* Skeleton Loading */
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

.mobile-skeleton .mobile-card {
    background: #f9fafb;
}

.mobile-skeleton .mobile-card .skeleton-loader {
    height: 60px;
    margin-bottom: 0.5rem;
}

/* Utility Classes */
.bg-primary {
    background-color: #3bb77e;
}

.bg-primary-dark {
    background-color: #2ea16d;
}

/* Table Container Responsive */
.table-container {
    margin-bottom: 1.5rem;
}

.overflow-x-auto {
    overflow-x: auto;
}

@media (max-width: 1023px) {
    .overflow-x-auto {
        overflow-x: hidden;
    }

    table {
        min-width: 800px;
    }
}

/* Pagination Responsive */
.pagination-section {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    align-items: center;
}

@media (min-width: 768px) {
    .pagination-section {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
}

.pagination-info {
    font-size: 0.875rem;
    color: #6b7280;
    text-align: center;
}

@media (min-width: 768px) {
    .pagination-info {
        text-align: left;
    }
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.pagination-btn {
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    background: white;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pagination-btn:hover:not(:disabled) {
    background-color: #f9fafb;
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination-text {
    font-size: 0.875rem;
    color: #374151;
    padding: 0 0.5rem;
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
</style>