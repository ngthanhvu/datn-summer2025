<template>
  
  <div class="tw-bg-[#f7f8fa] tw-p-6 tw-min-h-screen">
    <div v-if="loading" class="tw-text-center tw-py-8">Đang tải dữ liệu...</div>
    <div v-else-if="error" class="tw-text-center tw-text-red-500 tw-py-4">{{ error }}</div>
    <template v-else>
      <FlashSaleForm :editData="flashSale" />
    </template>
    <button @click="goBack" class="tw-bg-gray-400 tw-text-white tw-px-4 tw-py-2 tw-rounded mt-4 inline-block">Quay lại</button>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'admin'
})

useHead({
    title: "Chỉnh sửa flash sale"
})
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import FlashSaleForm from '@/components/admin/flash-sale/FlashSaleForm.vue'
import { useFlashsale } from '@/composables/useFlashsale'
const route = useRoute()
const router = useRouter()
const { getFlashSaleById } = useFlashsale()
const id = route.params.id
const flashSale = ref(null)
const loading = ref(true)
const error = ref('')

onMounted(async () => {
  loading.value = true
  try {
    const data = await getFlashSaleById(id)
    flashSale.value = data
  } catch (e) {
    error.value = e.message || 'Không tìm thấy flash sale'
  } finally {
    loading.value = false
  }
})
function goBack() {
  router.back()
}
</script>
