<template>
    <div class="tw-mt-8">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
            <h2 class="tw-text-2xl tw-font-bold tw-text-gray-800">Sản phẩm theo danh mục</h2>
        </div>

        <!-- Category Tabs -->
        <div v-if="!categoriesLoading" class="tw-flex tw-flex-wrap tw-gap-2 tw-mb-6">
            <button @click="selectCategory(null)" :class="[
                'tw-px-4 tw-py-2 tw-rounded-full tw-text-sm tw-font-medium tw-transition-colors',
                selectedCategory === null
                    ? 'tw-bg-blue-600 tw-text-white'
                    : 'tw-bg-gray-100 tw-text-gray-700 tw-hover:bg-gray-200'
            ]">
                Tất cả sản phẩm
            </button>
            <button v-for="category in categories" :key="category.id" @click="selectCategory(category.id)" :class="[
                'tw-px-4 tw-py-2 tw-rounded-full tw-text-sm tw-font-medium tw-transition-colors',
                selectedCategory === category.id
                    ? 'tw-bg-blue-600 tw-text-white'
                    : 'tw-bg-gray-100 tw-text-gray-700 tw-hover:bg-gray-200'
            ]">
                {{ category.name }}
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading"
            class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-4 xl:tw-grid-cols-5 tw-gap-4">
            <div v-for="i in 5" :key="i"
                class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-overflow-hidden tw-animate-pulse">
                <div class="tw-h-80 tw-bg-gray-200"></div>
                <div class="tw-p-4">
                    <div class="tw-h-4 tw-bg-gray-200 tw-rounded tw-mb-2"></div>
                    <div class="tw-h-4 tw-bg-gray-200 tw-rounded tw-mb-2"></div>
                    <div class="tw-h-6 tw-bg-gray-200 tw-rounded tw-mb-2"></div>
                    <div class="tw-h-8 tw-bg-gray-200 tw-rounded"></div>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div v-else class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-4 xl:tw-grid-cols-5 tw-gap-4">
            <Card v-for="product in filteredProducts.slice(0, 5)" :key="product.id" :product="product" />
        </div>

        <!-- Empty State -->
        <div v-if="!loading && filteredProducts.length === 0" class="tw-text-center tw-py-8">
            <p class="tw-text-gray-500">Không có sản phẩm nào trong danh mục này</p>
        </div>
    </div>
</template>

<script setup>
import Card from './Card.vue'

const { getProductsByCategory, getCategories } = useHome()

const selectedCategory = ref(null)
const categories = ref([])
const allProducts = ref([])
const filteredProducts = ref([])
const loading = ref(true)
const categoriesLoading = ref(true)

// Lấy danh mục
const fetchCategories = async () => {
    try {
        categoriesLoading.value = true
        const cats = await getCategories()
        categories.value = cats
        if (cats.length > 0) {
            selectedCategory.value = cats[0].id
        }
    } catch (error) {
        console.error('Error fetching categories:', error)
    } finally {
        categoriesLoading.value = false
    }
}

// Lấy tất cả sản phẩm
const fetchAllProducts = async () => {
    try {
        loading.value = true
        const products = await getProductsByCategory(null, 50)
        allProducts.value = products
        filteredProducts.value = products
    } catch (error) {
        console.error('Error fetching products:', error)
    } finally {
        loading.value = false
    }
}

// Chọn danh mục
const selectCategory = async (categoryId) => {
    selectedCategory.value = categoryId
    loading.value = true

    try {
        if (categoryId) {
            const products = await getProductsByCategory(categoryId, 20)
            filteredProducts.value = products
        } else {
            filteredProducts.value = allProducts.value
        }
    } catch (error) {
        console.error('Error fetching products by category:', error)
    } finally {
        loading.value = false
    }
}

// Khởi tạo dữ liệu
onMounted(async () => {
    await Promise.all([
        fetchCategories(),
        fetchAllProducts()
    ])
    selectedCategory.value = null // Mặc định chọn "Tất cả sản phẩm"
})
</script>