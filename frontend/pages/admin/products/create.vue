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
      <button @click="handleSubmit"
        class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 hover:tw-bg-primary-dark">
        Tạo sản phẩm
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
const basicFields = ref([
  {
    name: 'name',
    label: 'Tên sản phẩm',
    type: 'text',
    placeholder: 'Nhập tên sản phẩm',
    required: true,
    validation: {
      required: 'Vui lòng nhập tên sản phẩm',
      minLength: { value: 3, message: 'Tên sản phẩm phải có ít nhất 3 ký tự' }
    }
  },
  {
    name: 'price',
    label: 'Giá',
    type: 'number',
    placeholder: 'Nhập giá sản phẩm',
    required: true,
    min: 0,
    step: 1000,
    validation: {
      required: 'Vui lòng nhập giá sản phẩm',
      min: { value: 0, message: 'Giá không được âm' }
    }
  },
  {
    name: 'original_price',
    label: 'Giá gốc',
    type: 'number',
    placeholder: 'Nhập giá gốc',
    required: false,
    min: 0,
    step: 1000,
    validation: {
      min: { value: 0, message: 'Giá gốc không được âm' }
    }
  },
  {
    name: 'discount_price',
    label: 'Giá khuyến mãi',
    type: 'number',
    placeholder: 'Nhập giá khuyến mãi',
    required: false,
    min: 0,
    step: 1000,
    validation: {
      min: { value: 0, message: 'Giá khuyến mãi không được âm' }
    }
  },
  {
    name: 'category',
    label: 'Danh mục',
    type: 'select',
    placeholder: 'Chọn danh mục',
    required: true,
    options: [],
    validation: {
      required: 'Vui lòng chọn danh mục'
    }
  },
  {
    name: 'brand',
    label: 'Thương hiệu',
    type: 'select',
    placeholder: 'Chọn thương hiệu',
    required: true,
    options: [],
    validation: {
      required: 'Vui lòng chọn thương hiệu'
    }
  },
  {
    name: 'description',
    label: 'Mô tả',
    type: 'textarea',
    placeholder: 'Nhập mô tả sản phẩm',
    rows: 4,
    validation: {
      minLength: { value: 10, message: 'Mô tả phải có ít nhất 10 ký tự' }
    }
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
    required: true,
    validation: {
      required: 'Vui lòng nhập màu sắc'
    }
  },
  {
    name: 'size',
    label: 'Kích thước',
    type: 'text',
    placeholder: 'Nhập kích thước',
    required: true,
    validation: {
      required: 'Vui lòng nhập kích thước'
    }
  },
  {
    name: 'price',
    label: 'Giá',
    type: 'number',
    placeholder: 'Nhập giá biến thể',
    required: true,
    min: 0,
    step: 1000,
    validation: {
      required: 'Vui lòng nhập giá biến thể',
      min: { value: 0, message: 'Giá biến thể không được âm' }
    }
  },
  {
    name: 'sku',
    label: 'SKU',
    type: 'text',
    placeholder: 'Nhập mã SKU',
    required: true,
    validation: {
      required: 'Vui lòng nhập mã SKU',
      pattern: { value: /^[A-Z0-9-]+$/, message: 'SKU chỉ được chứa chữ hoa, số và dấu gạch ngang' }
    }
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

const validateField = (field, value) => {
  const fieldConfig = basicFields.value.find(f => f.name === field)
  if (!fieldConfig || !fieldConfig.validation) return ''

  const validation = fieldConfig.validation

  if (validation.required && !value) {
    return validation.required
  }

  if (validation.minLength && value.length < validation.minLength.value) {
    return validation.minLength.message
  }

  if (validation.min && value < validation.min.value) {
    return validation.min.message
  }

  if (validation.pattern && !validation.pattern.value.test(value)) {
    return validation.pattern.message
  }

  return ''
}

const validateVariantField = (field, value, index) => {
  const fieldConfig = variantFields.find(f => f.name === field)
  if (!fieldConfig || !fieldConfig.validation) return ''

  const validation = fieldConfig.validation

  if (validation.required && !value) {
    return validation.required
  }

  if (validation.minLength && value.length < validation.minLength.value) {
    return validation.minLength.message
  }

  if (validation.min && value < validation.min.value) {
    return validation.min.message
  }

  if (validation.pattern && !validation.pattern.value.test(value)) {
    return validation.pattern.message
  }

  return ''
}

const validateForm = () => {
  let hasError = false
  const errors = { ...formErrors.value }

  // Validate basic fields
  errors.name = validateField('name', formData.value.name)
  errors.price = validateField('price', formData.value.price)
  errors.original_price = validateField('original_price', formData.value.original_price)
  errors.discount_price = validateField('discount_price', formData.value.discount_price)
  errors.category = validateField('category', formData.value.category)
  errors.brand = validateField('brand', formData.value.brand)
  errors.description = validateField('description', formData.value.description)

  // Validate variants
  errors.variants = formData.value.variants.map((variant, index) => {
    const variantErrors = {}
    variantErrors.color = validateVariantField('color', variant.color, index)
    variantErrors.size = validateVariantField('size', variant.size, index)
    variantErrors.price = validateVariantField('price', variant.price, index)
    variantErrors.sku = validateVariantField('sku', variant.sku, index)
    return variantErrors
  })

  formErrors.value = errors
  hasError = Object.values(errors).some(error => error !== '') ||
    errors.variants.some(variantErrors =>
      Object.values(variantErrors).some(error => error !== ''))

  return !hasError
}

const handleSubmit = async () => {
  try {
    if (!validateForm()) {
      return
    }

    if (!formData.value.mainImage) {
      notyf.error('Vui lòng chọn ảnh chính cho sản phẩm')
      return
    }

    const productData = new FormData()
    productData.append('name', formData.value.name)
    productData.append('description', formData.value.description)
    productData.append('price', String(formData.value.price))
    productData.append('original_price', String(formData.value.original_price))
    productData.append('discount_price', String(formData.value.discount_price))
    productData.append('is_active', formData.value.status ? '1' : '0')
    productData.append('categories_id', String(formData.value.category))
    productData.append('brand_id', String(formData.value.brand))
    productData.append('is_main', formData.value.mainImage)

    formData.value.additionalImages.forEach((img) => {
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

    await createProduct(productData)
    await navigateTo('/admin/products')
  } catch (error) {
    console.error('Error creating product:', error)
    alert('Có lỗi khi tạo sản phẩm')
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