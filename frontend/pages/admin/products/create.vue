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

    <div class="tw-flex tw-flex-col tw-gap-6">
      <div class="tw-grid tw-grid-cols-2 tw-gap-6">
        <div class="tw-space-y-4">
          <Form :fields="basicFields" :initial-data="formData" v-model="formData" @submit="handleSubmit" />
        </div>
        <div class="tw-space-y-4">
          <Form :fields="imageFields" :initial-data="formData" v-model="formData" @submit="handleSubmit" />
        </div>
      </div>

      <div class="tw-w-full">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
          <h2 class="tw-text-xl tw-font-semibold">Biến thể sản phẩm</h2>
          <div class="tw-flex tw-gap-2">
            <button v-if="!showVariants" @click="showVariantsSection"
              class="tw-bg-gray-100 tw-text-gray-600 tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-gray-200">
              <i class="fas fa-plus"></i>
              Thêm biến thể
            </button>
            <button v-if="showVariants" @click="addVariant"
              class="tw-bg-gray-100 tw-text-gray-600 tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-gray-200">
              <i class="fas fa-plus"></i>
              Thêm biến thể
            </button>
          </div>
        </div>

        <div v-if="showVariants" class="tw-w-full">
          <div class="tw-flex tw-gap-4 tw-overflow-x-auto tw-pb-4 tw-w-full">
            <div v-for="(variant, index) in formData.variants" :key="index"
              class="tw-bg-gray-50 tw-p-4 tw-rounded-lg tw-flex-1 tw-min-w-[300px]">
              <div class="tw-flex tw-justify-between tw-mb-2">
                <h3 class="tw-font-medium">Biến thể {{ index + 1 }}</h3>
                <button @click="removeVariant(index)" class="tw-text-red-500 hover:tw-text-red-700">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
              <Form :fields="variantFields" :initial-data="variant" v-model="formData.variants[index]" />
            </div>
          </div>
        </div>
      </div>
    </div>

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

const basicFields = [
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
    name: 'brand',
    label: 'Thương hiệu',
    type: 'select',
    placeholder: 'Chọn thương hiệu',
    required: true,
    options: [
      { value: 'apple', label: 'Apple' },
      { value: 'samsung', label: 'Samsung' },
      { value: 'sony', label: 'Sony' },
      { value: 'lg', label: 'LG' }
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
    name: 'status',
    label: 'Trạng thái',
    type: 'toggle'
  }
]

const variantFields = [
  {
    name: 'color',
    label: 'Màu sắc',
    type: 'text',
    placeholder: 'Nhập màu sắc',
    required: true
  },
  {
    name: 'size',
    label: 'Kích thước',
    type: 'text',
    placeholder: 'Nhập kích thước',
    required: true
  },
  {
    name: 'price',
    label: 'Giá',
    type: 'number',
    placeholder: 'Nhập giá biến thể',
    required: true,
    min: 0,
    step: 1000
  },
  {
    name: 'quantity',
    label: 'Số lượng',
    type: 'number',
    placeholder: 'Nhập số lượng',
    required: true,
    min: 0
  },
  {
    name: 'sku',
    label: 'SKU',
    type: 'text',
    placeholder: 'Nhập mã SKU',
    required: true
  }
]

const imageFields = [
  {
    name: 'mainImage',
    label: 'Ảnh chính',
    type: 'mainImage',
    required: true,
    description: 'Chọn ảnh chính cho sản phẩm'
  },
  {
    name: 'additionalImages',
    label: 'Ảnh phụ',
    type: 'additionalImages',
    description: 'Chọn thêm các ảnh khác cho sản phẩm'
  }
]

const formData = ref({
  name: '',
  price: 0,
  category: '',
  brand: '',
  description: '',
  status: true,
  mainImage: null,
  mainImagePreview: null,
  additionalImages: [],
  additionalImagePreviews: [],
  variants: []  // Changed to empty array initially
})

// Add showVariants ref to control visibility
const showVariants = ref(false)

const handleSubmit = async () => {
  try {
    // Validate if main image is selected
    if (!formData.value.mainImage) {
      alert('Vui lòng chọn ảnh chính cho sản phẩm')
      return
    }

    // Prepare form data for API
    const productData = new FormData()
    productData.append('name', formData.value.name)
    productData.append('price', formData.value.price)
    productData.append('category', formData.value.category)
    productData.append('brand', formData.value.brand)
    productData.append('description', formData.value.description)
    productData.append('status', formData.value.status)
    productData.append('mainImage', formData.value.mainImage)

    // Append variants data only if there are variants
    if (formData.value.variants.length > 0) {
      formData.value.variants.forEach((variant, index) => {
        Object.keys(variant).forEach(key => {
          productData.append(`variants[${index}][${key}]`, variant[key])
        })
      })
    }

    // Append additional images if any
    formData.value.additionalImages.forEach((image, index) => {
      productData.append(`additionalImages[${index}]`, image)
    })

    // TODO: Call API to create product with images
    console.log('Create product with images:', formData.value)

    // Navigate back to products list
    await navigateTo('/admin/products')
  } catch (error) {
    console.error('Error creating product:', error)
  }
}

// Add new function to handle showing variants section
const showVariantsSection = () => {
  showVariants.value = true
  addVariant() // Add first variant immediately
}

const addVariant = () => {
  formData.value.variants.push({
    color: '',
    size: '',
    price: 0,
    quantity: 0,
    sku: ''
  })
}

const removeVariant = (index) => {
  formData.value.variants.splice(index, 1)
  // Hide variants section if no variants left
  if (formData.value.variants.length === 0) {
    showVariants.value = false
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