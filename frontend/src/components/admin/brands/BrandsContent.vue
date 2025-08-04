<template>
    <div class="brands-page">
        <div class="page-header flex justify-between items-center">
            <div>
                <h1>Quản lý thương hiệu</h1>
                <p class="text-gray-600">Quản lý thương hiệu sản phẩm của bạn</p>
            </div>
            <div class="flex gap-3">
                <button @click="handleRefresh"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                    Tải lại
                </button>
                <router-link to="/admin/brands/create"
                    class="bg-primary text-white rounded px-4 py-2 flex items-center gap-2 hover:bg-primary-dark">
                    <i class="fas fa-plus"></i>
                    Thêm thương hiệu
                </router-link>
            </div>
        </div>

        <!-- <BrandsTable :brands="brands" :isLoading="isLoading" @delete="handleDelete" @bulk-delete="handleBulkDelete" /> -->
        <BrandsTable :brands="brands" :is-loading="isLoading" :current-page="currentPage" :items-per-page="itemsPerPage"
            @update:currentPage="currentPage = $event" @delete="handleDelete" @bulk-delete="handleBulkDelete" />

    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useBrandStore } from '../../../stores/brands'
import BrandsTable from './BrandsTable.vue'

const brandStore = useBrandStore()

const currentPage = ref(1)
const itemsPerPage = 10

const brands = computed(() => brandStore.brands)
const isLoading = computed(() => brandStore.loading)

const handleDelete = async (brand) => { }
const handleBulkDelete = async (selectedBrands) => { }
const handleRefresh = async () => {
    await brandStore.fetchBrands()
}

onMounted(async () => {
    await brandStore.fetchBrands()
})
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

.bg-primary {
    background-color: #3bb77e;
}

.bg-primary-dark {
    background-color: #2ea16d;
}
</style>