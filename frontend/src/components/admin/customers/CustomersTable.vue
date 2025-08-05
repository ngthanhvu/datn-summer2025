<template>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div class="relative">
                    <input type="text" v-model="searchQuery" placeholder="Tìm kiếm..."
                        class="border border-gray-300 rounded px-4 py-2 pl-10 w-64">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
                <select v-model="filterStatus" class="border border-gray-300 rounded px-4 py-2">
                    <option value="">Tất cả trạng thái</option>
                    <option value="true">Đang hoạt động</option>
                    <option value="false">Đã khóa</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <table class="w-full">
                <thead class="border-b border-gray-300">
                    <tr>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                            #
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                            Ảnh
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                            Tên người dùng
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                            Email
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                            Số điện thoại
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                            Trạng thái
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                            Thao tác
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <!-- Skeleton loading -->
                    <tr v-if="props.isLoading" v-for="n in 7" :key="'skeleton-' + n">
                        <td v-for="i in 7" :key="i" class="px-4 py-3">
                            <div class="skeleton-loader"></div>
                        </td>
                    </tr>
                    <tr v-else v-for="(customer, index) in paginatedCustomers" :key="customer.id"
                        class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900 text-center">{{ getDisplayIndex(index) }}</td>
                        <td class="px-4 py-3 text-center">
                            <img :src="customer.avatar || defaultAvatar" :alt="customer.username"
                                class="w-10 h-10 rounded-full object-cover mx-auto" />
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-center">{{ customer.username }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-center">{{ customer.email }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-center">{{ customer.phone ?
                            customer.phone : 'Không có' }}</td>
                        <td class="px-4 py-3 text-center">
                            <button
                                :class="['w-10 h-6 rounded-full relative transition-colors', customer.status ? 'bg-primary' : 'bg-gray-300']"
                                @click="toggleStatus(customer)" :aria-pressed="customer.status"
                                style="background-color: #3bb77e">
                                <span
                                    :class="['absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform', customer.status ? 'translate-x-4' : '']"></span>
                            </button>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium">
                            <div class="flex items-center justify-center gap-2">
                                <router-link :to="`/admin/customers/${customer.id}`"
                                    class="inline-flex items-center p-1.5 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-colors duration-150"
                                    title="Xem/Chỉnh sửa khách hàng">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                </router-link>
                                <button @click="handleDelete(customer)"
                                    class="inline-flex items-center p-1.5 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors duration-150"
                                    title="Xóa khách hàng">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!props.isLoading && paginatedCustomers.length === 0">
                        <td colspan="8" class="text-center text-gray-500">Không có dữ liệu</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Simple Pagination -->
        <div v-if="!props.isLoading && totalPages > 1" class="flex justify-between items-center mt-6">
            <div class="text-sm text-gray-600">
                Hiển thị {{ paginatedCustomers.length }} trên tổng số {{ filteredCustomers.length }} bản ghi
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
    customers: {
        type: Array,
        required: true
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
    },
    totalItems: {
        type: Number,
        default: 0
    }
})

const defaultAvatar = ref('https://img.freepik.com/premium-vector/error-404-found-glitch-effect_8024-4.jpg')
const emit = defineEmits(['delete', 'page-change'])

const searchQuery = ref('')
const filterStatus = ref('')

const filteredCustomers = computed(() => {
    return props.customers.filter(customer => {
        const matchesSearch =
            (customer.username?.toLowerCase().includes(searchQuery.value.toLowerCase()) || '') ||
            (customer.email?.toLowerCase().includes(searchQuery.value.toLowerCase()) || '') ||
            (customer.phone?.includes(searchQuery.value) || '')

        const matchesStatus = filterStatus.value === '' || customer.status?.toString() === filterStatus.value

        return matchesSearch && matchesStatus
    })
})

// Simple pagination computed properties
const totalPages = computed(() => Math.ceil(filteredCustomers.value.length / props.itemsPerPage))
const startIndex = computed(() => (props.currentPage - 1) * props.itemsPerPage)
const endIndex = computed(() => Math.min(startIndex.value + props.itemsPerPage, filteredCustomers.value.length))

const paginatedCustomers = computed(() => {
    return filteredCustomers.value.slice(startIndex.value, endIndex.value)
})

const getDisplayIndex = (index) => {
    return startIndex.value + index + 1
}

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value && page !== props.currentPage) {
        emit('page-change', page)
    }
}

const handleDelete = (customer) => {
    if (confirm('Bạn có chắc chắn muốn xóa khách hàng này?')) {
        emit('delete', customer)
    }
}

const toggleStatus = async (customer) => {
    const newStatus = customer.status ? 0 : 1
    try {
        await updateCustomerStatus(customer.id, newStatus)
        customer.status = newStatus
        // Nếu có notyf hoặc emit refresh thì gọi ở đây
    } catch (e) {
        // Nếu có notyf thì báo lỗi ở đây
    }
}

// Hàm giả lập gọi API cập nhật trạng thái
const updateCustomerStatus = async (id, status) => {
    // TODO: Thay bằng gọi API thực tế
    return new Promise((resolve) => setTimeout(resolve, 500))
}
</script>

<style scoped>
.text-primary {
    color: #3bb77e;
}

.text-primary-dark {
    color: #2d9d6a;
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