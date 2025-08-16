<template>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex justify-end mb-4" v-if="brands.length > 0">
            <button v-if="selectedBrands.size > 0" @click="$emit('bulkDelete', selectedBrands)"
                class="bg-red-500 text-white rounded px-3 py-1 hover:bg-red-600 flex items-center gap-2 text-sm">
                <i class="fas fa-trash"></i>
                <span class="hidden sm:inline">Xóa {{ selectedBrands.size }} mục đã chọn</span>
                <span class="sm:hidden">Xóa {{ selectedBrands.size }}</span>
            </button>
        </div>

        <!-- Table Section -->
        <div class="table-container">
            <!-- Desktop Table -->
            <div class="desktop-table">
                <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white">
                    <table class="w-full text-sm min-w-[800px]">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="px-3 py-2 text-center">
                                    <div class="flex items-center justify-center">
                                        <input type="checkbox" :checked="selectedBrands.size === paginatedBrands.length"
                                            @change="toggleSelectAll" class="rounded">
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-center">#</th>
                                <th class="px-6 py-3 text-center">Logo</th>
                                <th class="px-6 py-3 text-center">Tên thương hiệu</th>
                                <th class="px-6 py-3 text-center">Mô tả</th>
                                <th class="px-6 py-3 text-center">Danh mục cha</th>
                                <th class="px-6 py-3 text-center">Số lượng sản phẩm</th>
                                <th class="px-6 py-3 text-center">Trạng thái</th>
                                <th class="px-6 py-3 text-center">Thao tác</th>
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
                                        <div class="flex justify-center">
                                            <input type="checkbox" :checked="selectedBrands.has(brand.id)"
                                                @change="toggleSelect(brand.id)" class="rounded">
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">{{ (currentPage - 1) * itemsPerPage + index + 1 }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex justify-center">
                                            <img :src="brand.image" :alt="brand.name"
                                                class="w-10 h-10 object-cover rounded">
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">{{ brand.name }}</td>
                                    <td class="px-4 py-3 text-center max-w-xs truncate" :title="brand.description">{{
                                        brand.description }}</td>
                                    <td class="px-4 py-3 text-center">{{ getParentBrandName(brand) }}</td>
                                    <td class="px-4 py-3 text-center">{{ brand.products_count }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex justify-center">
                                            <span :class="getStatusBadgeClass(Number(brand.is_active))">
                                                {{ getStatusText(Number(brand.is_active)) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-1 sm:gap-2">
                                            <router-link :to="`/admin/brands/edit/${brand.id}`"
                                                class="inline-flex items-center p-1.5 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-colors duration-150"
                                                title="Chỉnh sửa thương hiệu">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                    </path>
                                                </svg>
                                            </router-link>
                                            <button @click="handleDelete(brand)"
                                                class="inline-flex items-center p-1.5 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors duration-150"
                                                title="Xóa thương hiệu">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
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
            </div>

            <!-- Mobile Cards -->
            <div class="mobile-cards">
                <div v-if="props.isLoading" class="mobile-skeleton">
                    <div v-for="n in props.itemsPerPage" :key="'mobile-skeleton-' + n" class="mobile-card skeleton">
                        <div class="skeleton-loader"></div>
                    </div>
                </div>
                <div v-else v-for="(brand, index) in paginatedBrands" :key="'mobile-' + brand.id" class="mobile-card">
                    <div class="card-header">
                        <div class="card-checkbox">
                            <input type="checkbox" :checked="selectedBrands.has(brand.id)"
                                @change="toggleSelect(brand.id)" />
                        </div>
                        <div class="card-number">{{ (currentPage - 1) * itemsPerPage + index + 1 }}</div>
                    </div>

                    <div class="card-content">
                        <div class="brand-image">
                            <img :src="brand.image" :alt="brand.name" class="w-16 h-16 object-cover rounded" />
                        </div>

                        <div class="brand-info">
                            <h3 class="brand-name">{{ brand.name }}</h3>
                            <div class="brand-details">
                                <span class="detail-item">
                                    <i class="fas fa-info-circle text-gray-400"></i>
                                    {{ brand.description ? brand.description.substring(0, 50) + '...' : 'Không có mô tả'
                                    }}
                                </span>
                                <span class="detail-item">
                                    <i class="fas fa-sitemap text-gray-400"></i>
                                    {{ getParentBrandName(brand) }}
                                </span>
                                <span class="detail-item">
                                    <i class="fas fa-box text-gray-400"></i>
                                    {{ brand.products_count }} sản phẩm
                                </span>
                            </div>
                        </div>

                        <div class="brand-status">
                            <span :class="getStatusBadgeClass(Number(brand.is_active))">
                                {{ getStatusText(Number(brand.is_active)) }}
                            </span>
                        </div>
                    </div>

                    <div class="card-actions">
                        <router-link :to="`/admin/brands/edit/${brand.id}`" class="mobile-action-btn edit-btn">
                            <i class="fas fa-edit"></i>
                            <span>Sửa</span>
                        </router-link>
                        <button @click="handleDelete(brand)" class="mobile-action-btn delete-btn">
                            <i class="fas fa-trash"></i>
                            <span>Xóa</span>
                        </button>
                    </div>
                </div>

                <div v-if="!props.isLoading && paginatedBrands.length === 0" class="empty-state">
                    <i class="fas fa-trademark text-gray-400 text-4xl mb-2"></i>
                    <p class="text-gray-500">Không có dữ liệu</p>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="!props.isLoading && totalPages > 1"
            class="flex flex-col sm:flex-row justify-between items-center mt-6 gap-4">
            <div class="text-sm text-gray-600 text-center sm:text-left">
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
import Swal from 'sweetalert2'
import { push } from 'notivue'

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

const getStatusBadgeClass = (isActive) => {
    return isActive === 1 ? 'status-badge active' : 'status-badge inactive'
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

const emit = defineEmits(['update:currentPage', 'delete', 'bulkDelete', 'refresh'])

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        emit('update:currentPage', page)
    }
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

/* Table Container */
.table-container {
    margin-bottom: 1.5rem;
}

/* Desktop Table */
.desktop-table {
    display: block;
}

/* Mobile Cards */
.mobile-cards {
    display: none;
}

@media (max-width: 1023px) {
    .desktop-table {
        display: none;
    }

    .mobile-cards {
        display: block;
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

.brand-image {
    flex-shrink: 0;
}

.brand-info {
    flex: 1;
}

.brand-name {
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.5rem;
    font-size: 1rem;
    line-height: 1.4;
}

.brand-details {
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

.brand-status {
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

/* Mobile Skeleton */
.mobile-skeleton .mobile-card {
    background: #f9fafb;
}

.mobile-skeleton .mobile-card .skeleton-loader {
    height: 60px;
    margin-bottom: 0.5rem;
}

/* Responsive table styles */
.table-container {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    /* Smooth scrolling on iOS */
}

/* Mobile-specific improvements */
@media (max-width: 768px) {
    .table-container {
        border-radius: 0.5rem;
    }

    table {
        font-size: 0.875rem;
    }

    th,
    td {
        padding: 0.5rem 0.25rem;
    }

    .max-w-xs {
        max-width: 8rem;
    }
}

@media (max-width: 640px) {

    th,
    td {
        padding: 0.375rem 0.125rem;
    }

    .max-w-xs {
        max-width: 6rem;
    }
}

/* Touch-friendly improvements */
@media (hover: none) and (pointer: coarse) {

    button,
    a {
        min-height: 44px;
        /* iOS recommended touch target size */
    }

    input[type="checkbox"] {
        min-width: 20px;
        min-height: 20px;
        width: 20px;
        height: 20px;
    }

    .action-buttons button {
        padding: 0.75rem;
    }
}

/* Pagination responsive */
@media (max-width: 480px) {
    .pagination-info {
        font-size: 0.875rem;
    }

    .pagination-controls {
        gap: 0.5rem;
    }

    .pagination-controls button {
        padding: 0.5rem 0.75rem;
    }
}

/* Utility Classes */
.bg-primary {
    background-color: #3bb77e;
}

.bg-primary-dark {
    background-color: #2ea16d;
}
</style>