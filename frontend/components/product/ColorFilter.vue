<template>
  <div class="tw-mb-6">
    <h3 class="tw-font-medium tw-mb-2 tw-text-sm">MÀU SẮC</h3>
    <div class="tw-flex tw-flex-col tw-gap-2">
      <label v-for="color in colors" :key="color" class="tw-flex tw-items-center tw-gap-2">
        <input type="radio" name="color" class="tw-form-radio" :value="color" v-model="selectedColor" />
        {{ color }}
      </label>
      <button class="tw-text-xs tw-text-blue-500 tw-mt-1">Xem thêm</button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const colors = ['Đỏ', 'Cam', 'Tím', 'Xám', 'Trắng']
const selectedColor = ref('')
</script> 