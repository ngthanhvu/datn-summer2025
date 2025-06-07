<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <!-- Search and Filter Section -->
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
            <!-- Left side: Search and Filters -->
            <div class="tw-flex tw-gap-4">
                <!-- Search Box -->
                <div class="tw-relative">
                    <input type="text" v-model="searchQuery" placeholder="Tìm kiếm..." @input="handleSearch"
                        class="tw-border tw-rounded tw-px-4 tw-py-2 tw-pl-10 tw-w-64 focus:tw-outline-none focus:tw-border-primary">
                    <i
                        class="fas fa-search tw-absolute tw-left-3 tw-top-1/2 tw-transform -tw-translate-y-1/2 tw-text-gray-400"></i>
                </div>

                <!-- Status Filter -->
                <div class="tw-relative">
                    <select v-model="selectedStatus"
                        class="tw-border tw-rounded tw-px-4 tw-py-2 tw-w-56 focus:tw-outline-none focus:tw-border-primary appearance-none">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1">Hoạt động</option>
                        <option value="0">Vô hiệu</option>
                    </select>
                    <i
                        class="fas fa-chevron-down tw-absolute tw-right-3 tw-top-1/2 tw-transform -tw-translate-y-1/2 tw-text-gray-400 tw-pointer-events-none"></i>
                </div>

                <!-- Date Filter -->
                <div class="tw-relative">
                    <input v-model="selectedDate" type="date"
                        class="tw-border tw-rounded tw-px-4 tw-py-2 tw-w-56 focus:tw-outline-none focus:tw-border-primary">
                </div>
            </div>

            <!-- Right side: Add Button -->
            <NuxtLink to="/admin/promotions/create"
                class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-primary-dark tw-transition-colors">
                <i class="fas fa-plus"></i>
                Thêm mới
            </NuxtLink>
        </div>

        <!-- Table Section -->
        <div class="tw-overflow-x-auto">
            <table class="tw-w-full tw-text-left">
                <!-- Table Header -->
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

                <!-- Table Body -->
                <tbody>
                    <tr v-for="(item, index) in paginatedData" :key="index"
                        class="tw-border-b hover:tw-bg-gray-50 tw-transition-colors">
                        <!-- ID Column -->
                        <td class="tw-px-4 tw-py-3 tw-text-gray-600">
                            #{{ item.id }}
                        </td>

                        <!-- Name Column -->
                        <td class="tw-px-4 tw-py-3">
                            <div class="tw-font-medium">{{ item.name }}</div>
                        </td>

                        <!-- Code Column -->
                        <td class="tw-px-4 tw-py-3">
                            <span class="tw-bg-gray-100 tw-text-gray-700 tw-px-2 tw-py-1 tw-rounded tw-text-sm">
                                {{ item.code }}
                            </span>
                        </td>

                        <!-- Type Column -->
                        <td class="tw-px-4 tw-py-3">
                            <span :class="[
                                'tw-px-2 tw-py-1 tw-rounded tw-text-sm',
                                item.type === 'percentage'
                                    ? 'tw-bg-blue-100 tw-text-blue-700'
                                    : 'tw-bg-purple-100 tw-text-purple-700'
                            ]">
                                {{ item.type === 'percentage' ? 'Giảm theo %' : 'Giảm số tiền' }}
                            </span>
                        </td>

                        <!-- Value Column -->
                        <td class="tw-px-4 tw-py-3 tw-font-medium">
                            <span :class="[
                                item.type === 'percentage'
                                    ? 'tw-text-blue-600'
                                    : 'tw-text-purple-600'
                            ]">
                                {{ item.type === 'percentage' ? item.value + '%' : formatPrice(item.value) }}
                            </span>
                        </td>

                        <!-- Min Spend Column -->
                        <td class="tw-px-4 tw-py-3">
                            {{ formatPrice(item.minSpend) }}
                        </td>

                        <!-- Max Discount Column -->
                        <td class="tw-px-4 tw-py-3">
                            {{ formatPrice(item.maxDiscount) }}
                        </td>

                        <!-- Usage Limit Column -->
                        <td class="tw-px-4 tw-py-3">
                            {{ item.usageLimit === 0 ? 'Không giới hạn' : item.usageLimit }}
                        </td>

                        <!-- Usage Count Column -->
                        <td class="tw-px-4 tw-py-3">
                            <div class="tw-flex tw-items-center tw-gap-2">
                                <span>{{ item.usageCount }}</span>
                                <div class="tw-w-16 tw-h-2 tw-bg-gray-200 tw-rounded-full">
                                    <div class="tw-h-full tw-bg-primary tw-rounded-full"
                                        :style="{ width: `${(item.usageCount / (item.usageLimit || 1)) * 100}%` }">
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Start Date Column -->
                        <td class="tw-px-4 tw-py-3">
                            {{ formatDate(item.startDate) }}
                        </td>

                        <!-- End Date Column -->
                        <td class="tw-px-4 tw-py-3">
                            {{ formatDate(item.endDate) }}
                        </td>

                        <!-- Status Column -->
                        <td class="tw-px-4 tw-py-3">
                            <span :class="getStatusBadgeClass(item.status)">
                                {{ getStatusText(item.status) }}
                            </span>
                        </td>

                        <!-- Actions Column -->
                        <td class="tw-px-4 tw-py-3">
                            <div class="tw-flex tw-gap-2">
                                <NuxtLink :to="'/admin/promotions/' + item.id"
                                    class="tw-bg-blue-600 tw-text-white tw-rounded tw-p-2 hover:tw-bg-blue-700 tw-transition-colors">
                                    <i class="fas fa-edit"></i>
                                </NuxtLink>
                                <button @click="handleDelete(item)"
                                    class="tw-bg-red-600 tw-text-white tw-rounded tw-p-2 hover:tw-bg-red-700 tw-transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Section -->
        <div class="tw-flex tw-justify-between tw-items-center tw-mt-6">
            <!-- Records Info -->
            <div class="tw-text-sm tw-text-gray-600">
                Hiển thị {{ paginatedData.length }} trên tổng số {{ filteredData.length }} bản ghi
            </div>

            <!-- Pagination Controls -->
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
import { ref, computed, watch } from 'vue'

