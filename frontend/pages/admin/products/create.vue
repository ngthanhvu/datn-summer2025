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
          <Form v-if="isDataLoaded" :fields="basicFields" :initial-data="formData" v-model="formData"
            @submit="handleSubmit" :errors="formErrors" />
          <div v-else class="tw-text-center tw-text-gray-500">Đang tải danh mục và thương hiệu...</div>
        </div>
        <div class="tw-space-y-4">
          <Form :fields="imageFields" :initial-data="formData" v-model="formData" @submit="handleSubmit"
            :errors="formErrors" />
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
              <Form :fields="variantFields" :initial-data="variant" v-model="formData.variants[index]"
                :errors="formErrors.variants[index]" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="tw-flex tw-justify-end tw-gap-4 tw-mt-6">
      <NuxtLink to="/admin/products" class="tw-px-4 tw-py-2 tw-border tw-rounded tw-text-gray-600 hover:tw-bg-gray-50">
        Hủy
      </NuxtLink>
      <button @click="handleSubmit" :disabled="isSubmitting"
        class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 hover:tw-bg-primary-dark disabled:tw-opacity-50 disabled:tw-cursor-not-allowed">
        {{ isSubmitting ? 'Đang tạo...' : 'Tạo sản phẩm' }}
      </button>
    </div>
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

import { ref, onMounted } from 'vue'
import Form from '~/components/admin/Form.vue'
import { useProducts } from '~/composables/useProducts'
import { useCategory } from '~/composables/useCategory'
import { useBrand } from '~/composables/useBrand'

const notyf = useNuxtApp().$notyf
const isDataLoaded = ref(false)
const isSubmitting = ref(false)
const basicFields = ref([
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
    name: 'original_price',
    label: 'Giá gốc',
    type: 'number',
    placeholder: 'Nhập giá gốc',
    required: false,
    min: 0,
    step: 1000
  },
  {
    name: 'discount_price',
    label: 'Giá khuyến mãi',
    type: 'number',
    placeholder: 'Nhập giá khuyến mãi',
    required: false,
    min: 0,
    step: 1000
  },
  {
    name: 'category',
    label: 'Danh mục',
    type: 'select',
    placeholder: 'Chọn danh mục',
    required: true,
    options: []
  },
  {
    name: 'brand',
    label: 'Thương hiệu',
    type: 'select',
    placeholder: 'Chọn thương hiệu',
    required: true,
    options: []
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
])

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
  original_price: 0,
  discount_price: 0,
  category: '',
  brand: '',
  description: '',
  status: true,
  mainImage: null,
  mainImagePreview: null,
  additionalImages: [],
  additionalImagePreviews: [],
  variants: []
})

const formErrors = ref({
  name: '',
  price: '',
  original_price: '',
  discount_price: '',
  category: '',
  brand: '',
  description: '',
  variants: []
})

const showVariants = ref(false)

const { createProduct } = useProducts()
const { getCategories } = useCategory()
const { getBrands } = useBrand()

onMounted(async () => {
  try {
    const [categories, brands] = await Promise.all([
      getCategories(),
      getBrands()
    ])

    if (categories && Array.isArray(categories)) {
      const catOptions = categories.map(cat => ({ value: String(cat.id), label: cat.name }))
      const categoryField = basicFields.value.find(f => f.name === 'category')
      if (categoryField) {
        categoryField.options = catOptions
      }
    }

    if (brands && Array.isArray(brands)) {
      const brandOptions = brands.map(brand => ({ value: String(brand.id), label: brand.name }))
      const brandField = basicFields.value.find(f => f.name === 'brand')
      if (brandField) {
        brandField.options = brandOptions
      }
    }

    isDataLoaded.value = true
  } catch (err) {
    console.error('Không thể tải danh mục/thương hiệu', err)
  }
})

const validateForm = () => {
  let hasError = false
  const errors = { ...formErrors.value }

  // Basic validation
  if (!formData.value.name) {
    errors.name = 'Vui lòng nhập tên sản phẩm'
    hasError = true
  }
  if (!formData.value.price) {
    errors.price = 'Vui lòng nhập giá sản phẩm'
    hasError = true
  }
  if (!formData.value.category) {
    errors.category = 'Vui lòng chọn danh mục'
    hasError = true
  }
  if (!formData.value.brand) {
    errors.brand = 'Vui lòng chọn thương hiệu'
    hasError = true
  }

  formErrors.value = errors
  return !hasError
}

const handleSubmit = async () => {
  try {
    if (!validateForm()) {
      return
    }

    isSubmitting.value = true
    const productData = new FormData()

    productData.append('name', formData.value.name)
    productData.append('description', formData.value.description)
    productData.append('price', String(formData.value.price))
    productData.append('original_price', String(formData.value.original_price))
    productData.append('discount_price', String(formData.value.discount_price))
    productData.append('is_active', formData.value.status ? '1' : '0')
    productData.append('categories_id', String(formData.value.category))
    productData.append('brand_id', String(formData.value.brand))

    if (formData.value.mainImage) {
      productData.append('is_main', formData.value.mainImage)
    }

    formData.value.additionalImages.forEach(img => {
      productData.append('image_path[]', img)
    })

    if (formData.value.variants.length > 0) {
      formData.value.variants.forEach((variant, idx) => {
        productData.append(`variants[${idx}][color]`, variant.color)
        productData.append(`variants[${idx}][size]`, variant.size)
        productData.append(`variants[${idx}][price]`, String(variant.price))
        productData.append(`variants[${idx}][sku]`, variant.sku)
      })
    }

    const response = await createProduct(productData)
    notyf.success('Tạo sản phẩm thành công!')
    await navigateTo('/admin/products')
  } catch (error) {
    notyf.error(error.response?.data?.message || 'Có lỗi khi tạo sản phẩm')
  } finally {
    isSubmitting.value = false
  }
}

const showVariantsSection = () => {
  showVariants.value = true
  addVariant()
}

const addVariant = () => {
  formData.value.variants.push({
    color: '',
    size: '',
    price: 0,
    sku: ''
  })
  formErrors.value.variants.push({
    color: '',
    size: '',
    price: '',
    sku: ''
  })
}

const removeVariant = (index) => {
  formData.value.variants.splice(index, 1)
  formErrors.value.variants.splice(index, 1)
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