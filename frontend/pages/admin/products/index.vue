<template>
  <div class="products-page">
    <div class="page-header tw-flex tw-items-center tw-justify-between">
      <div>
        <h1>Quản lý sản phẩm</h1>
        <p class="text-gray-600">Quản lý danh sách sản phẩm của bạn</p>
      </div>
      <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-3">
        <button @click="handleRefresh"
          class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-gray-600 tw-text-white tw-text-sm tw-font-medium tw-rounded-lg hover:tw-bg-gray-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-gray-500 focus:tw-ring-offset-2 tw-transition-colors tw-duration-200">
          <svg class="tw-w-4 tw-h-4 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
            </path>
          </svg>
          Tải lại
        </button>
      </div>
    </div>

    <ProductsTable :columns="columns" :data="products" :categories="categories" :brands="brands" :isLoading="isLoading"
      @delete="handleDelete" @refresh="handleRefresh" />
  </div>
</template>

<script setup>
useHead({
  title: "Quản lý sản phẩm",
  meta: [
    { name: "description", content: "Quản lý danh sách sản phẩm của bạn" }
  ]
})
definePageMeta({
  layout: 'admin',
  middleware: 'admin'
})

import { ref, onMounted } from 'vue'
import ProductsTable from '~/components/admin/ProductsTable.vue'
import { useProducts } from '~/composables/useProducts'
import Swal from 'sweetalert2'

const columns = [
  { key: 'main_image', label: 'Ảnh chính', type: 'main_image' },
  { key: 'sub_images', label: 'Ảnh phụ', type: 'sub_images' },
  { key: 'name', label: 'Tên sản phẩm' },
  { key: 'category', label: 'Danh mục', type: 'category' },
  { key: 'brand', label: 'Thương hiệu', type: 'brand' },
  { key: 'price', label: 'Giá bán', type: 'price' },
  { key: 'discount_price', label: 'Giá khuyến mãi', type: 'price' },
  { key: 'variants', label: 'Biến thể', type: 'variants' },
  { key: 'is_active', label: 'Trạng thái', type: 'status' }
]

const products = ref([])
const brands = ref([])
const categories = ref([])
const isLoading = ref(true)
const { getProducts, deleteProduct, getBrands, getCategories } = useProducts()

onMounted(async () => {
  isLoading.value = true
  try {
    const [productsData, brandsData, categoriesData] = await Promise.all([
      getProducts(),
      getBrands(),
      getCategories()
    ])

    products.value = productsData.map(product => ({
      ...product,
      brand: brandsData.find(b => b.id === product.brand_id)?.name || 'N/A',
      category: categoriesData.find(c => c.id === product.categories_id)?.name || 'N/A'
    }))

    brands.value = brandsData.map(brand => ({
      value: brand.name,
      label: brand.name
    }))

    categories.value = categoriesData.map(category => ({
      value: category.name,
      label: category.name
    }))
  } catch (error) {
    console.error('Error fetching data:', error)
  } finally {
    isLoading.value = false
  }
})

const handleDelete = async (product) => {
  Swal.fire({
    title: 'Bạn có chắc chắn muốn xóa sản phẩm này không?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await deleteProduct(product.id)
        products.value = await getProducts()
        const Toast = Swal.mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true,
        })
        Toast.fire({
          icon: 'success',
          title: 'Sản phẩm đã được xóa thành công'
        })
      } catch (error) {
        console.error('Error deleting product:', error)
        Swal.fire('Có lỗi xảy ra khi xóa sản phẩm', error.message, 'error')
      }
    }
  })
}

const handleRefresh = async () => {
  isLoading.value = true
  try {
    const productsData = await getProducts()
    products.value = productsData.map(product => ({
      ...product,
      brand: brands.value.find(b => b.value === product.brand)?.value || 'N/A',
      category: categories.value.find(c => c.value === product.category)?.value || 'N/A'
    }))
  } catch (error) {
    console.error('Error refreshing products:', error)
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.products-page {
  padding: 1.5rem;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 1.875rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 0.5rem;
}
</style>