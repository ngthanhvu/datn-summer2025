<template>
  <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
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

    <Form :fields="formFields" :initial-data="formData" v-model="formData" @submit="handleSubmit" />

    <div class="tw-flex tw-justify-end tw-gap-4 tw-mt-6">
      <NuxtLink to="/admin/products" class="tw-px-4 tw-py-2 tw-border tw-rounded tw-text-gray-600 hover:tw-bg-gray-50">
        Hủy
      </NuxtLink>
      <button @click="handleSubmit"
        class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 hover:tw-bg-primary-dark">
        Tạo sản phẩm
      </button>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'admin'
})

import { ref } from 'vue'
import Form from '~/components/admin/Form.vue'

const formFields = [
  {
    name: 'name',
    label: 'Tên sản phẩm',
    type: 'text',
    placeholder: 'Nhập tên sản phẩm',
    required: true
  },
  {
    name: 'price',
    label: 'Giá',
    type: 'number',
    placeholder: 'Nhập giá sản phẩm',
    required: true,
    min: 0,
    step: 1000
  },
  {
    name: 'category',
    label: 'Danh mục',
    type: 'select',
    placeholder: 'Chọn danh mục',
    required: true,
    options: [
      { value: 'phone', label: 'Điện thoại' },
      { value: 'laptop', label: 'Laptop' },
      { value: 'tablet', label: 'Máy tính bảng' }
    ]
  },
  {
    name: 'description',
    label: 'Mô tả',
    type: 'textarea',
    placeholder: 'Nhập mô tả sản phẩm',
    rows: 4
  },
  {
    name: 'image',
    label: 'Hình ảnh',
    type: 'image'
  },
  {
    name: 'status',
    label: 'Trạng thái',
    type: 'toggle'
  }
]

const formData = ref({
  name: '',
  price: 0,
  category: '',
  description: '',
  status: true,
  image: null
})

const handleSubmit = async () => {
  try {
    // TODO: Call API to create product
    console.log('Create product:', formData.value)

    // Navigate back to products list
    await navigateTo('/admin/products')
  } catch (error) {
    console.error('Error creating product:', error)
  }
}
</script>

<style scoped>
.tw-bg-primary {
  background-color: #3bb77e;
}

.tw-bg-primary-dark {
  background-color: #2ea16d;
}
</style>