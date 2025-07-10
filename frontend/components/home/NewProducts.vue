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
        <div v-if="homeStore.isLoadingProducts"
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

        <!-- Error State -->
        <div v-else-if="homeStore.error" class="tw-text-center tw-py-8">
            <div class="tw-text-red-500 tw-mb-4">{{ homeStore.error }}</div>
            <button @click="fetchNewProducts"
                class="tw-px-4 tw-py-2 tw-bg-blue-600 tw-text-white tw-rounded hover:tw-bg-blue-700">
                Thử lại
            </button>
        </div>

        <!-- Products Grid -->
        <div v-else class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-4 xl:tw-grid-cols-5 tw-gap-4">
            <Card v-for="product in homeStore.newProducts.slice(0, 5)" :key="product.id" :product="product"
                @quick-view="openQuickView" />
        </div>

        <!-- Empty State -->
        <div v-if="!homeStore.isLoadingProducts && !homeStore.error && homeStore.newProducts.length === 0"
            class="tw-text-center tw-py-8">
            <p class="tw-text-gray-500">Chưa có sản phẩm mới</p>
        </div>
        <!-- Quick View Modal -->
        <QuickView :show="showQuickView" :product="quickViewProduct" @close="closeQuickView" />
    </div>
</template>

<script setup>
import { useHomeStore } from '~/stores/useHomeStore'
import Card from './Card.vue'
import QuickView from '~/components/product-detail/Quick-view.vue'

const homeStore = useHomeStore()

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
        await homeStore.fetchNewProducts(10)
    } catch (err) {
        console.error('Error fetching new products:', err)
    }
}

onMounted(async () => {
    if (!homeStore.hasValidData('products')) {
        await fetchNewProducts()
    }
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