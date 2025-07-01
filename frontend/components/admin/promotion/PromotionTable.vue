<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6 tw-text-sm">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
            <div class="tw-flex tw-gap-4">
                <div class="tw-relative">
                    <input type="text" v-model="searchQuery" placeholder="Tìm kiếm..." @input="handleSearch"
                        class="tw-border tw-rounded tw-px-4 tw-py-2 tw-pl-10 tw-w-64 focus:tw-outline-none focus:tw-border-primary">
                    <i
                        class="fas fa-search tw-absolute tw-left-3 tw-top-1/2 tw-transform -tw-translate-y-1/2 tw-text-gray-400"></i>
                </div>
                <div class="tw-relative">
                    <select v-model="selectedStatus"
                        class="tw-border tw-rounded tw-px-4 tw-py-2 tw-w-56 focus:tw-outline-none focus:tw-border-primary appearance-none">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1">Hoạt động</option>
                        <option value="0">Vô hiệu</option>
                    </select>
                </div>
                <div class="tw-relative">
                    <input v-model="selectedDate" type="date"
                        class="tw-border tw-rounded tw-px-4 tw-py-2 tw-w-56 focus:tw-outline-none focus:tw-border-primary">
                </div>
            </div>
            <NuxtLink to="/admin/promotions/create"
                class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-primary-dark tw-transition-colors">
                <i class="fas fa-plus"></i>
                Thêm mới
            </NuxtLink>
        </div>
        <div v-if="error"
            class="tw-bg-red-100 tw-border tw-border-red-400 tw-text-red-700 tw-px-4 tw-py-3 tw-rounded tw-mb-4">
            {{ error }}
        </div>
        <div v-else class="tw-overflow-x-auto">
            <table class="tw-w-full tw-text-left">
                <thead>
                    <tr class="tw-border-b tw-bg-gray-50">
                        <th v-for="column in columns" :key="column.key"
                            class="tw-px-4 tw-py-3 tw-font-semibold tw-cursor-pointer hover:tw-bg-gray-100"
                            @click="sortBy(column.key)">
                            <div class="tw-flex tw-items-center tw-gap-2">
                                {{ column.label }}
                                <i v-if="sortKey === column.key"
                                    :class="['fas', sortOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down', 'tw-text-primary']"></i>
                            </div>
                        </th>
                        <th class="tw-px-4 tw-py-3 tw-font-semibold">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Skeleton loading -->
                    <tr v-if="props.isLoading" v-for="n in 13" :key="'skeleton-' + n">
                        <td v-for="i in 13" :key="i" class="tw-px-4 tw-py-3">
                            <div class="skeleton-loader"></div>
                        </td>
                    </tr>
                    <tr v-else v-for="(item, index) in paginatedData" :key="index"
                        class="tw-border-b hover:tw-bg-gray-50 tw-transition-colors">
                        <td class="tw-px-4 tw-py-3 tw-text-gray-600">
                            {{ index + 1 }}
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            <div class="tw-font-medium">{{ item.name }}</div>
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            <span class="tw-bg-gray-100 tw-text-gray-700 tw-px-2 tw-py-1 tw-rounded tw-text-sm">
                                {{ item.code }}
                            </span>
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            <span :class="[
                                'tw-px-2 tw-py-1 tw-rounded tw-text-sm',
                                item.type === 'percent'
                                    ? 'tw-bg-blue-100 tw-text-blue-700'
                                    : 'tw-bg-purple-100 tw-text-purple-700'
                            ]">
                                {{ item.type === 'percent' ? 'Giảm theo %' : 'Giảm số tiền' }}
                            </span>
                        </td>
                        <td class="tw-px-4 tw-py-3 tw-font-medium">
                            <span :class="[
                                item.type === 'percent'
                                    ? 'tw-text-blue-600'
                                    : 'tw-text-purple-600'
                            ]">
                                {{ item.type === 'percent' ? Math.round(parseFloat(item.value)) + '%' :
                                    formatPrice(item.value) }}
                            </span>
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            {{ formatPrice(item.min_order_value) }}
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            <!-- {{ formatPrice(item.max_discount_value) }} -->
                            {{
                                item.max_discount_value != null ? formatPrice(item.max_discount_value) :
                                    'Giảm theo phần trăm'
                            }}
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            {{ item.usage_limit === 0 ? 'Không giới hạn' : item.usage_limit }}
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            <div class="tw-flex tw-items-center tw-gap-2">
                                <span>{{ item.used_count }}</span>
                            </div>
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            {{ formatDate(item.start_date) }}
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            {{ formatDate(item.end_date) }}
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            <span :class="getStatusBadgeClass(item.is_active)">
                                {{ getStatusText(item.is_active) }}
                            </span>
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            <div class="tw-flex tw-items-center tw-gap-2">
                                <NuxtLink :to="'/admin/promotions/' + item.id"
                                    class="tw-inline-flex tw-items-center tw-p-1.5 tw-text-blue-600 hover:tw-text-blue-900 hover:tw-bg-blue-50 tw-rounded-lg tw-transition-colors tw-duration-150"
                                    title="Xem/Chỉnh sửa khuyến mãi">
                                    <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                </NuxtLink>
                                <button @click="handleDelete(item)"
                                    class="tw-inline-flex tw-items-center tw-p-1.5 tw-text-red-600 hover:tw-text-red-900 hover:tw-bg-red-50 tw-rounded-lg tw-transition-colors tw-duration-150"
                                    title="Xóa khuyến mãi">
                                    <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!props.isLoading && !paginatedData.length">
                        <td colspan="12" class="tw-px-4 tw-py-3 tw-text-center tw-text-gray-600">
                            Không có dữ liệu
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="!loading && !error" class="tw-flex tw-justify-between tw-items-center tw-mt-6">
            <div class="tw-text-sm tw-text-gray-600">
                Hiển thị {{ paginatedData.length }} trên tổng số {{ filteredData.length }} bản ghi
            </div>
            <div class="tw-flex tw-gap-2">
                <button :disabled="currentPage === 1" @click="currentPage--"
                    class="tw-px-3 tw-py-1 tw-border tw-rounded hover:tw-bg-gray-50 disabled:tw-opacity-50 disabled:tw-cursor-not-allowed tw-transition-colors">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span class="tw-px-3 tw-py-1">
                    Trang {{ currentPage }} / {{ totalPages }}
                </span>
                <button :disabled="currentPage === totalPages" @click="currentPage++"
                    class="tw-px-3 tw-py-1 tw-border tw-rounded hover:tw-bg-gray-50 disabled:tw-opacity-50 disabled:tw-cursor-not-allowed tw-transition-colors">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useCoupon } from '~/composables/useCoupon'

