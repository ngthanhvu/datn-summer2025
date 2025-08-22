<template>
    <div class="mt-3 bg-white p-4 md:p-8 rounded-[10px]">
        <div class="flex justify-between items-center mb-4 md:mb-6">
            <h2 class="text-lg md:text-2xl font-semibold text-gray-800">Sản phẩm theo danh mục</h2>
        </div>

        <!-- Category Tabs -->
        <div class="flex flex-wrap gap-2 mb-4 md:mb-6">
            <button @click="selectCategory(null)" :class="[
                'px-3 md:px-4 py-1.5 md:py-2 rounded-full text-xs md:text-sm font-medium transition-colors cursor-pointer',
                selectedCategory === null
                    ? 'bg-[#81aacc] text-white'
                    : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-200'
            ]">
                Tất cả sản phẩm
            </button>
            <button v-for="category in categories" :key="category.id" @click="selectCategory(category.id)" :class="[
                'px-3 md:px-4 py-1.5 md:py-2 rounded-full text-xs md:text-sm font-medium transition-colors cursor-pointer',
                selectedCategory === category.id
                    ? 'bg-[#81aacc] text-white'
                    : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-200'
            ]">
                {{ category.name }}
            </button>
        </div>

        <!-- Loading State -->
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

        <!-- Products -->
        <div v-else
            class="flex gap-4 overflow-x-auto scroll-smooth md:grid md:grid-cols-1 md:sm:grid-cols-2 md:lg:grid-cols-4 md:xl:grid-cols-5 md:gap-4">
            <div v-for="product in filteredProducts.slice(0, 5)" :key="product.id" class="flex-shrink-0 w-64 md:w-auto">
                <Card :product="product" @quick-view="onQuickView" />
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!isLoading && filteredProducts.length === 0" class="text-center py-8">
            <p class="text-gray-500">Không có sản phẩm nào trong danh mục này</p>
        </div>

        <!-- Quick View Modal -->
        <QuickView :show="showQuickView" :product="selectedProduct" @close="showQuickView = false"
            @add-to-cart="handleAddToCart" />
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import Card from '../ui/Card.vue'
import QuickView from '../products/Quick-view.vue'
import { useProductStore } from '../../stores/products'
import { useCategoryStore } from '../../stores/categories'

const productStore = useProductStore()
const categoryStore = useCategoryStore()

const categories = computed(() => categoryStore.categories)
const products = computed(() => productStore.products)

const selectedCategory = ref(null)
const isLoading = ref(false)
const showQuickView = ref(false)
const selectedProduct = ref(null)

const filteredProducts = computed(() => {
    if (!selectedCategory.value) return products.value
    return products.value.filter(p => p.categories_id === selectedCategory.value)
})

const selectCategory = (id) => {
    selectedCategory.value = id
}

const onQuickView = (product) => {
    selectedProduct.value = product
    showQuickView.value = true
}

const handleAddToCart = () => {
    // Xử lý sau khi thêm vào giỏ hàng
    showQuickView.value = false
}
</script>
