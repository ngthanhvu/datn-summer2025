<template>
    <div class="tw-mt-3 tw-bg-white tw-p-8 tw-rounded-[10px]">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
            <h2 class="tw-text-2xl tw-font-bold tw-text-gray-800">Sản phẩm theo danh mục</h2>
        </div>

        <!-- Category Tabs -->
        <div v-if="!homeStore.isLoadingCategories" class="tw-flex tw-flex-wrap tw-gap-2 tw-mb-6">
            <button @click="selectCategory(null)" :class="[
                'tw-px-4 tw-py-2 tw-rounded-full tw-text-sm tw-font-medium tw-transition-colors',
                selectedCategory === null
                    ? 'tw-bg-[#81aacc] tw-text-white'
                    : 'tw-bg-white tw-border tw-border-gray-300 tw-text-gray-700 tw-hover:bg-gray-200'
            ]">
                Tất cả sản phẩm
            </button>
            <button v-for="category in homeStore.categories" :key="category.id" @click="selectCategory(category.id)"
                :class="[
                    'tw-px-4 tw-py-2 tw-rounded-full tw-text-sm tw-font-medium tw-transition-colors',
                    selectedCategory === category.id
                        ? 'tw-bg-[#81aacc] tw-text-white'
                        : 'tw-bg-white tw-border tw-border-gray-300 tw-text-gray-700 tw-hover:bg-gray-200'
                ]">
                {{ category.name }}
            </button>
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

        <!-- Products Grid -->
        <div v-else class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-4 xl:tw-grid-cols-5 tw-gap-4">
            <Card v-for="product in homeStore.categoryProducts.slice(0, 5)" :key="product.id" :product="product"
                @quick-view="openQuickView" />
        </div>

        <!-- Empty State -->
        <div v-if="!homeStore.isLoadingProducts && homeStore.categoryProducts.length === 0"
            class="tw-text-center tw-py-8">
            <p class="tw-text-gray-500">Không có sản phẩm nào trong danh mục này</p>
        </div>
        <!-- Quick View Modal -->
        <QuickView :show="showQuickView" :product="quickViewProduct" @close="closeQuickView" />
    </div>
</template>

<script setup>
import Card from './Card.vue'
import QuickView from '~/components/product-detail/Quick-view.vue'
import { useHomeStore } from '~/stores/useHomeStore'

const homeStore = useHomeStore()

const selectedCategory = ref(null)

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

// Chọn danh mục
const selectCategory = async (categoryId) => {
    selectedCategory.value = categoryId
    await homeStore.fetchCategoryProducts(categoryId, 20)
}

// Khởi tạo dữ liệu
// XÓA toàn bộ onMounted fetch data, chỉ lấy state từ store
</script>