<template>
    <div class="tw-container tw-mx-auto tw-px-4 tw-py-8">
        <div class="tw-flex tw-gap-8">
            <ProductFilter v-model="showFilter" @filter="handleFilter" />
            <main class="tw-flex-1">
                <div
                    class="tw-flex tw-flex-col md:tw-flex-row tw-justify-between tw-items-start md:tw-items-center tw-gap-4 tw-mb-3 tw-bg-white tw-p-3 tw-rounded-[5px]">
                    <div class="tw-flex tw-items-center tw-gap-4 tw-w-full md:tw-w-auto">
                        <button @click="showFilter = !showFilter"
                            class="tw-flex tw-items-center tw-gap-2 tw-text-sm tw-text-gray-600 md:tw-hidden">
                            <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Lọc sản phẩm
                        </button>
                        <div class="tw-relative tw-w-full md:tw-w-64">
                            <input type="text" v-model="searchQuery" @input="handleSearch"
                                placeholder="Tìm kiếm sản phẩm..."
                                class="tw-w-full tw-px-4 tw-py-1.5 tw-text-sm tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500" />
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="tw-h-4 tw-w-4 tw-absolute tw-right-3 tw-top-1/2 tw-transform tw--translate-y-1/2 tw-text-gray-400"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                    <ProductSort @sort="handleSort" />
                </div>

                <!-- Loading Skeleton -->
                <div v-if="loading" class="tw-grid tw-grid-cols-2 md:tw-grid-cols-3 lg:tw-grid-cols-4 tw-gap-4">
                    <div v-for="i in 12" :key="i"
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
                <div v-else
                    class="tw-grid tw-grid-cols-2 md:tw-grid-cols-3 lg:tw-grid-cols-4 tw-gap-4 tw-bg-white tw-p-8 tw-rounded-[5px]">
                    <Card v-for="product in paginatedProducts" :key="product.id" :product="product"
                        @quick-view="openQuickView" />
                </div>

                <div v-if="totalPages > 1 && !loading"
                    class="tw-flex tw-justify-center tw-items-center tw-space-x-2 tw-mt-8">
                    <!-- Previous -->
                    <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
                        class="tw-px-3 tw-py-2 tw-bg-white tw-border tw-rounded hover:tw-bg-gray-50 disabled:tw-opacity-50">
                        ‹
                    </button>

                    <!-- Page numbers -->
                    <button v-for="page in totalPages" :key="page" @click="goToPage(page)"
                        :class="page === currentPage ? 'tw-bg-[#81aacc] tw-text-white' : 'tw-bg-white'"
                        class="tw-px-3 tw-py-2 tw-border tw-rounded hover:tw-bg-gray-50">
                        {{ page }}
                    </button>

                    <!-- Next -->
                    <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages"
                        class="tw-px-3 tw-py-2 tw-bg-white tw-border tw-rounded hover:tw-bg-gray-50 disabled:tw-opacity-50">
                        ›
                    </button>
                </div>

                <!-- Empty State -->
                <div v-if="products.length === 0 && !loading" class="tw-text-center tw-py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-12 tw-w-12 tw-mx-auto tw-text-gray-400 tw-mb-4"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    <h3 class="tw-text-lg tw-font-medium tw-text-gray-900">Không tìm thấy sản phẩm</h3>
                    <p class="tw-text-gray-500">Vui lòng thử lại với bộ lọc khác</p>
                </div>
            </main>
        </div>
        <!-- Quick View Modal -->
        <QuickView :show="showQuickView" :product="quickViewProduct" @close="closeQuickView" />
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import ProductFilter from '~/components/product/ProductFilter.vue'
import ProductSort from '~/components/product/ProductSort.vue'
import Card from '~/components/home/Card.vue'
import QuickView from '~/components/product-detail/Quick-view.vue'
import { useRoute } from 'vue-router'

const showFilter = ref(false)
const products = ref([])
const searchQuery = ref('')
const loading = ref(false)
const { getProducts, searchProducts } = useProducts()
const currentPage = ref(1)
const itemsPerPage = 12
const filters = ref({})
const route = useRoute()

// Quick View State
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

const fetchProducts = async () => {
    loading.value = true
    try {
        const filtersObj = {}
        if (route.query.category) {
            filtersObj.category = route.query.category
        }
        if (route.query.brand) {
            filtersObj.brand = route.query.brand
        }
        filters.value = filtersObj
        products.value = await getProducts(filtersObj)
    } catch (error) {
        console.error('Error fetching products:', error)
    } finally {
        loading.value = false
    }
}

onMounted(fetchProducts)
watch(
    [() => route.query.category, () => route.query.brand],
    fetchProducts
)

const handleSort = async (sortOption) => {
    loading.value = true
    try {
        products.value = await getProducts({
            ...filters.value,
            sort_by: sortOption.sort_by,
            sort_direction: sortOption.sort_direction
        })
    } catch (error) {
        console.error('Error sorting products:', error)
    } finally {
        loading.value = false
    }
}

const handleSearch = async () => {
    loading.value = true
    try {
        if (searchQuery.value.trim() === '') {
            products.value = await getProducts(filters.value);
            return;
        }

        products.value = await searchProducts(searchQuery.value, filters.value);

    } catch (e) {
        console.error('Lỗi khi tìm kiếm sản phẩm:', e)
    } finally {
        loading.value = false
    }
}

const handleFilter = async (newFilters) => {
    filters.value = newFilters
    currentPage.value = 1
    loading.value = true
    try {
        products.value = await getProducts(filters.value)
    } catch (error) {
        console.error('Error filtering products:', error)
    } finally {
        loading.value = false
    }
}

const paginatedProducts = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage
    const end = start + itemsPerPage
    return products.value.slice(start, end)
})

const totalPages = computed(() => {
    return Math.ceil(products.value.length / itemsPerPage)
})

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page
    }
}

</script>

<style scoped>
@media (max-width: 768px) {
    .tw-container {
        padding-bottom: 20px;
    }
}
</style>