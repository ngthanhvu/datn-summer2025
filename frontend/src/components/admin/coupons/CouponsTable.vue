<template>
    <div class="bg-white rounded-lg shadow p-4 sm:p-6 text-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="relative w-full sm:w-auto">
                    <input type="text" v-model="searchQuery" placeholder="Tìm kiếm..." @input="handleSearch"
                        class="border border-gray-300 rounded px-4 py-2 pl-10 w-full sm:w-64 focus:outline-none focus:border-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
                <div class="relative w-full sm:w-auto">
                    <select v-model="selectedStatus"
                        class="border border-gray-300 rounded px-4 py-2 w-full sm:w-56 focus:outline-none focus:border-primary appearance-none">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1">Hoạt động</option>
                        <option value="0">Vô hiệu</option>
                    </select>
                </div>
                <div class="relative w-full sm:w-auto">
                    <input v-model="selectedDate" type="date"
                        class="border border-gray-300 rounded px-4 py-2 w-full sm:w-56 focus:outline-none focus:border-primary">
                </div>
            </div>
            <router-link to="/admin/coupons/create"
                class="w-full sm:w-auto bg-primary text-white rounded px-4 py-2 flex items-center justify-center gap-2 hover:bg-primary-dark transition-colors cursor-pointer">
                <i class="fas fa-plus"></i>
                Thêm mới
            </router-link>
        </div>
        <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ error }}
        </div>

        <!-- Desktop table -->
        <div v-else class="overflow-x-auto overflow-hidden rounded-2xl border border-gray-200 bg-white hidden md:block">
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
                        <td v-for="i in 13" :key="i" class="px-4 py-3">
                            <div class="skeleton-loader"></div>
                        </td>
                    </tr>
                    <tr v-else v-for="(item, index) in paginatedData" :key="index"
                        class="border-b border-gray-300 hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-gray-600">
                            {{ index + 1 }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ item.name }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-sm">
                                {{ item.code }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span :class="[
                                'px-2 py-1 rounded text-sm',
                                item.type === 'percent'
                                    ? 'bg-blue-100 text-blue-700'
                                    : (item.type === 'shipping'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-purple-100 text-purple-700')
                            ]">
                                {{ item.type === 'percent' ? 'Giảm Theo %' : (item.type === 'shipping' ? 'Miễn Phí Ship'
                                    : 'Giảm Số Tiền') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-medium">
                            <span :class="[
                                item.type === 'percent'
                                    ? 'text-blue-600'
                                    : (item.type === 'shipping' ? 'text-green-600' : 'text-purple-600')
                            ]">
                                {{
                                    item.type === 'percent'
                                        ? Math.round(parseFloat(item.value)) + '%'
                                        : (item.type === 'shipping' ? 'Miễn ship' : formatPrice(item.value))
                                }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            {{ formatPrice(item.min_order_value) }}
                        </td>
                        <td class="px-4 py-3">
                            {{
                                item.type === 'shipping'
                                    ? 'Miễn ship'
                                    : (item.max_discount_value != null
                                        ? formatPrice(item.max_discount_value)
                                        : 'Giảm theo phần trăm')
                            }}
                        </td>
                        <td class="px-4 py-3">
                            {{ item.usage_limit === 0 ? 'Không giới hạn' : item.usage_limit }}
                        </td>
                        <!-- <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span>{{ item.used_count }}</span>
                            </div>
                        </td> -->
                        <td class="px-4 py-3">
                            {{ formatDate(item.start_date) }}
                        </td>
                        <td class="px-4 py-3">
                            {{ formatDate(item.end_date) }}
                        </td>
                        <td class="px-4 py-3">
                            <span :class="getStatusBadgeClass(item.is_active)">
                                {{ getStatusText(item.is_active) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <router-link :to="'/admin/coupons/edit/' + item.id"
                                    class="inline-flex items-center p-1.5 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-colors duration-150"
                                    title="Xem/Chỉnh sửa khuyến mãi">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                </router-link>
                                <button @click="handleDelete(item)"
                                    class="inline-flex items-center p-1.5 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors duration-150"
                                    title="Xóa khuyến mãi">
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
                        <td colspan="12" class="px-4 py-3 text-center text-gray-600">
                            Không có dữ liệu
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile card list -->
        <div v-if="!props.isLoading && paginatedData.length > 0" class="space-y-3 md:hidden">
            <div v-for="(item, index) in paginatedData" :key="'m-' + index"
                class="rounded-lg border border-gray-200 p-3">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold truncate">{{ item.name }}</div>
                        <div class="text-xs text-gray-500 break-all">{{ item.code }}</div>
                    </div>
                    <div class="flex gap-1">
                        <router-link :to="'/admin/coupons/edit/' + item.id"
                            class="p-1 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded transition-colors"
                            title="Xem/Chỉnh sửa khuyến mãi">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                        </router-link>
                        <button @click="handleDelete(item)"
                            class="p-1 text-red-600 hover:text-red-900 hover:bg-red-50 rounded transition-colors"
                            title="Xóa khuyến mãi">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="text-gray-500">Loại</div>
                    <div class="text-right">
                        <span :class="[
                            'px-2 py-0.5 rounded text-[10px] inline-block',
                            item.type === 'percent'
                                ? 'bg-blue-100 text-blue-700'
                                : (item.type === 'shipping'
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-purple-100 text-purple-700')
                        ]">
                            {{
                                item.type === 'percent' ? 'Giảm theo %' : (
                                    item.type === 'shipping' ? 'Miễn ship' : 'Giảm số tiền'
                                )
                            }}
                        </span>
                    </div>
                    <div class="text-gray-500">Giá trị</div>
                    <div class="text-right font-medium">
                        <span :class="[
                            item.type === 'percent'
                                ? 'text-blue-600'
                                : (item.type === 'shipping' ? 'text-green-600' : 'text-purple-600')
                        ]">
                            {{
                                item.type === 'percent'
                                    ? Math.round(parseFloat(item.value)) + '%'
                                    : (item.type === 'shipping' ? 'Miễn ship' : formatPrice(item.value))
                            }}
                        </span>
                    </div>
                    <div class="text-gray-500">Đơn tối thiểu</div>
                    <div class="text-right">{{ formatPrice(item.min_order_value) }}</div>
                    <div class="text-gray-500">Giảm tối đa</div>
                    <div class="text-right">
                        {{
                            item.type === 'shipping'
                                ? 'Miễn ship'
                                : (item.max_discount_value != null
                                    ? formatPrice(item.max_discount_value)
                                    : 'Giảm theo %')
                        }}
                    </div>
                    <div class="text-gray-500">Giới hạn</div>
                    <div class="text-right">{{ item.usage_limit === 0 ? 'Không giới hạn' : item.usage_limit }}</div>
                    <!-- <div class="text-gray-500">Đã dùng</div>
                    <div class="text-right">{{ item.used_count }}</div> -->
                    <div class="text-gray-500">Ngày bắt đầu</div>
                    <div class="text-right">{{ formatDate(item.start_date) }}</div>
                    <div class="text-gray-500">Ngày kết thúc</div>
                    <div class="text-right">{{ formatDate(item.end_date) }}</div>
                    <div class="text-gray-500">Trạng thái</div>
                    <div class="text-right">
                        <span :class="getStatusBadgeClass(item.is_active)">
                            {{ getStatusText(item.is_active) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="!props.isLoading && !paginatedData.length" class="text-center text-gray-500 py-4 md:hidden">
            Không có dữ liệu
        </div>

        <div v-if="!loading && !error" class="flex flex-col sm:flex-row sm:justify-between sm:items-center mt-6 gap-3">
            <div class="text-sm text-gray-600 text-center sm:text-left">
                Hiển thị {{ paginatedData.length }} trên tổng số {{ filteredData.length }} bản ghi
            </div>
            <div class="flex justify-center gap-2">
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
import { useCoupon } from '../../../composable/useCoupon'
import Swal from 'sweetalert2'
import { push } from 'notivue'
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
    // { key: 'used_count', label: 'Đã dùng' },
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
    // if (confirm('Bạn có chắc chắn muốn xóa chương trình khuyến mãi này?')) {
    //     try {
    //         await deleteCoupon(promotion.id)
    //         await loadPromotions()
    //     } catch (err) {
    //         error.value = 'Không thể xóa chương trình khuyến mãi. Vui lòng thử lại.'
    //         console.error('Error deleting promotion:', err)
    //     }
    // }
    const result = await Swal.fire({
        title: 'Bạn có chắc chắn muốn xóa khuyến mái?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Xóa',
    })

    if (result.isConfirmed) {
        try {
            await deleteCoupon(promotion.id)
            await loadPromotions()
            push.success('Đã xóa khuyến mái.')
        } catch (err) {
            error.value = 'Không thể xóa khuyến mái. Vui lòng thử lại.'
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
    return status === true
        ? 'status-badge active'
        : 'status-badge inactive'
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
.bg-primary {
    background-color: #3bb77e;
}

.bg-primary-dark {
    background-color: #2ea16d;
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