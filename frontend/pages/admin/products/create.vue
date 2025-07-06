<template>
  <div class="tw-bg-[#F5F7FB] tw-rounded-lg tw-shadow tw-p-6">
    <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
      <div>
        <h1 class="tw-text-2xl tw-font-semibold tw-text-gray-900">Thêm sản phẩm mới</h1>
        <p class="tw-text-gray-600">Điền thông tin để tạo sản phẩm mới</p>
      </div>
      <NuxtLink to="/admin/products"
        class="tw-bg-gray-100 tw-text-gray-600 tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-gray-200">
        <i class="fas fa-arrow-left"></i>
        Quay lại
      </NuxtLink>
    </div>
    <ProductsForm />
  </div>
</template>

<script setup>
useHead({
  title: 'Tạo sản phẩm'
})

definePageMeta({
  layout: 'admin',
  middleware: 'auth'
})

import ProductsForm from '~/components/admin/products/ProductsForm.vue'
</script>

<style scoped>
.tw-bg-primary {
  background-color: #3bb77e;
}

.tw-bg-primary-dark {
  background-color: #2ea16d;
}
</style>