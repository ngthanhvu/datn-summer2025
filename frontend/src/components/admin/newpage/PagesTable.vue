<template>
    <div class="bg-white rounded-lg shadow p-6 text-sm">
        <div class="flex justify-between items-center mb-6">
            <div class="flex gap-4">
                <div class="relative">
                    <input type="text" v-model="searchQuery" placeholder="Tìm kiếm..." @input="handleSearch"
                        class="border border-gray-300 rounded px-4 py-2 pl-10 w-64 focus:outline-none focus:border-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
                <div class="relative">
                    <select v-model="selectedType"
                        class="border border-gray-300 rounded px-4 py-2 w-56 focus:outline-none focus:border-primary appearance-none">
                        <option value="">Tất cả loại trang</option>
                        <option value="policy">Chính sách</option>
                        <option value="support">Hỗ trợ</option>
                        <option value="other">Khác</option>
                    </select>
                </div>
                <div class="relative">
                    <select v-model="selectedStatus"
                        class="border border-gray-300 rounded px-4 py-2 w-56 focus:outline-none focus:border-primary appearance-none">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1">Hoạt động</option>
                        <option value="0">Vô hiệu</option>
                    </select>
                </div>
            </div>
            <button @click="$router.push('/admin/pages/create')"
                class="bg-primary text-white rounded px-4 py-2 flex items-center gap-2 hover:bg-primary-dark transition-colors cursor-pointer">
                <i class="fas fa-plus"></i>
                Thêm mới
            </button>
        </div>
        <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ error }}
        </div>
        <div v-else class="overflow-x-auto overflow-hidden rounded-2xl border border-gray-200 bg-white">
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
                            <button
                                :class="['w-10 h-6 rounded-full relative transition-colors', item.status ? 'bg-primary' : 'bg-gray-300']"
                                @click="toggleStatus(item)" :aria-pressed="item.status"
                                style="background-color: #3bb77e">
                                <span
                                    :class="['absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform', item.status ? 'translate-x-4' : '']"></span>
                            </button>
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

        <div v-if="!loading && !error" class="flex justify-between items-center mt-6">
            <div class="text-sm text-gray-600">
                Hiển thị {{ paginatedData.length }} trên tổng số {{ filteredData.length }} bản ghi
            </div>
            <div class="flex gap-2">
                <button :disabled="currentPage === 1" @click="currentPage--"
                    class="px-3 py-1 border border-gray-400 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span class="px-3 py-1">
                    Trang {{ currentPage }} / {{ totalPages }}
                </span>
                <button :disabled="currentPage === totalPages" @click="currentPage++"
                    class="px-3 py-1 border border-gray-400 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <i class="fas fa-chevron-right"></i>
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

const { pages, loading, error, pagination, fetchPages, updatePageStatus, deletePage: deletePageApi } = usePages()

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

const toggleStatus = async (page) => {
    try {
        await updatePageStatus(page.id, !page.status)
        push.success('Cập nhật trạng thái trang thành công!')
        await loadPages()
    } catch (error) {
        console.error('Error updating page status:', error)
        push.error('Có lỗi xảy ra khi cập nhật trạng thái trang!')
    }
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
</style> 