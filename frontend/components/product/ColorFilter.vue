<template>
  <div class="tw-mb-6">
    <h3 class="tw-font-medium tw-mb-2 tw-text-sm">MÀU SẮC</h3>
    <div class="tw-flex tw-flex-col tw-gap-2">
      <label v-for="color in displayedColors" :key="color.value" class="tw-flex tw-items-center tw-gap-2">
        <input 
          type="radio" 
          name="color" 
          class="tw-form-radio" 
          :value="color.value" 
          v-model="selectedColor"
          @change="handleColorChange" 
        />
        {{ color.label }}
      </label>
      <button 
        v-if="selectedColor" 
        @click="clearColor" 
        class="tw-text-xs tw-text-blue-500 tw-mt-1"
      >
        Xóa lọc
      </button>
      <button 
        v-if="colors.length > initialDisplayCount" 
        @click="toggleShowMore" 
        class="tw-text-xs tw-text-blue-500 tw-mt-1"
      >
        {{ showAll ? 'Thu gọn' : `Xem thêm (${colors.length - initialDisplayCount})` }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const colors = [
  { value: 'red', label: 'Đỏ' },
  { value: 'orange', label: 'Cam' },
  { value: 'purple', label: 'Tím' },
  { value: 'gray', label: 'Xám' },
  { value: 'white', label: 'Trắng' },
  { value: 'black', label: 'Đen' },
  { value: 'blue', label: 'Xanh dương' },
  { value: 'green', label: 'Xanh lá' },
  { value: 'yellow', label: 'Vàng' },
  { value: 'pink', label: 'Hồng' }
]

const initialDisplayCount = 5
const showAll = ref(false)
const selectedColor = ref('')
const emit = defineEmits(['filter'])

const displayedColors = computed(() => {
  return showAll.value ? colors : colors.slice(0, initialDisplayCount)
})

const toggleShowMore = () => {
  showAll.value = !showAll.value
}

const handleColorChange = () => {
  emit('filter', { color: selectedColor.value })
}

const clearColor = () => {
  selectedColor.value = ''
  emit('filter', { color: null })
}
</script>