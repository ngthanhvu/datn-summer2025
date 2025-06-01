<template>
    <div class="tw-container tw-mx-auto tw-px-4 tw-py-8">
        <div class="tw-flex tw-gap-8">
            <ProductFilter v-model="showFilter" />
            <main class="tw-flex-1">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
                    <button @click="showFilter = !showFilter"
                        class="tw-flex tw-items-center tw-gap-2 tw-text-sm tw-text-gray-600 md:tw-hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Lọc sản phẩm
                    </button>
                    <ProductSort @sort="handleSort" />
                </div>

                <!-- Products Grid -->
                <div class="tw-grid tw-grid-cols-2 md:tw-grid-cols-3 lg:tw-grid-cols-4 tw-gap-4">
                    <Card v-for="product in products" :key="product.id" :product="product" />
                </div>

                <!-- Empty State -->
                <div v-if="products.length === 0" class="tw-text-center tw-py-12">
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
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import ProductFilter from '~/components/product/ProductFilter.vue'
import ProductSort from '~/components/product/ProductSort.vue'
import Card from '~/components/home/Card.vue'

const showFilter = ref(false)
const products = ref([])
const { getProducts } = useProducts()

onMounted(async () => {
    try {
        products.value = await getProducts()
    } catch (error) {
        console.error('Error fetching products:', error)
    }
})

const handleSort = (sortOption) => {
    // Implement sorting logic here
    console.log('Sort by:', sortOption)
}
</script>

<style scoped>
@media (max-width: 768px) {
    .tw-container {
        padding-bottom: 20px;
    }
}
</style>