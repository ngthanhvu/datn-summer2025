<template>
    <div>
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
            <div>
                <h2 class="tw-text-3xl tw-font-bold">Danh sách khách hàng</h2>
                <p class="tw-text-gray-400 tw-mt-1">Quản lý danh sách khách hàng</p>
            </div>
        </div>
        <Table :items="users" :columns="columns" :show-filters="false" :categories="categories" :statuses="statuses"
            :show-date-filter="true" :selectable="true" @edit="handleEdit" @delete="handleDelete"
            @selection-change="handleSelectionChange" @filter-change="handleFilterChange" />
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin'
})
useHead({
    title: 'Quản lý khách hàng',
    meta: [
        { name: 'description', content: 'Quản lý khách hàng' }
    ]
})
import { useAuth } from '~/composables/useAuth'
const { getListUser } = useAuth()

const users = ref([])

const columns = ref([
    { key: 'username', label: 'Tài khoản' },
    { key: 'email', label: 'Email' },
    { key: 'role', label: 'Vai trò' },
    { key: 'createdAt', label: 'Ngày tạo' },
])

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    })
}

onMounted(async () => {
    const res = await getListUser()
    if (res && res.users) {
        users.value = res.users.map(user => ({
            ...user,
            createdAt: formatDate(user.created_at)
        }))
    }
})
</script>

<style></style>