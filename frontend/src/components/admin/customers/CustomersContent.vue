<template>
    <div class="customers-page">
        <div class="page-header flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
            <div>
                <h1>Quản lý khách hàng</h1>
                <p class="text-gray-600">Quản lý danh sách khách hàng của bạn</p>
            </div>
            <button @click="handleRefresh"
                class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
                Tải lại
            </button>
        </div>

        <CustomersTable :customers="sortedCustomers" :isLoading="isLoading" :currentPage="currentPage"
            :itemsPerPage="itemsPerPage" :totalItems="totalItems" :currentUserRole="currentUserRole"
            @delete="handleDelete" @page-change="handlePageChange" @update-customer="handleUpdateCustomer"
            @toggle-status="handleToggleStatus" @create-customer="handleCreateCustomer" />
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import CustomersTable from './CustomersTable.vue'
import { useAuth } from '../../../composable/useAuth'
import { push } from 'notivue'
import Swal from 'sweetalert2'

const { getListUser, updateUserByAdmin, updateCustomerStatus, deleteUser, createUserByAdmin, user } = useAuth()
const customers = ref([])
const isLoading = ref(true)

// Pagination state
const currentPage = ref(1)
const itemsPerPage = ref(10)
const totalItems = ref(0)

const currentUserRole = ref('user')

const sortedCustomers = computed(() => {
    return [...customers.value].sort((a, b) => {
        const rolePriority = {
            'master_admin': 3,
            'admin': 2,
            'user': 1
        }

        const aPriority = rolePriority[a.role] || 0
        const bPriority = rolePriority[b.role] || 0

        if (aPriority !== bPriority) {
            return bPriority - aPriority
        }

        return a.id - b.id
    })
})

const showErrorAlert = (errorMessage) => {
    const errorConfigs = {
        'không thể xóa chính bản thân': {
            title: 'Không thể xóa tài khoản',
            text: 'Bạn không thể xóa chính bản thân!'
        },
        'không thể thay đổi vai trò của chính bản thân': {
            title: 'Không thể thay đổi vai trò',
            text: 'Bạn không thể thay đổi vai trò của chính bản thân!'
        },
        'Chỉ Master Admin mới có quyền tạo tài khoản Master Admin': {
            title: 'Không có quyền',
            text: 'Chỉ Master Admin mới có quyền tạo tài khoản Master Admin!'
        },
        'Admin thường không thể tạo tài khoản Admin khác': {
            title: 'Không có quyền',
            text: 'Admin thường không thể tạo tài khoản Admin khác!'
        },
        'Không thể thay đổi thông tin của Master Admin': {
            title: 'Không có quyền',
            text: 'Không thể thay đổi thông tin của Master Admin!'
        },
        'Chỉ Master Admin mới có quyền thay đổi role thành Master Admin': {
            title: 'Không có quyền',
            text: 'Chỉ Master Admin mới có quyền thay đổi role thành Master Admin!'
        },
        'Chỉ Master Admin mới có quyền xóa Master Admin khác': {
            title: 'Không có quyền',
            text: 'Chỉ Master Admin mới có quyền xóa Master Admin khác!'
        },
        'Không thể xóa Master Admin cuối cùng trong hệ thống': {
            title: 'Không thể xóa',
            text: 'Không thể xóa Master Admin cuối cùng trong hệ thống!'
        }
    }

    for (const [key, config] of Object.entries(errorConfigs)) {
        if (errorMessage.includes(key)) {
            Swal.fire({
                icon: 'warning',
                title: config.title,
                text: config.text,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Đã hiểu'
            })
            return true
        }
    }

    push.error(errorMessage)
    return false
}

onMounted(async () => {
    isLoading.value = true
    try {
        const res = await getListUser()
        customers.value = res.users
        totalItems.value = res.users.length

        if (user.value) {
            currentUserRole.value = user.value.role
        }
    } catch (err) {
        console.error('Get list user error:', err.response?.data || err.message)
        throw err
    } finally {
        isLoading.value = false
    }
})

const handleDelete = async (customer) => {
    try {
        await deleteUser(customer.id)

        const index = customers.value.findIndex(c => c.id === customer.id)
        if (index !== -1) {
            customers.value.splice(index, 1)
            totalItems.value = customers.value.length
        }

        push.success('Xóa khách hàng thành công')
    } catch (error) {
        console.error('Lỗi khi xóa khách hàng:', error)

        const errorMessage = error.response?.data?.error || 'Có lỗi xảy ra khi xóa khách hàng'
        showErrorAlert(errorMessage)
    }
}

const handleUpdateCustomer = async (customerData) => {
    try {
        await updateUserByAdmin(customerData)

        const index = customers.value.findIndex(c => c.id === customerData.id)
        if (index !== -1) {
            customers.value[index] = {
                ...customers.value[index],
                ...customerData
            }
        }
        push.success('Cập nhật khách hàng thành công')

    } catch (error) {
        console.error('Lỗi khi cập nhật khách hàng:', error)

        const errorMessage = error.response?.data?.error || 'Có lỗi xảy ra khi cập nhật khách hàng'
        showErrorAlert(errorMessage)
    }
}

const handleCreateCustomer = async (customerData) => {
    try {
        const response = await createUserByAdmin(customerData)

        // Add new customer to the list
        customers.value.unshift(response.user)
        totalItems.value = customers.value.length

        push.success('Tạo người dùng thành công')
    } catch (error) {
        console.error('Lỗi khi tạo người dùng:', error)

        const errorMessage = error.response?.data?.error || 'Có lỗi xảy ra khi tạo người dùng'
        showErrorAlert(errorMessage)
    }
}

const handleToggleStatus = async (customer) => {
    try {
        const newStatus = customer.status === 1 ? 0 : 1
        await updateCustomerStatus(customer.id, newStatus)

        const index = customers.value.findIndex(c => c.id === customer.id)
        if (index !== -1) {
            customers.value[index].status = newStatus
        }

        push.success('Cập nhật trạng thái khách hàng thành công')
    } catch (error) {
        console.error('Lỗi khi cập nhật trạng thái khách hàng:', error)

        const errorMessage = error.response?.data?.error || 'Có lỗi xảy ra khi cập nhật trạng thái khách hàng'
        showErrorAlert(errorMessage)
    }
}

const handlePageChange = (page) => {
    currentPage.value = page
}

const handleRefresh = async () => {
    isLoading.value = true
    currentPage.value = 1 // Reset to first page
    try {
        const res = await getListUser()
        customers.value = res.users
        totalItems.value = res.users.length
        // sortedCustomers will automatically update due to computed property
    } catch (err) {
        console.error('Get list user error:', err.response?.data || err.message)
        throw err
    } finally {
        isLoading.value = false
    }
}
</script>

<style scoped>
.customers-page {
    padding: 1.5rem;
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    font-size: 1.875rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.5rem;
}

@media (max-width: 640px) {
    .page-header h1 {
        font-size: 1.5rem;
    }
}
</style>