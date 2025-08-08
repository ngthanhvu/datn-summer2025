<template>
    <div class="pages-page">
        <div class="page-header flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">Quản lý trang</h1>
                <p class="text-sm sm:text-base text-gray-600">Quản lý các trang chính sách và hỗ trợ khách hàng</p>
            </div>
            <button @click="handleRefresh"
                class="inline-flex items-center px-3 sm:px-4 py-2 bg-gray-600 text-white text-xs sm:text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
                Tải lại
            </button>
        </div>

        <PagesTable :isLoading="isLoading" />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import PagesTable from './PagesTable.vue'

const isLoading = ref(true)

const handleRefresh = () => {
    isLoading.value = true
    setTimeout(() => {
        isLoading.value = false
    }, 1500)
}

onMounted(() => {
    setTimeout(() => {
        isLoading.value = false
    }, 1500)
})
</script>

<style scoped>
.pages-page {
    padding: 1rem;
}

@media (min-width: 640px) {
    .pages-page {
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
</style> 