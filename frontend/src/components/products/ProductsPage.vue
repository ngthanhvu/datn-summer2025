<!-- src/pages/products.vue -->
<template>
    <div class="container mx-auto px-2 sm:px-4 py-4 sm:py-8">
        <div class="flex flex-col lg:flex-row gap-4 lg:gap-8">
            <!-- Filter Sidebar - Ẩn trên mobile, hiển thị trên desktop -->
            <div class="hidden lg:block lg:w-80">
                <ProductFilter @filter="handleFilter" />
            </div>

            <!-- Mobile Filter Overlay -->
            <div v-if="showFilter" class="fixed inset-0 bg-black bg-opacity-50 z-50 lg:hidden"
                @click="showFilter = false">
                <div class="fixed top-0 left-0 h-full w-80 bg-white shadow-lg overflow-y-auto" @click.stop>
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold">Bộ lọc</h3>
                        <button @click="showFilter = false" class="text-gray-500 hover:text-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="p-4">
                        <ProductFilter @filter="handleFilter" />
                    </div>
                </div>
            </div>

            <main class="flex-1 min-w-0">
                <!-- Top controls -->
                <div
                    class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-3 bg-white p-3 rounded-[5px]">
                    <div class="flex items-center gap-4 w-full md:w-auto">
                        <button @click="showFilter = !showFilter"
                            class="flex items-center gap-2 text-sm text-gray-600 lg:hidden">
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
                        <span class="text-sm md:text-md">Sắp xếp:</span>
                        <select v-model="sortOption" @change="handleSort"
                            class="border border-gray-300 rounded px-2 py-1 text-sm md:text-md">
                            <option value="name_asc">Tên A → Z</option>
                            <option value="name_desc">Tên Z → A</option>
                            <option value="price_asc">Giá tăng dần</option>
                            <option value="price_desc">Giá giảm dần</option>
                        </select>
                    </div>
                </div>

                <!-- Products Grid -->
                <div v-if="filteredProducts.length > 0"
                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 bg-white p-4 md:p-8 rounded-[5px]">
                    <Card v-for="product in paginatedProducts" :key="product.id" :product="product"
                        @quick-view="openQuickView" />
                </div>

                <!-- No products found message -->
                <div v-if="filteredProducts.length === 0" class="bg-white p-4 md:p-8 rounded-[5px] text-center">
                    <div class="flex flex-col items-center gap-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-3.039 0-5.812-1.23-7.834-3.209M15 19.128v-.037c0-2.123-1.152-4.078-3.007-5.085M12 19.128v-.037c0-2.123-1.152-4.078-3.007-5.085M12 19.128v-.037c0-2.123-1.152-4.078-3.007-5.085" />
                        </svg>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Không tìm thấy sản phẩm</h3>
                            <p class="text-gray-500">Không có sản phẩm nào phù hợp với bộ lọc hiện tại. Vui lòng thử
                                điều chỉnh bộ lọc hoặc tìm kiếm với từ khóa khác.</p>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="totalPages > 1 && filteredProducts.length > 0"
                    class="flex justify-center items-center space-x-1 sm:space-x-2 mt-8">
                    <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
                        class="px-2 sm:px-3 py-2 bg-white border rounded hover:bg-gray-50 disabled:opacity-50">‹</button>

                    <button v-for="page in totalPages" :key="page" @click="goToPage(page)"
                        :class="page === currentPage ? 'bg-[#81aacc] text-white' : 'bg-white'"
                        class="px-2 sm:px-3 py-2 border rounded hover:bg-gray-50 text-sm">{{ page }}</button>

                    <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages"
                        class="px-2 sm:px-3 py-2 bg-white border rounded hover:bg-gray-50 disabled:opacity-50">›</button>
                </div>
            </main>
        </div>
        <QuickView :show="showQuickView" :product="quickViewProduct" @close="closeQuickView" />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Card from '../ui/Card.vue'
import ProductFilter from './ProductsFillter.vue'
import { useProductStore } from '../../stores/products'
import QuickView from '../products/Quick-view.vue'

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


const handleFilter = (newFilter) => {
    filters.value = { ...filters.value, ...newFilter }
    currentPage.value = 1
    // Đóng filter overlay trên mobile sau khi áp dụng
    if (window.innerWidth < 1024) {
        showFilter.value = false
    }
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
</script>
