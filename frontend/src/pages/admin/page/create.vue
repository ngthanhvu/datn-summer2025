<template>
  <div class="p-6 md:p-10 bg-gray-50 min-h-screen">
    <!-- Top Bar -->
    <div class="flex justify-between items-center mb-4">
      <div class="relative max-w-xs w-full">
        <h1 class="text-3xl font-bold text-gray-900 mb-1">Thêm trang mới</h1>
        <p class="text-gray-600">Điền thông tin để tạo trang mới</p>
      </div>
      <button @click="$router.go(-1)"
        class="flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
        <i class="fas fa-arrow-left"></i> Quay lại
      </button>
    </div>

    <PagesForm />
  </div>
</template>

<script setup>
import PagesForm from '../../../components/admin/newpage/PagesForm.vue';
import { useHead } from '@vueuse/head';
useHead({
  title: "Tạo mới page | DEVGANG",
  meta: [
    {
      name: "description",
      content: "Tạo Page"
    }
  ]
})
</script>