<template>
    <div class="bg-white rounded-lg shadow p-4 sm:p-6 text-sm">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-4 sm:mb-6">
            <!-- Filters - Stack on mobile -->
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 flex-1">
                <div class="relative">
                    <input type="text" v-model="searchQuery" placeholder="Tìm kiếm..." @input="handleSearch"
                        class="border border-gray-300 rounded px-3 sm:px-4 py-2 pl-9 sm:pl-10 w-full sm:w-64 text-xs sm:text-sm focus:outline-none focus:border-primary">
                    <i
                        class="fas fa-search absolute left-2 sm:left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs sm:text-sm"></i>
                </div>
                <div class="flex gap-3 sm:gap-4">
                    <div class="relative flex-1 sm:flex-none">
                        <select v-model="selectedType"
                            class="border border-gray-300 rounded px-3 sm:px-4 py-2 w-full sm:w-56 text-xs sm:text-sm focus:outline-none focus:border-primary appearance-none">
                            <option value="">Tất cả loại trang</option>
                            <option value="policy">Chính sách</option>
                            <option value="support">Hỗ trợ</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>
                    <div class="relative flex-1 sm:flex-none">
                        <select v-model="selectedStatus"
                            class="border border-gray-300 rounded px-3 sm:px-4 py-2 w-full sm:w-56 text-xs sm:text-sm focus:outline-none focus:border-primary appearance-none">
                            <option value="">Tất cả trạng thái</option>
                            <option value="1">Hoạt động</option>
                            <option value="0">Vô hiệu</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Add button -->
            <button @click="$router.push('/admin/pages/create')"
                class="bg-primary text-white rounded px-3 sm:px-4 py-2 flex items-center justify-center gap-2 hover:bg-primary-dark transition-colors cursor-pointer text-xs sm:text-sm">
                <i class="fas fa-plus text-xs sm:text-sm"></i>
                Thêm mới
            </button>
        </div>
        <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ error }}
        </div>

        <div v-else>
            <!-- Mobile Card Layout (hidden on desktop) -->
            <div class="block sm:hidden">
                <!-- Loading Cards -->
                <div v-if="props.isLoading" class="space-y-4">
                    <div v-for="n in 5" :key="n" class="bg-white border border-gray-200 rounded-lg p-4">
                        <div class="animate-pulse">
                            <div class="flex justify-between items-start mb-3">
                                <div class="bg-gray-200 h-5 rounded w-32"></div>
                                <div class="bg-gray-200 h-6 rounded w-16"></div>
                            </div>
                            <div class="bg-gray-200 h-4 rounded w-24 mb-2"></div>
                            <div class="bg-gray-200 h-4 rounded w-20 mb-3"></div>
                            <div class="flex justify-between items-center">
                                <div class="bg-gray-200 h-4 rounded w-28"></div>
                                <div class="bg-gray-200 h-8 rounded w-20"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else-if="paginatedData.length === 0" class="text-center py-8">
                    <i class="fas fa-file-alt text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600">Không có trang nào</p>
                </div>

                <!-- Page Cards -->
                <div v-else class="space-y-4">
                    <div v-for="(item, index) in paginatedData" :key="index"
                        class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">

                        <!-- Header: Title and Status -->
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-medium text-gray-900 text-sm leading-tight mb-1 truncate">
                                    {{ item.title }}
                                </h3>
                                <div class="text-xs text-gray-500 mb-2">
                                    <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded">
                                        {{ item.slug }}
                                    </span>
                                </div>
                            </div>
                            <span :class="getStatusBadgeClass(item.status)">
                                {{ getStatusText(item.status) }}
                            </span>
                        </div>

                        <!-- Meta Info -->
                        <div class="grid grid-cols-2 gap-4 text-xs text-gray-500 mb-3">
                            <div>
                                <span class="font-medium">Loại:</span>
                                <span :class="[
                                    'ml-1 px-2 py-1 rounded text-xs',
                                    getTypeBadgeClass(item.type)
                                ]">
                                    {{ getTypeLabel(item.type) }}
                                </span>
                            </div>
                            <div>
                                <span class="font-medium">Thứ tự:</span>
                                <span class="ml-1">{{ item.sort_order }}</span>
                            </div>
                            <div class="col-span-2">
                                <span class="font-medium">Ngày tạo:</span>
                                <span class="ml-1">{{ formatDate(item.created_at) }}</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2 pt-3 border-t border-gray-200">
                            <button @click="$router.push(`/admin/pages/${item.id}/edit`)"
                                class="flex-1 inline-flex items-center justify-center px-3 py-2 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-colors duration-150 text-xs">
                                <i class="fas fa-edit mr-1"></i>
                                Sửa
                            </button>
                            <button @click="viewPage(item)"
                                class="flex-1 inline-flex items-center justify-center px-3 py-2 text-green-600 hover:text-green-900 hover:bg-green-50 rounded-lg transition-colors duration-150 text-xs">
                                <i class="fas fa-eye mr-1"></i>
                                Xem
                            </button>
                            <button @click="handleDelete(item)"
                                class="flex-1 inline-flex items-center justify-center px-3 py-2 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors duration-150 text-xs">
                                <i class="fas fa-trash mr-1"></i>
                                Xóa
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Desktop Table Layout (hidden on mobile) -->
            <div class="hidden sm:block overflow-x-auto overflow-hidden rounded-2xl border border-gray-200 bg-white">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-gray-300">
                            <th v-for="column in columns" :key="column.key"
                                class="px-4 py-3 font-semibold cursor-pointer hover:bg-gray-100"
                                @click="sortBy(column.key)">
                                <div class="flex items-center gap-2">
                                    {{ column.label }}
                                    <i v-if="sortKey === column.key"
                                        :class="['fas', sortOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down', 'text-primary']"></i>
                                </div>
                            </th>
                            <th class="px-4 py-3 font-semibold">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Skeleton loading -->
                        <tr v-if="props.isLoading" v-for="n in 13" :key="'skeleton-' + n">
                            <td v-for="i in 8" :key="i" class="px-4 py-3">
                                <div class="skeleton-loader"></div>
                            </td>
                        </tr>
                        <tr v-else v-for="(item, index) in paginatedData" :key="index"
                            class="border-b border-gray-300 hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-600">
                                {{ index + 1 }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ item.title }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-sm">
                                    {{ item.slug }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="[
                                    'px-2 py-1 rounded text-sm',
                                    getTypeBadgeClass(item.type)
                                ]">
                                    {{ getTypeLabel(item.type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                {{ item.sort_order }}
                            </td>
                            <td class="px-4 py-3">
                                {{ formatDate(item.created_at) }}
                            </td>
                            <td class="px-4 py-3">
                                <span :class="getStatusBadgeClass(item.status)">
                                    {{ getStatusText(item.status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <button @click="$router.push(`/admin/pages/${item.id}/edit`)"
                                        class="inline-flex items-center p-1.5 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-colors duration-150"
                                        title="Chỉnh sửa trang">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                    </button>
                                    <button @click="handleDelete(item)"
                                        class="inline-flex items-center p-1.5 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors duration-150"
                                        title="Xóa trang">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!props.isLoading && !paginatedData.length">
                            <td colspan="8" class="px-4 py-3 text-center text-gray-600">
                                Không có dữ liệu
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="!loading && !error"
            class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mt-4 sm:mt-6 pt-4 border-t border-gray-200">
            <div class="text-xs sm:text-sm text-gray-600 text-center sm:text-left">
                Hiển thị {{ paginatedData.length }} trên tổng số {{ filteredData.length }} bản ghi
            </div>
            <div class="flex gap-2 justify-center">
                <button :disabled="currentPage === 1" @click="currentPage--"
                    class="px-2 sm:px-3 py-1 border border-gray-400 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-xs sm:text-sm">
                    <i class="fas fa-chevron-left mr-1"></i>
                    <span class="hidden sm:inline">Trước</span>
                </button>
                <span class="px-2 sm:px-3 py-1 text-xs sm:text-sm">
                    Trang {{ currentPage }} / {{ totalPages }}
                </span>
                <button :disabled="currentPage === totalPages" @click="currentPage++"
                    class="px-2 sm:px-3 py-1 border border-gray-400 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-xs sm:text-sm">
                    <span class="hidden sm:inline">Sau</span>
                    <i class="fas fa-chevron-right ml-1"></i>
                </button>
            </div>
        </div>


    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { usePages } from '../../../composable/usePages'
import { push } from 'notivue'

const { pages, loading, error, pagination, fetchPages, deletePage: deletePageApi } = usePages()

const columns = [
    { key: 'id', label: '#' },
    { key: 'title', label: 'Tiêu đề' },
    { key: 'slug', label: 'Slug' },
    { key: 'type', label: 'Loại' },
    { key: 'sort_order', label: 'Thứ tự' },
    { key: 'created_at', label: 'Ngày tạo' },
    { key: 'status', label: 'Trạng thái', type: 'status' }
]

const router = useRouter()

const searchQuery = ref('')
const selectedType = ref('')
const selectedStatus = ref('')
const currentPage = ref(1)
const sortKey = ref('')
const sortOrder = ref('asc')
const itemsPerPage = 10

const props = defineProps({
    isLoading: {
        type: Boolean,
        default: false
    }
})

const loadPages = async () => {
    try {
        await fetchPages(1, {})
    } catch (err) {
        console.error('Error loading pages:', err)
        push.error('Có lỗi xảy ra khi tải danh sách trang!')
    }
}

// Computed
const filteredData = computed(() => {
    let result = [...pages.value]

    // Search
    if (searchQuery.value) {
        result = result.filter(item =>
            Object.values(item).some(val =>
                String(val).toLowerCase().includes(searchQuery.value.toLowerCase())
            )
        )
    }

    // Type filter
    if (selectedType.value) {
        result = result.filter(item => item.type === selectedType.value)
    }

    // Status filter
    if (selectedStatus.value !== '') {
        const statusBool = selectedStatus.value === '1'
        result = result.filter(item => item.status === statusBool)
    }

    // Sort
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
    Math.ceil(filteredData.value.length / itemsPerPage)
)

const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage
    const end = start + itemsPerPage
    return filteredData.value.slice(start, end)
})

// Methods
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

const handleDelete = async (page) => {
    if (confirm(`Bạn có chắc chắn muốn xóa trang "${page.title}"?`)) {
        try {
            await deletePageApi(page.id)
            push.success('Xóa trang thành công!')
            await loadPages()
        } catch (err) {
            console.error('Error deleting page:', err)
            push.error('Có lỗi xảy ra khi xóa trang!')
        }
    }
}

const viewPage = (page) => {
    // Open page in new tab to view
    const baseUrl = import.meta.env.VITE_FRONTEND_URL || window.location.origin
    const pageUrl = `${baseUrl}/page/${page.slug}`
    window.open(pageUrl, '_blank')
}

// Watch for filter changes
watch([selectedType, selectedStatus], () => {
    currentPage.value = 1
})

// Utility functions
const getTypeLabel = (type) => {
    const labels = {
        policy: 'Chính sách',
        support: 'Hỗ trợ',
        other: 'Khác'
    }
    return labels[type] || type
}

const getTypeBadgeClass = (type) => {
    const classes = {
        policy: 'bg-blue-100 text-blue-700',
        support: 'bg-green-100 text-green-700',
        other: 'bg-gray-100 text-gray-700'
    }
    return classes[type] || 'bg-gray-100 text-gray-700'
}

const getStatusBadgeClass = (status) => {
    return status ? 'status-badge active' : 'status-badge inactive'
}

const getStatusText = (status) => {
    return status ? 'Hoạt động' : 'Vô hiệu'
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    })
}

// Load data on component mount
onMounted(() => {
    loadPages()
})
</script>

<style scoped>
.bg-primary {
    background-color: #3bb77e;
}

.bg-primary-dark {
    background-color: #2ea16d;
}

/* Custom scrollbar for table */
.overflow-x-auto::-webkit-scrollbar {
    height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Custom select arrow */
select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236B7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.5rem center;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
}

/* Focus styles */
input:focus,
select:focus {
    box-shadow: 0 0 0 2px rgba(59, 183, 126, 0.2);
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
</style>