<template>
  <div class="tw-bg-[#f7f8fa] tw-p-6 tw-min-h-screen">
    <FlashSaleForm :editData="flashSale" />
    <button @click="goBack" class="tw-bg-gray-400 tw-text-white tw-px-4 tw-py-2 tw-rounded mt-4 inline-block">Quay lại</button>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'admin'
})
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import FlashSaleForm from '@/components/admin/flash-sale/FlashSaleForm.vue'
const route = useRoute()
const router = useRouter()
const id = route.params.id
const flashSale = ref({
  id,
  name: 'Flash sale cuối năm',
  productCount: 7,
  time: '00:00 07/10/2021 ~ 23:59 07/10/2021',
  active: true,
  repeat: false,
  start: '',
  end: '',
  buttonText: '',
  buttonUrl: '',
  repeatMinutes: 60,
  autoIncrease: false
})
function goBack() {
  router.back()
}
</script>
