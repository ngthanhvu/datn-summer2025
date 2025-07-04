<template>
    <div class="customers-page">
        <div class="page-header tw-flex tw-justify-between tw-items-center">
            <div>
                <h1>Quản lý khách hàng</h1>
                <p class="text-gray-600">Quản lý danh sách khách hàng của bạn</p>
            </div>
            <button @click="handleRefresh"
                class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-gray-600 tw-text-white tw-text-sm tw-font-medium tw-rounded-lg hover:tw-bg-gray-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-gray-500 focus:tw-ring-offset-2 tw-transition-colors tw-duration-200">
                <svg class="tw-w-4 tw-h-4 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
                Tải lại
            </button>
        </div>

        <CustomersTable :customers="customers" :isLoading="isLoading" @delete="handleDelete" />
    </div>
</template>

<script setup>
useHead({
    title: "Quản lý khách hàng",
    meta: [
        { name: "description", content: "Quản lý danh sách khách hàng của bạn" }
    ]
})
definePageMeta({
    layout: 'admin',
    middleware: 'admin'
})
import CustomersTable from '~/components/admin/customers/CustomersTable.vue'
import { useAuth } from '~/composables/useAuth'

const { getListUser } = useAuth()
const customers = ref([])
const isLoading = ref(true)

onMounted(async () => {
    isLoading.value = true
    try {
        const res = await getListUser()
        customers.value = res.users
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
    }
}

const handleRefresh = async () => {
    isLoading.value = true
    try {
        const res = await getListUser()
        customers.value = res.users
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
</style>