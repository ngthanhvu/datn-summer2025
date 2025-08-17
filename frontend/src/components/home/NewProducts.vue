<template>
    <div class="mt-3 bg-white p-4 md:p-8 rounded-[5px]">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-lg md:text-2xl font-semibold text-gray-800">Sản phẩm mới</h2>
            <router-link to="/san-pham"
                class="text-blue-600 hover:text-blue-800 font-medium transition-colors text-sm md:text-base">
                Xem tất cả →
            </router-link>
        </div>

        <!-- Loading -->
        <div v-if="isLoading"
            class="flex gap-4 overflow-x-auto scroll-smooth md:grid md:grid-cols-1 md:sm:grid-cols-2 md:lg:grid-cols-4 md:xl:grid-cols-5 md:gap-4">
            <div v-for="i in 5" :key="i"
                class="bg-white rounded-lg shadow-sm overflow-hidden animate-pulse flex-shrink-0 w-64 md:w-auto">
                <div class="h-80 bg-gray-200"></div>
                <div class="p-4">
                    <div class="h-4 bg-gray-200 rounded mb-2"></div>
                    <div class="h-4 bg-gray-200 rounded mb-2"></div>
                    <div class="h-6 bg-gray-200 rounded mb-2"></div>
                    <div class="h-8 bg-gray-200 rounded"></div>
                </div>
            </div>
        </div>

        <!-- Product Grid -->
        <div v-else-if="!isLoading && products.length > 0"
            class="flex gap-4 overflow-x-auto scroll-smooth md:grid md:grid-cols-1 md:sm:grid-cols-2 md:lg:grid-cols-4 md:xl:grid-cols-5 md:gap-4">
            <div v-for="product in products" :key="product.id" class="flex-shrink-0 w-64 md:w-auto">
                <Card :product="product" @quick-view="openQuickView" />
            </div>
        </div>

        <!-- Empty -->
        <div v-else-if="!isLoading && products.length === 0" class="text-center py-8">
            <p class="text-gray-500">Chưa có sản phẩm mới</p>
        </div>
        <QuickView :show="showQuickView" :product="quickViewProduct" @close="closeQuickView" />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Card from '../ui/Card.vue'
import { useProductStore } from '../../stores/products'
import QuickView from '../products/Quick-view.vue'

const productStore = useProductStore()

const isLoading = computed(() => productStore.loading)
const error = computed(() => productStore.error && productStore.error.message ? productStore.error.message : productStore.error)

onMounted(() => {
    fetchNewProducts()
})

const showQuickView = ref(false)
const quickViewProduct = ref(null)

function openQuickView(product) {
    quickViewProduct.value = product
    showQuickView.value = true
}
function closeQuickView() {
    showQuickView.value = false
    quickViewProduct.value = null
}

function fetchNewProducts() {
    productStore.fetchProducts()
}

// Lấy 5 sản phẩm mới nhất (giả sử API trả về đã sort theo mới nhất, hoặc sort ở đây)
const products = computed(() => {
    const data = productStore.products || []  // Nếu undefined/null thì trả mảng rỗng
    return [...data].sort((a, b) => b.id - a.id).slice(0, 5)
})
</script>
