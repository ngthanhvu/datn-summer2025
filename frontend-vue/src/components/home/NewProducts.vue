<template>
    <div class="mt-3 bg-white p-8 rounded-[5px]">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-2xl font-semibold text-gray-800">Sản phẩm mới</h2>
            <router-link to="/products/new" class="text-blue-600 hover:text-blue-800 font-medium transition-colors">
                Xem tất cả →
            </router-link>
        </div>

        <!-- Loading -->
        <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <div v-for="i in 5" :key="i" class="bg-white rounded-lg shadow-sm overflow-hidden animate-pulse">
                <div class="h-80 bg-gray-200"></div>
                <div class="p-4">
                    <div class="h-4 bg-gray-200 rounded mb-2"></div>
                    <div class="h-4 bg-gray-200 rounded mb-2"></div>
                    <div class="h-6 bg-gray-200 rounded mb-2"></div>
                    <div class="h-8 bg-gray-200 rounded"></div>
                </div>
            </div>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="text-center py-8">
            <div class="text-red-500 mb-4">{{ error }}</div>
            <button @click="fetchNewProducts" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Thử lại
            </button>
        </div>

        <!-- Product Grid -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <Card v-for="product in products" :key="product.id" :product="product" />
        </div>

        <!-- Empty -->
        <div v-if="!isLoading && !error && products.length === 0" class="text-center py-8">
            <p class="text-gray-500">Chưa có sản phẩm mới</p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Card from '../ui/Card.vue'
import { useProductStore } from '../../stores/products'

const productStore = useProductStore()

const isLoading = computed(() => productStore.loading)
const error = computed(() => productStore.error && productStore.error.message ? productStore.error.message : productStore.error)

onMounted(() => {
    fetchNewProducts()
})

function fetchNewProducts() {
    productStore.fetchProducts()
}

// Lấy 5 sản phẩm mới nhất (giả sử API trả về đã sort theo mới nhất, hoặc sort ở đây)
const products = computed(() => {
    // Nếu muốn sort theo id giảm dần (mới nhất):
    return [...productStore.products].sort((a, b) => b.id - a.id).slice(0, 5)
})
</script>
