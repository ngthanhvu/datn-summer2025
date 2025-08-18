<!-- src/pages/products.vue -->
<template>
    <div class="container mx-auto px-2 sm:px-4 py-4 sm:py-8">
        <div class="flex flex-col lg:flex-row gap-4 lg:gap-8">
            <!-- Filter Sidebar - Ẩn trên mobile, hiển thị trên desktop -->
            <div class="hidden lg:block lg:w-80">
                <ProductFilter @filter="handleFilter" />
            </div>

            <!-- Mobile Filter Overlay -->
            <Transition name="filter-overlay">
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
            </Transition>

            <main class="flex-1 min-w-0">
                <!-- Top controls -->
                <div
                    class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4 bg-white p-3 rounded-lg shadow-sm">
                    <div class="flex items-center gap-4 w-full md:w-auto">
                        <button @click="showFilter = !showFilter"
                            class="flex items-center gap-2 text-sm text-gray-600 lg:hidden bg-gray-100 px-3 py-2 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            Lọc sản phẩm
                        </button>
                        <div class="relative w-full md:w-64">
                            <input type="text" v-model="searchQuery" @input="handleSearch"
                                placeholder="Tìm kiếm sản phẩm..." :class="[
                                    'w-full px-4 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500',
                                    productStore.loading ? 'border-blue-300 bg-blue-50' : 'border-gray-300'
                                ]" />
                            <div v-if="productStore.loading"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                <div
                                    class="animate-spin h-4 w-4 border-2 border-blue-500 border-t-transparent rounded-full">
                                </div>
                            </div>
                            <svg v-else xmlns="http://www.w3.org/2000/svg"
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
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm md:text-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="name_asc">Tên A → Z</option>
                            <option value="name_desc">Tên Z → A</option>
                            <option value="price_asc">Giá tăng dần</option>
                            <option value="price_desc">Giá giảm dần</option>
                        </select>
                    </div>
                </div>

                <!-- Loading state -->
                <div v-if="productStore.loading" class="bg-white p-4 md:p-6 rounded-lg shadow-sm">
                    <div class="mb-6">
                        <div class="skeleton-loader h-6 w-32 mb-2"></div>
                        <div class="skeleton-loader h-4 w-48"></div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
                        <div v-for="n in 8" :key="n" class="product-card-skeleton">
                            <div class="skeleton-loader h-48 w-full rounded-lg mb-3"></div>

                            <div class="space-y-2">
                                <div class="skeleton-loader h-4 w-3/4"></div>
                                <div class="skeleton-loader h-4 w-1/2"></div>
                                <div class="skeleton-loader h-5 w-1/3"></div>
                            </div>

                            <div class="mt-3">
                                <div class="skeleton-loader h-10 w-full rounded-lg"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div v-if="filteredProducts && filteredProducts.length > 0"
                    class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 bg-white p-4 md:p-6 rounded-lg shadow-sm">
                    <Card v-for="product in filteredProducts" :key="product.id" :product="product"
                        @quick-view="openQuickView" />
                </div>

                <!-- No products found message -->
                <div v-if="(!filteredProducts || filteredProducts.length === 0) && !productStore.loading"
                    class="bg-white p-8 rounded-lg shadow-sm text-center">
                    <div class="flex flex-col items-center gap-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">
                                {{ searchQuery.trim() ? 'Không tìm thấy sản phẩm phù hợp' : 'Không có sản phẩm nào' }}
                            </h3>
                            <p class="text-gray-500">
                                {{ searchQuery.trim()
                                    ? `Không có sản phẩm nào phù hợp với từ khóa "${searchQuery}". Vui lòng thử từ khóa khác
                                hoặc điều chỉnh bộ lọc.`
                                    : 'Không có sản phẩm nào phù hợp với bộ lọc hiện tại. Vui lòng thử điều chỉnh bộ lọc.'
                                }}
                            </p>
                            <div v-if="searchQuery.trim()" class="mt-4">
                                <button @click="clearSearch"
                                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                                    Xóa từ khóa tìm kiếm
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="totalPages > 1 && productStore.pagination?.total > 0"
                    class="flex justify-center items-center space-x-1 sm:space-x-2 mt-8">
                    <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
                        class="px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"><i
                            class="fa-solid fa-angles-left"></i></button>

                    <button v-for="page in totalPages" :key="page" @click="goToPage(page)"
                        :class="page === currentPage ? 'bg-[#81aacc] border-[#81aacc] text-white' : 'bg-white hover:bg-gray-50'"
                        class="px-3 py-2 rounded-lg border border-gray-300 text-sm font-medium cursor-pointer">{{ page
                        }}</button>

                    <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages"
                        class="px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"><i
                            class="fa-solid fa-angles-right"></i></button>
                </div>
            </main>
        </div>
        <QuickView :show="showQuickView" :product="quickViewProduct" @close="closeQuickView" />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick, onUnmounted } from 'vue'
