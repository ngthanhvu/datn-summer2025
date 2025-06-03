<template>
  <aside :class="[
    'tw-w-72 tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6',
    'md:tw-block',
    modelValue ? 'tw-fixed tw-top-0 tw-left-0 tw-h-screen tw-z-[999] tw-overflow-y-auto' : 'tw-hidden'
  ]">
    <div class="tw-flex tw-justify-between tw-items-center tw-mb-4 md:tw-hidden">
      <h2 class="tw-text-lg tw-font-semibold">Bộ lọc</h2>
      <button @click="$emit('update:modelValue', false)" class="tw-text-gray-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-6 tw-w-6" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
    <h2 class="tw-text-lg tw-font-semibold tw-mb-4">Tất cả sản phẩm</h2>
    <ColorFilter @filter="handleColorFilter" />
    <PriceFilter @filter="handlePriceFilter" />
  </aside>
</template>

<script setup>
import { ref } from 'vue'
import ColorFilter from './ColorFilter.vue'
import PriceFilter from './PriceFilter.vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  }
})

const emit = defineEmits(['update:modelValue', 'filter'])

const filters = ref({
  color: null,
  min_price: null,
  max_price: null
})

const handleColorFilter = (colorFilter) => {
  filters.value = { ...filters.value, ...colorFilter }
  emit('filter', filters.value)
}

const handlePriceFilter = (priceFilter) => {
  filters.value = { ...filters.value, ...priceFilter }
  emit('filter', filters.value)
}
</script>