const emit = defineEmits(['delete', 'filter-change'])

// Table columns configuration
const columns = [
    { key: 'id', label: 'ID' },
    { key: 'name', label: 'Tên chương trình' },
    { key: 'code', label: 'Mã giảm giá' },
    { key: 'type', label: 'Loại' },
    { key: 'value', label: 'Giá trị' },
    { key: 'minSpend', label: 'Đơn tối thiểu', type: 'price' },
    { key: 'maxDiscount', label: 'Giảm tối đa', type: 'price' },
    { key: 'usageLimit', label: 'Giới hạn' },
    { key: 'usageCount', label: 'Đã dùng' },
    { key: 'startDate', label: 'Ngày bắt đầu' },
    { key: 'endDate', label: 'Ngày kết thúc' },
    { key: 'status', label: 'Trạng thái', type: 'status' }
]

// Mock data
const promotions = ref([
    {
        id: 1,
        name: 'Giảm giá mùa hè',
        code: 'SUMMER2024',
        type: 'percentage',
        value: 20,
        minSpend: 1000000,
        maxDiscount: 500000,
        usageLimit: 100,
        usageCount: 45,
        startDate: '2024-06-01',
        endDate: '2024-08-31',
        status: true
    },
    {
        id: 2,
        name: 'Giảm giá sinh nhật',
        code: 'BIRTHDAY',
        type: 'fixed',
        value: 200000,
        minSpend: 500000,
        maxDiscount: 200000,
        usageLimit: 0,
        usageCount: 156,
        startDate: '2024-01-01',
        endDate: '2024-12-31',
        status: true
    },
    {
        id: 3,
        name: 'Flash sale cuối tuần',
        code: 'WEEKEND',
        type: 'percentage',
        value: 15,
        minSpend: 300000,
        maxDiscount: 300000,
        usageLimit: 50,
        usageCount: 50,
        startDate: '2024-03-01',
        endDate: '2024-03-31',
        status: false
    }
])

// State
const searchQuery = ref('')
const selectedStatus = ref('')
const selectedDate = ref('')
const currentPage = ref(1)
const sortKey = ref('')
const sortOrder = ref('asc')
const itemsPerPage = 10

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
        result = result.filter(item => String(item.status) === selectedStatus.value)
    }

    // Date filter
    if (selectedDate.value) {
        result = result.filter(item => item.startDate === selectedDate.value || item.endDate === selectedDate.value)
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

const handleDelete = (promotion) => {
    if (confirm('Bạn có chắc chắn muốn xóa chương trình khuyến mãi này?')) {
        const index = promotions.value.findIndex(p => p.id === promotion.id)
        if (index !== -1) {
            promotions.value.splice(index, 1)
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

// Add new utility function for date formatting
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    })
}
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
</style>