<template>
  <div class="tw-mb-6">
    <h3 class="tw-font-medium tw-mb-2 tw-text-sm">MỨC GIÁ</h3>
    <div class="tw-flex tw-flex-col tw-gap-2 tw-text-sm">
      <label v-for="range in priceRanges" :key="range.id" class="tw-flex tw-items-center tw-gap-2">
        <input type="checkbox" class="tw-form-checkbox" :value="range.id" v-model="selectedRanges" />
        {{ range.label }}
      </label>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const priceRanges = [
  { id: 1, label: 'Dưới 1.000.000đ' },
  { id: 2, label: '1.000.000đ - 2.000.000đ' },
  { id: 3, label: '2.000.000đ - 3.000.000đ' },
  { id: 4, label: '3.000.000đ - 5.000.000đ' },
  { id: 5, label: '5.000.000đ - 10.000.000đ' },
  { id: 6, label: 'Giá trên 10.000.000đ' }
]

const selectedRanges = ref([])
</script> 