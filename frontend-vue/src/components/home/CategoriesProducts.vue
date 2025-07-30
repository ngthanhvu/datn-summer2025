<template>
    <div class="mt-3 bg-white p-8 rounded-[10px]">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Sản phẩm theo danh mục</h2>
        </div>

        <!-- Category Tabs -->
        <div class="flex flex-wrap gap-2 mb-6">
            <button @click="selectCategory(null)" :class="[
                'px-4 py-2 rounded-full text-sm font-medium transition-colors cursor-pointer',
                selectedCategory === null
                    ? 'bg-[#81aacc] text-white'
                    : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-200'
            ]">
                Tất cả sản phẩm
            </button>
            <button v-for="category in categories" :key="category.id" @click="selectCategory(category.id)" :class="[
                'px-4 py-2 rounded-full text-sm font-medium transition-colors cursor-pointer',
                selectedCategory === category.id
                    ? 'bg-[#81aacc] text-white'
                    : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-200'
            ]">
                {{ category.name }}
            </button>
        </div>

        <!-- Loading State -->
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

        <!-- Products -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <Card v-for="product in filteredProducts.slice(0, 5)" :key="product.id" :product="product" />
        </div>

        <!-- Empty State -->
        <div v-if="!isLoading && filteredProducts.length === 0" class="text-center py-8">
            <p class="text-gray-500">Không có sản phẩm nào trong danh mục này</p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import Card from '../ui/Card.vue'
import { useProductStore } from '../../stores/products'
import { useCategoryStore } from '../../stores/categories'

const productStore = useProductStore()
const categoryStore = useCategoryStore()

const categories = computed(() => categoryStore.categories)
const products = computed(() => productStore.products)

const selectedCategory = ref(null)
const isLoading = ref(false)

const filteredProducts = computed(() => {
    if (!selectedCategory.value) return products.value
    return products.value.filter(p => p.categories_id === selectedCategory.value)
})

const selectCategory = (id) => {
    selectedCategory.value = id
}
</script>
