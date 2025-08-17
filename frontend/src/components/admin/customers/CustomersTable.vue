<template>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="relative w-full sm:w-auto">
                        <input type="text" v-model="searchQuery" placeholder="Tìm kiếm..."
                            class="border border-gray-300 rounded px-4 py-2 pl-10 w-full sm:w-64">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <select v-model="filterStatus" class="border border-gray-300 rounded px-4 py-2 w-full sm:w-auto">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1">Đang hoạt động</option>
                        <option value="0">Đã khóa</option>
                    </select>
                    <select v-model="filterRole" class="border border-gray-300 rounded px-4 py-2 w-full sm:w-auto">
                        <option value="">Tất cả vai trò</option>
                        <option value="user">Người dùng</option>
                        <option value="admin">Quản trị viên</option>
                        <option value="master_admin">Master Admin</option>
                    </select>
                </div>
                <button @click="openCreateModal"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Thêm người dùng
                </button>
            </div>
        </div>

        <!-- Desktop table -->
        <div class="overflow-x-auto overflow-hidden rounded-2xl border border-gray-200 bg-white hidden md:block">
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
                            Vai trò
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                            Trạng thái
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                            Lý do ban
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                            Thao tác
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <!-- Skeleton loading -->
                    <tr v-if="props.isLoading" v-for="n in 7" :key="'skeleton-' + n">
                        <td v-for="i in 8" :key="i" class="px-4 py-3">
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
                            <span v-if="customer.role === 'master_admin'"
                                class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700 border border-yellow-400"
                                title="Master Admin - Quyền cao nhất, có thể quản lý tất cả">
                                <i class="fas fa-crown mr-1"></i>Master Admin
                            </span>
                            <span v-else-if="customer.role === 'admin'"
                                class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-700 border border-purple-200"
                                title="Quản trị viên - Có quyền truy cập tất cả tính năng">
                                <i class="fas fa-shield-alt mr-1"></i>Quản trị viên
                            </span>
                            <span v-else
                                class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700 border border-blue-200"
                                title="Người dùng - Quyền truy cập hạn chế">
                                <i class="fas fa-user mr-1"></i>Người dùng
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span v-if="customer.status == 1"
                                class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700 border border-green-300">Hoạt
                                động</span>
                            <span v-else
                                class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-red-100 border border-red-500 text-red-700">Đã
                                khoá</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span v-if="customer.status == 0 && customer.note"
                                class="text-sm text-gray-700 max-w-xs truncate block mx-auto" :title="customer.note">
                                {{ customer.note }}
                            </span>
                            <span v-else class="text-sm text-gray-400">-</span>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="openEditModal(customer)"
                                    class="inline-flex items-center p-1.5 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-colors duration-150 cursor-pointer"
                                    title="Chỉnh sửa khách hàng">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                </button>
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
                        <td colspan="9" class="text-center text-gray-500">Không có dữ liệu</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile card list -->
        <div v-if="!props.isLoading && paginatedCustomers.length > 0" class="space-y-3 md:hidden">
            <div v-for="(customer, index) in paginatedCustomers" :key="'m-' + customer.id"
                class="rounded-lg border border-gray-200 p-3">
                <div class="flex items-center gap-3">
                    <img :src="customer.avatar || defaultAvatar" :alt="customer.username"
                        class="w-12 h-12 rounded-full object-cover" />
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <div>
                                <div class="text-sm font-semibold truncate">{{ customer.username }}</div>
                                <div class="text-xs text-gray-500 break-all">{{ customer.email }}</div>
                            </div>
                            <div class="flex gap-1">
                                <button @click="openEditModal(customer)"
                                    class="p-1 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded transition-colors"
                                    title="Chỉnh sửa khách hàng">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                </button>
                                <button @click="handleDelete(customer)"
                                    class="p-1 text-red-600 hover:text-red-900 hover:bg-red-50 rounded transition-colors"
                                    title="Xóa khách hàng">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="mt-2 grid grid-cols-2 gap-2 text-xs">
                            <div class="text-gray-500">Số điện thoại</div>
                            <div class="text-right">{{ customer.phone ? customer.phone : 'Không có' }}</div>
                            <div class="text-gray-500">Vai trò</div>
                            <div class="text-right">
                                <span v-if="customer.role === 'master_admin'"
                                    class="inline-block px-2 py-0.5 text-[10px] font-semibold rounded-full bg-red-100 text-red-700 border border-red-200"
                                    title="Master Admin - Quyền cao nhất, có thể quản lý tất cả">
                                    <i class="fas fa-crown mr-1"></i>Master Admin
                                </span>
                                <span v-else-if="customer.role === 'admin'"
                                    class="inline-block px-2 py-0.5 text-[10px] font-semibold rounded-full bg-purple-100 text-purple-700 border border-purple-200"
                                    title="Quản trị viên - Có quyền truy cập tất cả tính năng">
                                    <i class="fas fa-shield-alt mr-1"></i>Quản trị viên
                                </span>
                                <span v-else
                                    class="inline-block px-2 py-0.5 text-[10px] font-semibold rounded-full bg-blue-100 text-blue-700 border border-blue-200"
                                    title="Người dùng - Quyền truy cập hạn chế">
                                    <i class="fas fa-user mr-1"></i>Người dùng
                                </span>
                            </div>
                            <div class="text-gray-500">Trạng thái</div>
                            <div class="text-right">
                                <span v-if="customer.status == 1"
                                    class="inline-block px-2 py-0.5 text-[10px] font-semibold rounded-full bg-green-100 text-green-700">Hoạt
                                    động</span>
                                <span v-else
                                    class="inline-block px-2 py-0.5 text-[10px] font-semibold rounded-full bg-red-500 text-white">Đã
                                    khoá</span>
                            </div>
                            <div v-if="customer.status == 0" class="text-gray-500">Lý do ban</div>
                            <div v-if="customer.status == 0" class="text-right text-xs text-gray-700">
                                {{ customer.note ? customer.note : 'Không có' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="!props.isLoading && paginatedCustomers.length === 0"
            class="text-center text-gray-500 py-4 md:hidden">
            Không có dữ liệu
        </div>

        <!-- Simple Pagination -->
        <div v-if="!props.isLoading && totalPages > 1"
            class="flex flex-col sm:flex-row sm:justify-between sm:items-center mt-6 gap-3">
            <div class="text-sm text-gray-600 text-center sm:text-left">
                Hiển thị {{ paginatedCustomers.length }} trên tổng số {{ filteredCustomers.length }} bản ghi
            </div>
            <div class="flex justify-center gap-2">
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

        <!-- Customer Edit Modal -->
        <CustomerEditModal :is-visible="isEditModalVisible" :customer="selectedCustomer"
            :currentUserRole="props.currentUserRole" @close="closeEditModal" @save="handleSaveCustomer" />

        <!-- Customer Create Modal -->
        <CustomerCreateModal :is-visible="isCreateModalVisible" :currentUserRole="props.currentUserRole"
            @close="closeCreateModal" @save="handleCreateCustomer" />
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import CustomerEditModal from './CustomerEditModal.vue'
import CustomerCreateModal from './CustomerCreateModal.vue'
import { useAuth } from '../../../composable/useAuth'
import Swal from 'sweetalert2'

const { updateCustomerStatus } = useAuth()

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
    },
    currentUserRole: {
        type: String,
        default: 'user'
    }
})

