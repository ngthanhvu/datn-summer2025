<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow">
        <div class="tw-p-4 tw-border-b">
            <div class="tw-flex tw-justify-between tw-items-center">
                <div class="tw-relative">
                    <input type="text" v-model="searchQuery" placeholder="Tìm kiếm..."
                        class="tw-border tw-rounded tw-px-4 tw-py-2 tw-pl-10 tw-w-64">
                    <i
                        class="fas fa-search tw-absolute tw-left-3 tw-top-1/2 tw-transform -tw-translate-y-1/2 tw-text-gray-400"></i>
                </div>
                <select v-model="filterStatus" class="tw-border tw-rounded tw-px-4 tw-py-2">
                    <option value="">Tất cả trạng thái</option>
                    <option value="true">Đang hoạt động</option>
                    <option value="false">Đã khóa</option>
                </select>
            </div>
        </div>

        <div class="tw-overflow-x-auto">
            <table class="tw-w-full">
                <thead>
                    <tr class="tw-bg-gray-50">
                        <th
                            class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            ID
                        </th>
                        <th
                            class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Ảnh
                        </th>
                        <th
                            class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Họ tên
                        </th>
                        <th
                            class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Email
                        </th>
                        <th
                            class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Số điện thoại
                        </th>
                        <th
                            class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Số đơn hàng
                        </th>
                        <th
                            class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Tổng chi tiêu
                        </th>
                        <th
                            class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Trạng thái
                        </th>
                        <th
                            class="tw-px-4 tw-py-3 tw-text-right tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Thao tác
                        </th>
                    </tr>
                </thead>
                <tbody class="tw-divide-y tw-divide-gray-200">
                    <tr v-for="customer in filteredCustomers" :key="customer.id" class="tw-hover:tw-bg-gray-50">
                        <td class="tw-px-4 tw-py-3 tw-text-sm tw-text-gray-900">{{ customer.id }}</td>
                        <td class="tw-px-4 tw-py-3">
                            <img :src="customer.avatar" :alt="customer.name"
                                class="tw-w-10 tw-h-10 tw-rounded-full tw-object-cover">
                        </td>
                        <td class="tw-px-4 tw-py-3 tw-text-sm tw-text-gray-900">{{ customer.name }}</td>
                        <td class="tw-px-4 tw-py-3 tw-text-sm tw-text-gray-900">{{ customer.email }}</td>
                        <td class="tw-px-4 tw-py-3 tw-text-sm tw-text-gray-900">{{ customer.phone }}</td>
                        <td class="tw-px-4 tw-py-3 tw-text-sm tw-text-gray-900">{{ customer.totalOrders }}</td>
                        <td class="tw-px-4 tw-py-3 tw-text-sm tw-text-gray-900">
                            {{ formatPrice(customer.totalSpent) }}
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            <span :class="[
                                'tw-px-2 tw-py-1 tw-rounded-full tw-text-xs',
                                customer.status
                                    ? 'tw-bg-green-100 tw-text-green-700'
                                    : 'tw-bg-red-100 tw-text-red-700'
                            ]">
                                {{ customer.status ? 'Đang hoạt động' : 'Đã khóa' }}
                            </span>
                        </td>
                        <td class="tw-px-4 tw-py-3 tw-text-right tw-text-sm tw-font-medium">
                            <div class="tw-flex tw-justify-end tw-gap-2">
                                <NuxtLink :to="`/admin/customers/${customer.id}`"
                                    class="tw-text-primary tw-hover:tw-text-primary-dark">
                                    <i class="fas fa-edit"></i>
                                </NuxtLink>
                                <button @click="handleDelete(customer)"
                                    class="tw-text-red-600 tw-hover:tw-text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
    customers: {
        type: Array,
        required: true
    }
})

const emit = defineEmits(['delete'])

const searchQuery = ref('')
const filterStatus = ref('')

const filteredCustomers = computed(() => {
    return props.customers.filter(customer => {
        const matchesSearch = customer.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            customer.email.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            customer.phone.includes(searchQuery.value)
        const matchesStatus = filterStatus.value === '' || customer.status.toString() === filterStatus.value
        return matchesSearch && matchesStatus
    })
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}

const handleDelete = (customer) => {
    if (confirm('Bạn có chắc chắn muốn xóa khách hàng này?')) {
        emit('delete', customer)
    }
}
</script>

<style scoped>
.tw-text-primary {
    color: #3bb77e;
}

.tw-text-primary-dark {
    color: #2d9d6a;
}
</style>