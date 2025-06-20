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
                            class="tw-px-4 tw-py-3 tw-text-center tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            ID
                        </th>
                        <th
                            class="tw-px-4 tw-py-3 tw-text-center tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Ảnh
                        </th>
                        <th
                            class="tw-px-4 tw-py-3 tw-text-center tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Tên người dùng
                        </th>
                        <th
                            class="tw-px-4 tw-py-3 tw-text-center tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Email
                        </th>
                        <th
                            class="tw-px-4 tw-py-3 tw-text-center tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Số điện thoại
                        </th>
                        <th
                            class="tw-px-4 tw-py-3 tw-text-center tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Trạng thái
                        </th>
                        <th
                            class="tw-px-4 tw-py-3 tw-text-center tw-text-xs tw-font-medium tw-text-gray-500 tw-uppercase">
                            Thao tác
                        </th>
                    </tr>
                </thead>
                <tbody class="tw-divide-y tw-divide-gray-200">
                    <tr v-for="customer in filteredCustomers" :key="customer.id" class="tw-hover:tw-bg-gray-50">
                        <td class="tw-px-4 tw-py-3 tw-text-sm tw-text-gray-900 tw-text-center">{{ customer.id }}</td>
                        <td class="tw-px-4 tw-py-3 tw-text-center">
                            <img :src="customer.avatar || defaultAvatar" :alt="customer.username"
                                class="tw-w-10 tw-h-10 tw-rounded-full tw-object-cover tw-mx-auto" />
                        </td>
                        <td class="tw-px-4 tw-py-3 tw-text-sm tw-text-gray-900 tw-text-center">{{ customer.username }}
                        </td>
                        <td class="tw-px-4 tw-py-3 tw-text-sm tw-text-gray-900 tw-text-center">{{ customer.email }}</td>
                        <td class="tw-px-4 tw-py-3 tw-text-sm tw-text-gray-900 tw-text-center">{{ customer.phone ?
                            customer.phone : 'Không có' }}</td>
                        <td class="tw-px-4 tw-py-3 tw-text-center">
                            <span :class="[
                                'tw-px-2 tw-py-1 tw-rounded-full tw-text-xs',
                                customer.status
                                    ? 'tw-bg-green-100 tw-text-green-700'
                                    : 'tw-bg-red-100 tw-text-red-700'
                            ]">
                                {{ customer.status ? 'Đang hoạt động' : 'Đã khóa' }}
                            </span>
                        </td>
                        <td class="tw-px-4 tw-py-3 tw-text-sm tw-font-medium">
                            <div class="tw-flex tw-items-center tw-justify-center tw-gap-2">
                                <NuxtLink :to="`/admin/customers/${customer.id}`"
                                    class="tw-inline-flex tw-items-center tw-p-1.5 tw-text-blue-600 hover:tw-text-blue-900 hover:tw-bg-blue-50 tw-rounded-lg tw-transition-colors tw-duration-150"
                                    title="Xem/Chỉnh sửa khách hàng">
                                    <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                </NuxtLink>
                                <button @click="handleDelete(customer)"
                                    class="tw-inline-flex tw-items-center tw-p-1.5 tw-text-red-600 hover:tw-text-red-900 hover:tw-bg-red-50 tw-rounded-lg tw-transition-colors tw-duration-150"
                                    title="Xóa khách hàng">
                                    <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredCustomers.length === 0">
                        <td colspan="8" class="tw-text-center tw-text-gray-500">Không có dữ liệu</td>
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

const defaultAvatar = ref('https://img.freepik.com/premium-vector/error-404-found-glitch-effect_8024-4.jpg')
const emit = defineEmits(['delete'])

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