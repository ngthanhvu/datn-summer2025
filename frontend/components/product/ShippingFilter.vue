<template>
  <div>
    <h3 class="tw-font-medium tw-mb-2 tw-text-sm">DỊCH VỤ GIAO HÀNG</h3>
    <div class="tw-flex tw-flex-col tw-gap-2 tw-text-sm">
      <label v-for="service in shippingServices" :key="service.id" class="tw-flex tw-items-center tw-gap-2">
        <input type="checkbox" class="tw-form-checkbox" :value="service.id" v-model="selectedServices" />
        {{ service.label }}
      </label>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const shippingServices = [
  { id: 1, label: 'Miễn phí giao hàng' },
  { id: 2, label: 'Giao hàng nhanh 4h' },
  { id: 3, label: 'Giao hàng tiết kiệm' }
]

const selectedServices = ref([])
</script> 