import Card from '../ui/Card.vue'
import ProductFilter from './ProductsFillter.vue'
import { useProductStore } from '../../stores/products'
import QuickView from '../products/Quick-view.vue'

const productStore = useProductStore()

if (!productStore.products) {
    productStore.products = []
}
if (!productStore.pagination) {
    productStore.pagination = {
        current_page: 1,
        last_page: 1,
        per_page: 8,
        total: 0,
        from: null,
        to: null,
        next_page_url: null,
        prev_page_url: null,
        links: []
    }
}

onMounted(() => {
    if (productStore) {
        const [field, direction] = sortOption.value.split('_')
        const sortFilters = {
            ...filters.value,
            sort_by: field,
            sort_direction: direction
        }
        productStore.fetchProducts(sortFilters, currentPage.value)
    }
})

onUnmounted(() => {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value)
    }
})

const showFilter = ref(false)
const searchQuery = ref('')
const currentPage = ref(1)
const searchTimeout = ref(null)

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

    const [field, direction] = sortOption.value.split('_')
    const sortFilters = {
        ...filters.value,
        sort_by: field,
        sort_direction: direction
    }

    if (productStore) {
        if (searchQuery.value.trim()) {
            productStore.searchProductsAction(searchQuery.value, sortFilters, currentPage.value)
        } else {
            productStore.fetchProducts(sortFilters, currentPage.value)
        }
    }
    if (window.innerWidth < 1024) {
        showFilter.value = false
    }
}

const handleSearch = () => {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value)
    }

    searchTimeout.value = setTimeout(() => {
        performSearch()
    }, 500)
}

const performSearch = () => {
    currentPage.value = 1

    const [field, direction] = sortOption.value.split('_')
    const sortFilters = {
        ...filters.value,
        sort_by: field,
        sort_direction: direction
    }

    if (productStore) {
        if (searchQuery.value.trim()) {
            productStore.searchProductsAction(searchQuery.value, sortFilters, currentPage.value)
        } else {
            productStore.fetchProducts(sortFilters, currentPage.value)
        }
    }
}

const handleSort = () => {
    currentPage.value = 1
    const [field, direction] = sortOption.value.split('_')
    const sortFilters = {
        ...filters.value,
        sort_by: field,
        sort_direction: direction
    }

    if (productStore) {
        if (searchQuery.value.trim()) {
            productStore.searchProductsAction(searchQuery.value, sortFilters, currentPage.value)
        } else {
            productStore.fetchProducts(sortFilters, currentPage.value)
        }
    }
}

watch(searchQuery, (newQuery, oldQuery) => {
    if (newQuery !== oldQuery) {
        if (searchTimeout.value) {
            clearTimeout(searchTimeout.value)
        }

        searchTimeout.value = setTimeout(() => {
            currentPage.value = 1

            const [field, direction] = sortOption.value.split('_')
            const sortFilters = {
                ...filters.value,
                sort_by: field,
                sort_direction: direction
            }

            if (productStore) {
                if (newQuery.trim() === '') {
                    productStore.fetchProducts(sortFilters, currentPage.value)
                } else {
                    productStore.searchProductsAction(newQuery, sortFilters, currentPage.value)
                }
            }
        }, 500)
    }
})

const filteredProducts = computed(() => {
    const products = productStore.products || []
    return products
})
const totalPages = computed(() => productStore.pagination?.last_page || 1)

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value && productStore) {
        currentPage.value = page

        const [field, direction] = sortOption.value.split('_')
        const sortFilters = {
            ...filters.value,
            sort_by: field,
            sort_direction: direction
        }

        if (searchQuery.value.trim()) {
            productStore.searchProductsAction(searchQuery.value, sortFilters, page)
        } else {
            productStore.fetchProducts(sortFilters, page)
        }
    }
}

const clearSearch = () => {
    searchQuery.value = ''
    currentPage.value = 1

    const [field, direction] = sortOption.value.split('_')
    const sortFilters = {
        ...filters.value,
        sort_by: field,
        sort_direction: direction
    }

    if (productStore) {
        productStore.fetchProducts(sortFilters, currentPage.value)
    }
}
</script>

<style scoped>
/* Filter Overlay Transition */
.filter-overlay-enter-active,
.filter-overlay-leave-active {
    transition: opacity 0.3s ease;
}

.filter-overlay-enter-from,
.filter-overlay-leave-to {
    opacity: 0;
}

.filter-overlay-enter-to,
.filter-overlay-leave-from {
    opacity: 1;
}

/* Skeleton Loading */
.skeleton-loader {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 37%, #f0f0f0 63%);
    background-size: 400% 100%;
    animation: skeleton-loading 1.4s ease infinite;
    border-radius: 4px;
}

@keyframes skeleton-loading {
    0% {
        background-position: 100% 50%;
    }

    100% {
        background-position: 0% 50%;
    }
}

.product-card-skeleton {
    background: white;
    padding: 1rem;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
}
</style>
