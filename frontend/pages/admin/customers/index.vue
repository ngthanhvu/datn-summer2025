<template>
    <div class="customers-page">
        <div class="page-header">
            <h1>Quản lý khách hàng</h1>
            <p class="text-gray-600">Quản lý danh sách khách hàng của bạn</p>
        </div>

        <CustomersTable :customers="customers" @delete="handleDelete" />
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

onMounted(async () => {
    try {
        const res = await getListUser()
        customers.value = res.users
        console.log(customers.value)
    } catch (err) {
        console.error('Get list user error:', err.response?.data || err.message)
        throw err
    }
})

const handleDelete = (customer) => {
    const index = customers.value.findIndex(c => c.id === customer.id)
    if (index !== -1) {
        customers.value.splice(index, 1)
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