const defaultAvatar = ref('https://img.freepik.com/premium-vector/error-404-found-glitch-effect_8024-4.jpg')
const emit = defineEmits(['delete', 'page-change', 'update-customer', 'toggle-status', 'create-customer'])

const searchQuery = ref('')
const filterStatus = ref('')
const filterRole = ref('')

// Modal state
const isEditModalVisible = ref(false)
const isCreateModalVisible = ref(false)
const selectedCustomer = ref({})

const filteredCustomers = computed(() => {
    return props.customers.filter(customer => {
        const matchesSearch =
            (customer.username?.toLowerCase().includes(searchQuery.value.toLowerCase()) || '') ||
            (customer.email?.toLowerCase().includes(searchQuery.value.toLowerCase()) || '') ||
            (customer.phone?.includes(searchQuery.value) || '')

        const matchesStatus = filterStatus.value === '' || customer.status?.toString() === filterStatus.value
        const matchesRole = filterRole.value === '' || customer.role === filterRole.value

        return matchesSearch && matchesStatus && matchesRole
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

const handleDelete = async (customer) => {
    try {
        const result = await Swal.fire({
            title: 'Bạn có chắc chắn?',
            text: `Bạn có muốn xóa khách hàng "${customer.username || customer.email}" không?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy'
        })

        if (result.isConfirmed) {
            emit('delete', customer)
        }
    } catch (error) {
        console.error('Lỗi khi hiển thị dialog xác nhận:', error)
    }
}

const toggleStatus = async (customer) => {
    const newStatus = customer.status === 1 ? 0 : 1
    try {
        emit('toggle-status', customer)
    } catch (e) {
    }
}

const openEditModal = (customer) => {
    selectedCustomer.value = { ...customer }
    isEditModalVisible.value = true
}

const closeEditModal = () => {
    isEditModalVisible.value = false
    selectedCustomer.value = {}
}

const handleSaveCustomer = async (customerData) => {
    try {
        emit('update-customer', customerData)

        closeEditModal()

    } catch (error) {
        console.error('Error updating customer:', error)
    }
}

const openCreateModal = () => {
    isCreateModalVisible.value = true
}

const closeCreateModal = () => {
    isCreateModalVisible.value = false
}

const handleCreateCustomer = async (customerData) => {
    try {
        emit('create-customer', customerData)
        closeCreateModal()
    } catch (error) {
        console.error('Error creating customer:', error)
    }
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