const emit = defineEmits(['delete', 'filter-change'])

const { getCoupons, deleteCoupon } = useCoupon()

const columns = [
    { key: 'id', label: '#' },
    { key: 'name', label: 'Tên chương trình' },
    { key: 'code', label: 'Mã giảm giá' },
    { key: 'type', label: 'Loại' },
    { key: 'value', label: 'Giá trị' },
    { key: 'min_order_value', label: 'Đơn tối thiểu', type: 'price' },
    { key: 'max_discount_value', label: 'Giảm tối đa', type: 'price' },
    { key: 'usage_limit', label: 'Giới hạn' },
    { key: 'used_count', label: 'Đã dùng' },
    { key: 'start_date', label: 'Ngày bắt đầu' },
    { key: 'end_date', label: 'Ngày kết thúc' },
    { key: 'is_active', label: 'Trạng thái', type: 'status' }
]

const promotions = ref([])
const loading = ref(false)
const error = ref('')
const searchQuery = ref('')
const selectedStatus = ref('')
const selectedDate = ref('')
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

const loadPromotions = async () => {
    try {
        loading.value = true
        error.value = ''
        const data = await getCoupons()
        promotions.value = data
    } catch (err) {
        error.value = 'Không thể tải dữ liệu khuyến mãi. Vui lòng thử lại.'
        console.error('Error loading promotions:', err)
    } finally {
        loading.value = false
    }
}

// Computed
const filteredData = computed(() => {
    let result = [...promotions.value]

    // Search
    if (searchQuery.value) {
        result = result.filter(item =>
            Object.values(item).some(val =>
                String(val).toLowerCase().includes(searchQuery.value.toLowerCase())
            )
        )
    }

    // Status filter
    if (selectedStatus.value) {
        const statusBool = selectedStatus.value === '1'
        result = result.filter(item => item.is_active === statusBool)
    }

    // Date filter
    if (selectedDate.value) {
        result = result.filter(item => {
            const startDate = new Date(item.start_date).toISOString().split('T')[0]
            const endDate = new Date(item.end_date).toISOString().split('T')[0]
            return startDate === selectedDate.value || endDate === selectedDate.value
        })
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

const handleDelete = async (promotion) => {
    if (confirm('Bạn có chắc chắn muốn xóa chương trình khuyến mãi này?')) {
        try {
            await deleteCoupon(promotion.id)
            await loadPromotions()
        } catch (err) {
            error.value = 'Không thể xóa chương trình khuyến mãi. Vui lòng thử lại.'
            console.error('Error deleting promotion:', err)
        }
    }
}

// Watch for filter changes
watch([selectedStatus, selectedDate], () => {
    currentPage.value = 1
    emit('filter-change', {
        status: selectedStatus.value,
        date: selectedDate.value
    })
})

// Utility functions
const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}

const getStatusBadgeClass = (status) => {
    return status
        ? 'tw-bg-green-100 tw-text-green-700 tw-px-2 tw-py-1 tw-rounded-full tw-text-xs'
        : 'tw-bg-red-100 tw-text-red-700 tw-px-2 tw-py-1 tw-rounded-full tw-text-xs'
}

const getStatusText = (status) => {
    return status ? 'Hoạt động' : 'Vô hiệu'
}

// Format date function
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    })
}

// Load data on component mount
onMounted(() => {
    loadPromotions()
})
</script>

<style scoped>
.tw-bg-primary {
    background-color: #3bb77e;
}

.tw-bg-primary-dark {
    background-color: #2ea16d;
}

/* Custom scrollbar for table */
.tw-overflow-x-auto::-webkit-scrollbar {
    height: 6px;
}

.tw-overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.tw-overflow-x-auto::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.tw-overflow-x-auto::-webkit-scrollbar-thumb:hover {
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