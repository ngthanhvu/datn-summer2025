<template>
  <div class="tw-flex tw-items-center tw-gap-2">
    <span class="tw-text-sm">Sắp xếp:</span>
    <select class="tw-border tw-rounded tw-px-2 tw-py-1 tw-text-sm" v-model="selectedSort">
      <option v-for="option in sortOptions" :key="option.value" :value="option.value">
        {{ option.label }}
      </option>
    </select>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const sortOptions = [
  { value: 'name_asc', label: 'Tên A → Z' },
  { value: 'name_desc', label: 'Tên Z → A' },
  { value: 'price_asc', label: 'Giá tăng dần' },
  { value: 'price_desc', label: 'Giá giảm dần' }
]

const selectedSort = ref('name_asc')
</script> 