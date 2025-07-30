<!-- src/pages/products.vue -->
<template>
    <div class="container mx-auto px-4 py-8">
        <div class="flex gap-8">
            <ProductFilter @filter="handleFilter" />

            <main class="flex-1">
                <!-- Top controls -->
                <div
                    class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-3 bg-white p-3 rounded-[5px]">
                    <div class="flex items-center gap-4 w-full md:w-auto">
                        <button @click="showFilter = !showFilter"
                            class="flex items-center gap-2 text-sm text-gray-600 md:hidden">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Lọc sản phẩm
                        </button>
                        <div class="relative w-full md:w-64">
                            <input type="text" v-model="searchQuery" @input="handleSearch"
                                placeholder="Tìm kiếm sản phẩm..."
                                class="w-full px-4 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Sort -->
                    <div class="flex items-center gap-2">
                        <span class="text-md">Sắp xếp:</span>
                        <select v-model="sortOption" @change="handleSort"
                            class="border border-gray-300 rounded px-2 py-1 text-md">
                            <option value="name_asc">Tên A → Z</option>
                            <option value="name_desc">Tên Z → A</option>
                            <option value="price_asc">Giá tăng dần</option>
                            <option value="price_desc">Giá giảm dần</option>
                        </select>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 bg-white p-8 rounded-[5px]">
                    <Card v-for="product in paginatedProducts" :key="product.id" :product="product"
                        @quick-view="openQuickView" />
                </div>

                <!-- Pagination -->
                <div v-if="totalPages > 1" class="flex justify-center items-center space-x-2 mt-8">
                    <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
                        class="px-3 py-2 bg-white border rounded hover:bg-gray-50 disabled:opacity-50">‹</button>

                    <button v-for="page in totalPages" :key="page" @click="goToPage(page)"
                        :class="page === currentPage ? 'bg-[#81aacc] text-white' : 'bg-white'"
                        class="px-3 py-2 border rounded hover:bg-gray-50">{{ page }}</button>

                    <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages"
                        class="px-3 py-2 bg-white border rounded hover:bg-gray-50 disabled:opacity-50">›</button>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Card from '../ui/Card.vue'
import ProductFilter from './ProductsFillter.vue'
import { useProductStore } from '../../stores/products'

const productStore = useProductStore()

onMounted(() => {
    productStore.fetchProducts()
})
const showFilter = ref(false)
const searchQuery = ref('')
const currentPage = ref(1)
const itemsPerPage = 8

const sortOption = ref('name_asc')

const filters = ref({
    min_price: null,
    max_price: null,
    category: [],
    brand: [],
    color: [],
    size: []
})


const handleFilter = (newFilter) => {
    filters.value = { ...filters.value, ...newFilter }
    currentPage.value = 1
}

const handleSearch = () => {
    currentPage.value = 1
}

const handleSort = () => {
    currentPage.value = 1
}

const filteredProducts = computed(() => {
    return productStore.products.filter(p => {
        const matchName = p.name.toLowerCase().includes(searchQuery.value.toLowerCase().trim())

        const matchMin = !filters.value.min_price || p.price >= filters.value.min_price
        const matchMax = !filters.value.max_price || p.price <= filters.value.max_price

        const matchCategory = filters.value.category.length === 0 || filters.value.category.includes(p.categories_id)
        const matchBrand = filters.value.brand.length === 0 || filters.value.brand.includes(p.brand_id)

        const matchColor =
            filters.value.color.length === 0 ||
            p.variants.some(variant => filters.value.color.includes(variant.color))

        const matchSize =
            filters.value.size.length === 0 ||
            p.variants.some(variant => filters.value.size.includes(variant.size))

        return matchName && matchMin && matchMax && matchCategory && matchBrand && matchColor && matchSize
    })
})


const sortedProducts = computed(() => {
    const [key, dir] = sortOption.value.split('_')
    return [...filteredProducts.value].sort((a, b) => {
        const aVal = a[key]
        const bVal = b[key]
        if (aVal < bVal) return dir === 'asc' ? -1 : 1
        if (aVal > bVal) return dir === 'asc' ? 1 : -1
        return 0
    })
})

const paginatedProducts = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage
    return sortedProducts.value.slice(start, start + itemsPerPage)
})

const totalPages = computed(() => Math.ceil(sortedProducts.value.length / itemsPerPage))

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page
    }
}

const openQuickView = (product) => {
    console.log('Quick view:', product)
}
</script>
