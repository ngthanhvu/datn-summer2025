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
          <ProductSort />
        </div>
        <ProductGrid />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import ProductFilter from '~/components/product/ProductFilter.vue'
import ProductSort from '~/components/product/ProductSort.vue'
import ProductGrid from '~/components/product/ProductGrid.vue'

const showFilter = ref(false)

useHead({
  title: 'Sản phẩm - DEVGANG',
  meta: [
    { name: 'description', content: 'Sản phẩm - DEVGANG' },
  ],
});
</script>

<style scoped>
@media (max-width: 768px) {
  .tw-container {
    padding-bottom: 20px;
  }
}
</style>