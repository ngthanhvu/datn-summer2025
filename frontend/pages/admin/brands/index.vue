<template>
    <div class="brands-page">
        <div class="page-header tw-flex tw-justify-between tw-items-center">
            <div>
                <h1>Quản lý thương hiệu</h1>
                <p class="text-gray-600">Quản lý thương hiệu sản phẩm của bạn</p>
            </div>
            <NuxtLink to="/admin/brands/create"
                class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-primary-dark">
                <i class="fas fa-plus"></i>
                Thêm thương hiệu
            </NuxtLink>
        </div>

        <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
            <div class="tw-overflow-x-auto">
                <table class="tw-w-full">
                    <thead>
                        <tr class="tw-border-b tw-bg-gray-50">
                            <th class="tw-px-4 tw-py-3 tw-text-left">ID</th>
                            <th class="tw-px-4 tw-py-3 tw-text-left">Logo</th>
                            <th class="tw-px-4 tw-py-3 tw-text-left">Tên thương hiệu</th>
                            <th class="tw-px-4 tw-py-3 tw-text-left">Mô tả</th>
                            <th class="tw-px-4 tw-py-3 tw-text-left">Số sản phẩm</th>
                            <th class="tw-px-4 tw-py-3 tw-text-left">Trạng thái</th>
                            <th class="tw-px-4 tw-py-3 tw-text-left">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="brand in brands" :key="brand.id" class="tw-border-b hover:tw-bg-gray-50">
                            <td class="tw-px-4 tw-py-3">#{{ brand.id }}</td>
                            <td class="tw-px-4 tw-py-3">
                                <img :src="brand.logo" :alt="brand.name"
                                    class="tw-w-10 tw-h-10 tw-object-cover tw-rounded">
                            </td>
                            <td class="tw-px-4 tw-py-3">{{ brand.name }}</td>
                            <td class="tw-px-4 tw-py-3">{{ brand.description }}</td>
                            <td class="tw-px-4 tw-py-3">{{ brand.productCount }}</td>
                            <td class="tw-px-4 tw-py-3">
                                <span :class="[
                                    'tw-px-2 tw-py-1 tw-rounded-full tw-text-xs',
                                    brand.status ? 'tw-bg-green-100 tw-text-green-700' : 'tw-bg-red-100 tw-text-red-700'
                                ]">
                                    {{ brand.status ? 'Hoạt động' : 'Vô hiệu' }}
                                </span>
                            </td>
                            <td class="tw-px-4 tw-py-3">
                                <div class="tw-flex tw-gap-2">
                                    <NuxtLink :to="`/admin/brands/${brand.id}/edit`"
                                        class="tw-bg-blue-500 tw-text-white tw-rounded tw-px-2 tw-py-1 hover:tw-bg-blue-600">
                                        <i class="fas fa-edit"></i>
                                    </NuxtLink>
                                    <button @click="handleDelete(brand)"
                                        class="tw-bg-red-500 tw-text-white tw-rounded tw-px-2 tw-py-1 hover:tw-bg-red-600">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin'
})

import { ref } from 'vue'

// Mock data
const brands = ref([
    {
        id: 1,
        logo: 'https://via.placeholder.com/150',
        name: 'Apple',
        description: 'Công ty công nghệ Apple Inc.',
        productCount: 12,
        status: true
    },
    {
        id: 2,
        logo: 'https://via.placeholder.com/150',
        name: 'Samsung',
        description: 'Tập đoàn Samsung Electronics',
        productCount: 18,
        status: true
    },
    {
        id: 3,
        logo: 'https://via.placeholder.com/150',
        name: 'Sony',
        description: 'Tập đoàn Sony Corporation',
        productCount: 8,
        status: true
    },
    {
        id: 4,
        logo: 'https://via.placeholder.com/150',
        name: 'LG',
        description: 'Tập đoàn LG Electronics',
        productCount: 6,
        status: false
    }
])

const handleDelete = (brand) => {
    if (confirm('Bạn có chắc chắn muốn xóa thương hiệu này?')) {
        const index = brands.value.findIndex(b => b.id === brand.id)
        if (index !== -1) {
            brands.value.splice(index, 1)
        }
    }
}
</script>

<style scoped>
.brands-page {
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

.tw-bg-primary {
    background-color: #3bb77e;
}

.tw-bg-primary-dark {
    background-color: #2ea16d;
}
</style>