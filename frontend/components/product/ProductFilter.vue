<template>
  <aside :class="[
    'tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6 tw-z-[0]',
    'md:tw-block md:tw-static md:tw-w-72',
    modelValue
      ? 'tw-fixed tw-inset-0 tw-w-full tw-h-full tw-overflow-y-auto'
      : 'tw-hidden md:tw-block'
  ]" style="transition: all 0.3s;">
    <div class="tw-flex tw-justify-between tw-items-center tw-mb-4 md:tw-hidden">
      <h2 class="tw-text-lg tw-font-semibold">Bộ lọc</h2>
      <button @click="$emit('update:modelValue', false)" class="tw-text-gray-500 tw-p-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-6 tw-w-6" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
    <h2 class="tw-text-lg tw-font-semibold tw-mb-4 md:tw-block tw-hidden">Tất cả sản phẩm</h2>
    <div :class="[
      'tw-w-64 tw-bg-white tw-p-4 tw-rounded-lg tw-shadow',
      modelValue ? 'tw-block' : 'tw-hidden md:tw-block'
    ]">
      <h3 class="tw-text-lg tw-font-medium tw-mb-4">Bộ lọc sản phẩm</h3>

      <!-- Price Range -->
      <div class="tw-mb-6">
        <h4 class="tw-font-medium tw-mb-2">Khoảng giá</h4>
        <div class="tw-flex tw-gap-2">
          <input type="number" v-model="filters.min_price" placeholder="Từ"
            class="tw-w-full tw-px-2 tw-py-1 tw-border tw-rounded" />
          <input type="number" v-model="filters.max_price" placeholder="Đến"
            class="tw-w-full tw-px-2 tw-py-1 tw-border tw-rounded" />
        </div>
      </div>

      <!-- Categories -->
      <div class="tw-mb-6">
        <h4 class="tw-font-medium tw-mb-2">Danh mục</h4>
        <div class="tw-space-y-2">
          <label v-for="category in filterOptions.categories" :key="category.id" class="tw-flex tw-items-center">
            <input type="checkbox" :value="category.id" v-model="filters.category" class="tw-mr-2" />
            {{ category.name }}
          </label>
        </div>
      </div>

      <!-- Brands -->
      <div class="tw-mb-6">
        <h4 class="tw-font-medium tw-mb-2">Thương hiệu</h4>
        <div class="tw-space-y-2">
          <label v-for="brand in filterOptions.brands" :key="brand.id" class="tw-flex tw-items-center">
            <input type="checkbox" :value="brand.id" v-model="filters.brand" class="tw-mr-2" />
            {{ brand.name }}
          </label>
        </div>
      </div>

      <!-- Colors -->
      <div class="tw-mb-6">
        <h4 class="tw-font-medium tw-mb-2">Màu sắc</h4>
        <div class="tw-space-y-2">
          <label v-for="color in filterOptions.colors" :key="color" class="tw-flex tw-items-center">
            <input type="checkbox" :value="color" v-model="filters.color" class="tw-mr-2" />
            <span class="tw-inline-block tw-w-5 tw-h-5 tw-rounded-full tw-border tw-mr-2"
              :style="{ backgroundColor: color }" :title="color"></span>
            {{ color }}
          </label>
        </div>
      </div>

      <!-- Sizes -->
      <div class="tw-mb-6">
        <h4 class="tw-font-medium tw-mb-2">Kích thước</h4>
        <div class="tw-space-y-2">
          <label v-for="size in filterOptions.sizes" :key="size" class="tw-flex tw-items-center">
            <input type="checkbox" :value="size" v-model="filters.size" class="tw-mr-2" />
            {{ size }}
          </label>
        </div>
      </div>

      <!-- Apply Filters Button -->
      <button @click="applyFilters"
        class="tw-w-full tw-bg-[#81AACC] tw-text-white tw-py-2 tw-px-4 tw-rounded hover:tw-bg-[#6ba0cc]">
        Áp dụng bộ lọc
      </button>
    </div>
  </aside>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useProducts } from '~/composables/useProducts.js'

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  }
})

const emit = defineEmits(['update:modelValue', 'filter'])

const filters = ref({
  min_price: null,
  max_price: null,
  category: [],
  brand: [],
  color: [],
  size: []
})

const { getFilterOptions } = useProducts()

const filterOptions = ref({
  colors: [],
  sizes: [],
  categories: [],
  brands: []
})

const fetchFilterOptions = async () => {
  const data = await getFilterOptions()
  if (data) {
    filterOptions.value = {
      colors: data.colors || [],
      sizes: data.sizes || [],
      categories: data.categories || [],
      brands: data.brands || []
    }
  }
}

const applyFilters = () => {
  emit('filter', { ...filters.value })
  // Tự động đóng filter trên mobile
  if (window.innerWidth <= 768) {
    emit('update:modelValue', false)
  }
}

watch(filters, () => {
  applyFilters()
}, { deep: true })

onMounted(() => {
  fetchFilterOptions()
})
</script>

<style scoped>
@media (max-width: 768px) {
  aside {
    border-radius: 0 !important;
    padding: 16px !important;
    min-height: 100vh;
  }

  .tw-w-64 {
    width: 100% !important;
    max-width: 100vw !important;
  }

  .tw-p-4 {
    padding: 1rem !important;
  }

  .tw-p-6 {
    padding: 1.5rem !important;
  }

  .tw-shadow,
  .tw-shadow-sm {
    box-shadow: none !important;
  }
}
</style>