<template>
  <div class="tw-flex tw-items-center tw-gap-2">
    <span class="tw-text-sm">Sắp xếp:</span>
    <select 
      class="tw-border tw-rounded tw-px-2 tw-py-1 tw-text-sm" 
      v-model="selectedSort"
      @change="handleSortChange"
    >
      <option v-for="option in sortOptions" :key="option.value" :value="option.value">
        {{ option.label }}
      </option>
    </select>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const emit = defineEmits(['sort'])

const sortOptions = [
  { value: 'name_asc', label: 'Tên A → Z' },
  { value: 'name_desc', label: 'Tên Z → A' },
  { value: 'price_asc', label: 'Giá tăng dần' },
  { value: 'price_desc', label: 'Giá giảm dần' }
]

const selectedSort = ref('name_asc')

const handleSortChange = () => {
  const [field, direction] = selectedSort.value.split('_')
  emit('sort', {
    sort_by: field,
    sort_direction: direction
  })
}

// Emit initial sort on mount
onMounted(() => {
  handleSortChange()
})
</script>