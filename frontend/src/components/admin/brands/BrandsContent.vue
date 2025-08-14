<template>
    <div class="brands-page">
        <div class="page-header flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="header-content">
                <h1>Quản lý thương hiệu</h1>
                <p class="text-gray-600">Quản lý thương hiệu sản phẩm của bạn</p>
            </div>
            <div class="flex gap-2 sm:gap-3 w-full sm:w-auto">
                <button @click="handleRefresh"
                    class="inline-flex items-center px-3 sm:px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200 flex-1 sm:flex-none justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                    <span class="hidden sm:inline">Tải lại</span>
                    <span class="sm:hidden">Tải</span>
                </button>
                <router-link to="/admin/brands/create"
                    class="bg-primary text-white rounded px-3 sm:px-4 py-2 flex items-center gap-2 hover:bg-primary-dark flex-1 sm:flex-none justify-center">
                    <i class="fas fa-plus"></i>
                    <span class="hidden sm:inline">Thêm thương hiệu</span>
                    <span class="sm:hidden">Thêm</span>
                </router-link>
            </div>
        </div>

        <!-- <BrandsTable :brands="brands" :isLoading="isLoading" @delete="handleDelete" @bulk-delete="handleBulkDelete" /> -->
        <BrandsTable :brands="brands" :is-loading="isLoading" :current-page="currentPage" :items-per-page="itemsPerPage"
            @update:currentPage="currentPage = $event" @delete="handleDelete" @bulk-delete="handleBulkDelete"
            @refresh="handleRefresh" />

    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useBrandStore } from '../../../stores/brands'
import { useBrand } from '../../../composable/useBrand'
import { push } from 'notivue'
import BrandsTable from './BrandsTable.vue'

const { deleteBrand, bulkDeleteBrands } = useBrand()
const brandStore = useBrandStore()

const currentPage = ref(1)
const itemsPerPage = 10

const brands = computed(() => brandStore.brands)
const isLoading = computed(() => brandStore.loading)

const handleDelete = async (brand) => {
    try {
        await deleteBrand(brand.id)
        push.success('Xoá thương hiệu thành công')
        // Cập nhật store để UI tự động refresh
        await brandStore.fetchBrands()
    } catch (error) {
        console.error('Failed to delete brand:', error)
        push.error('Xoá thương hiệu thất bại')
    }
}

const handleBulkDelete = async (selectedBrands) => {
    try {
        // Convert Set to Array
        const ids = Array.from(selectedBrands)
        await bulkDeleteBrands(ids)
        push.success('Xoá thương hiệu hàng loạt thành công')
        // Cập nhật store
        await brandStore.fetchBrands()
    } catch (error) {
        console.error('Failed to bulk delete brands:', error)
        push.error('Xoá thương hiệu hàng loạt thất bại')
    }
}

const handleRefresh = async () => {
    await brandStore.fetchBrands()
}

onMounted(async () => {
    await brandStore.fetchBrands()
})
</script>


<style scoped>
.brands-page {
    padding: 1rem;
}

@media (min-width: 640px) {
    .brands-page {
        padding: 1.5rem;
    }
}

.page-header {
    margin-bottom: 1.5rem;
}

@media (min-width: 640px) {
    .page-header {
        margin-bottom: 2rem;
    }
}

.page-header h1 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.5rem;
}

@media (min-width: 640px) {
    .page-header h1 {
        font-size: 1.875rem;
    }
}

.bg-primary {
    background-color: #3bb77e;
}

.bg-primary-dark {
    background-color: #2ea16d;
}

/* Mobile-specific improvements */
@media (max-width: 640px) {
    .header-content h1 {
        font-size: 1.875rem;
        margin-bottom: 0.5rem;
    }

    .page-header {
        gap: 1rem;
    }

    .page-header>div:first-child {
        text-align: center;
    }

    .page-header>div:last-child {
        width: 100%;
    }
}

/* Touch-friendly improvements */
@media (hover: none) and (pointer: coarse) {

    button,
    a {
        min-height: 44px;
        /* iOS recommended touch target size */
    }
}
</style>