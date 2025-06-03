<template>
  <div class="tw-mb-6">
    <h3 class="tw-font-medium tw-mb-2 tw-text-sm">MỨC GIÁ</h3>
    <div class="tw-flex tw-flex-col tw-gap-2 tw-text-sm">
      <label v-for="range in priceRanges" :key="range.id" class="tw-flex tw-items-center tw-gap-2">
        <input 
          type="radio" 
          class="tw-form-radio" 
          :value="range.id" 
          v-model="selectedRange"
          @change="handlePriceChange" 
        />
        {{ range.label }}
      </label>
      <button v-if="selectedRange" @click="clearPrice" class="tw-text-xs tw-text-blue-500 tw-mt-1">Xóa lọc</button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const priceRanges = [
  { id: 1, label: 'Dưới 1.000.000đ', min: 0, max: 1000000 },
  { id: 2, label: '1.000.000đ - 2.000.000đ', min: 1000000, max: 2000000 },
  { id: 3, label: '2.000.000đ - 3.000.000đ', min: 2000000, max: 3000000 },
  { id: 4, label: '3.000.000đ - 5.000.000đ', min: 3000000, max: 5000000 },
  { id: 5, label: '5.000.000đ - 10.000.000đ', min: 5000000, max: 10000000 },
  { id: 6, label: 'Giá trên 10.000.000đ', min: 10000000, max: null }
]

const selectedRange = ref(null)
const emit = defineEmits(['filter'])

const handlePriceChange = () => {
  const range = priceRanges.find(r => r.id === selectedRange.value)
  if (range) {
    emit('filter', { 
      min_price: range.min,
      max_price: range.max
    })
  }
}

const clearPrice = () => {
  selectedRange.value = null
  emit('filter', { 
    min_price: null,
    max_price: null
  })
}
</script>