<template>
    <div class="tw-mt-3 tw-bg-white tw-p-8 tw-rounded-[5px]">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-3">
            <h2 class="tw-text-2xl tw-font-bold tw-text-gray-800">Sản phẩm mới</h2>
            <NuxtLink to="/products/new"
                class="tw-text-blue-600 tw-hover:text-blue-800 tw-font-medium tw-transition-colors">
                Xem tất cả →
            </NuxtLink>
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
            <Card v-for="product in newProducts.slice(0, 5)" :key="product.id" :product="product"
                @quick-view="openQuickView" />
        </div>

        <!-- Empty State -->
        <div v-if="!loading && newProducts.length === 0" class="tw-text-center tw-py-8">
            <p class="tw-text-gray-500">Chưa có sản phẩm mới</p>
        </div>
        <!-- Quick View Modal -->
        <QuickView :show="showQuickView" :product="quickViewProduct" @close="closeQuickView" />
    </div>
</template>

<script setup>
import { useHome } from '../../composables/useHome'
import Card from './Card.vue'
import QuickView from '~/components/product-detail/Quick-view.vue'

const { getNewProducts } = useHome()

const newProducts = ref([])
const loading = ref(true)

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

const fetchNewProducts = async () => {
    try {
        loading.value = true
        const products = await getNewProducts(10)
        newProducts.value = products
    } catch (error) {
        console.error('Error fetching new products:', error)
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    fetchNewProducts()
})
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    line-clamp: 2;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>