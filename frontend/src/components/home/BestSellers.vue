<template>
    <div v-if="isLoading || products.length > 0" class="mt-3 bg-white p-4 md:p-8 rounded-[5px]">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-lg md:text-2xl font-semibold text-gray-800">Bán chạy</h2>
            <router-link to="/san-pham" class="text-blue-600 hover:text-blue-800 font-medium transition-colors text-sm md:text-base">
                Xem tất cả →
            </router-link>
        </div>

        <div v-if="isLoading" class="flex gap-4 overflow-x-auto scroll-smooth md:grid md:grid-cols-1 md:sm:grid-cols-2 md:lg:grid-cols-4 md:xl:grid-cols-5 md:gap-4">
            <div v-for="i in 5" :key="i" class="bg-white rounded-lg shadow-sm overflow-hidden animate-pulse flex-shrink-0 w-64 md:w-auto">
                <div class="h-80 bg-gray-200"></div>
                <div class="p-4">
                    <div class="h-4 bg-gray-200 rounded mb-2"></div>
                    <div class="h-4 bg-gray-200 rounded mb-2"></div>
                    <div class="h-6 bg-gray-200 rounded mb-2"></div>
                    <div class="h-8 bg-gray-200 rounded"></div>
                </div>
            </div>
        </div>

        <div v-else-if="!isLoading && products.length > 0" class="flex gap-4 overflow-x-auto scroll-smooth md:grid md:grid-cols-1 md:sm:grid-cols-2 md:lg:grid-cols-4 md:xl:grid-cols-5 md:gap-4">
            <div v-for="product in products" :key="product.id" class="flex-shrink-0 w-64 md:w-auto">
                <Card :product="product" @quick-view="openQuickView" />
            </div>
        </div>

        
        <QuickView :show="showQuickView" :product="quickViewProduct" @close="closeQuickView" />
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue'
  import Card from '../ui/Card.vue'
  import { useProducts } from '../../composable/useProducts'
  import QuickView from '../products/Quick-view.vue'
  
  const { getBestSellerProducts } = useProducts()
  
  const isLoading = ref(false)
  const products = ref([])
  const error = ref(null)
  
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
  
  async function fetchBestSellers() {
      try {
          isLoading.value = true
          error.value = null
          const data = await getBestSellerProducts(10)
          products.value = Array.isArray(data) ? data : (data?.data || [])
      } catch (err) {
          error.value = err
          products.value = []
      } finally {
          isLoading.value = false
      }
  }
  
  onMounted(() => {
      fetchBestSellers()
  })
  </script>
  
  <style scoped></style>


