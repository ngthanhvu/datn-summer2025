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

        <CustomersTable :customers="customers" :isLoading="isLoading" :currentPage="currentPage"
            :itemsPerPage="itemsPerPage" :totalItems="totalItems" @delete="handleDelete" @page-change="handlePageChange"
            @update-customer="handleUpdateCustomer" @toggle-status="handleToggleStatus" />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import CustomersTable from './CustomersTable.vue'
import { useAuth } from '../../../composable/useAuth'
import { push } from 'notivue'

const { getListUser, updateUserByAdmin, updateCustomerStatus } = useAuth()
const customers = ref([])
const isLoading = ref(true)

// Pagination state
const currentPage = ref(1)
const itemsPerPage = ref(10)
const totalItems = ref(0)

onMounted(async () => {
    isLoading.value = true
    try {
        const res = await getListUser()
        customers.value = res.users
        totalItems.value = res.users.length
    } catch (err) {
        console.error('Get list user error:', err.response?.data || err.message)
        throw err
    } finally {
        isLoading.value = false
    }
})

const handleDelete = (customer) => {
    const index = customers.value.findIndex(c => c.id === customer.id)
    if (index !== -1) {
        customers.value.splice(index, 1)
        totalItems.value = customers.value.length
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
        // console.log('Cập nhật khách hàng thành công:', customerData)

    } catch (error) {
        console.error('Lỗi khi cập nhật khách hàng:', error)
    }
}

const handleToggleStatus = async (customer) => {
    try {
        const newStatus = customer.status === 1 ? 0 : 1
        await updateCustomerStatus(customer.id, newStatus)

        // Cập nhật dữ liệu local
        const index = customers.value.findIndex(c => c.id === customer.id)
        if (index !== -1) {
            customers.value[index].status = newStatus
        }

        console.log('Cập nhật trạng thái khách hàng thành công')
    } catch (error) {
        console.error('Lỗi khi cập nhật trạng thái khách hàng:', error)
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